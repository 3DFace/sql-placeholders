<?php

namespace dface\sql\placeholders;

class DefaultParser implements Parser
{

	private string $expression;
	private int $index;
	private int $len;

	public function parse(string $expression) : Node
	{
		$this->expression = $expression;
		$this->len = \strlen($expression);
		$this->index = 0;
		$node = $this->consumeNode();
		if (($c = $this->get(0)) !== null) {
			throw new ParserException("Unexpected character '".$c."' at ".$this->index);
		}
		return $node;
	}

	private function consume() : void
	{
		$this->index++;
	}

	private function get($i) : ?string
	{
		$j = $this->index + $i;
		return $j < $this->len ? $this->expression[$j] : null;
	}

	/**
	 * @param string $match
	 * @throws ParserException
	 */
	private function sureConsume(string $match) : void
	{
		if ($this->get(0) !== $match) {
			throw new ParserException($match." expected at ".$this->index, $this->index);
		}
		$this->consume();
	}

	private function consumeSpace() : void
	{
		while (\ctype_space($this->get(0))) {
			$this->consume();
		}
	}

	private function consumeIndexAnchor() : IndexAnchorNode
	{
		$anchor = '';
		while (true) {
			$c = $this->get(0);
			if (\ctype_digit($c)) {
				$this->consume();
				$anchor .= $c;
			}else {
				break;
			}
		}
		return new IndexAnchorNode((int)$anchor);
	}

	private function consumeKeyAnchor() : KeyAnchorNode
	{
		$anchor = $this->get(0);
		$this->consume();
		while (true) {
			$c = $this->get(0);
			if ($c === '_' || \ctype_alnum($c)) {
				$this->consume();
				$anchor .= $c;
			}else {
				break;
			}
		}
		return new KeyAnchorNode($anchor);
	}

	private function consumeAnchor()
	{
		$this->consumeSpace();
		$c = $this->get(0);
		if (\is_numeric($c)) {
			$anchor = $this->consumeIndexAnchor();
		}else {
			$anchor = $this->consumeKeyAnchor();
		}
		return $anchor;
	}

	/**
	 * @return PlaceHolderNode
	 * @throws ParserException
	 */
	private function consumePlaceHolderNode() : PlaceHolderNode
	{
		$this->sureConsume('{');
		$this->consumeSpace();
		$type = '';
		while (true) {
			$c = $this->get(0);
			if ($c === '_' || \ctype_alnum($c)) {
				$this->consume();
				$type .= $c;
			}else {
				break;
			}
		}
		switch ($type) {
			case 's':
			case 'b':
			case 'n':
			case 'i':
			case 'd':
				$node = $this->consumePlaceHolder($type);
				break;
			default:
				throw new ParserException("Unknown type '$type'");
		}
		$this->consumeSpace();
		$this->sureConsume('}');
		return $node;
	}

	/**
	 * @return Node | null
	 * @throws ParserException
	 */
	private function consumePlaceHolderArgument()
	{
		$this->consumeSpace();
		$c = $this->get(0);
		if ($c === ':') {
			$this->consume();
			$this->consumeSpace();
			$c = $this->get(0);
			switch ($c) {
				case '}':
					$node = AnonymousAnchorNode::$SHARED;
					break;
				case null:
					throw new ParserException("Unexpected end of string");
				default:
					$node = $this->consumeAnchor();
			}
		}else {
			$node = null;
		}
		return $node;
	}

	/**
	 * @param string $type
	 * @return PlaceHolderNode
	 * @throws ParserException
	 */
	private function consumePlaceHolder(string $type) : PlaceHolderNode
	{
		$notNull = false;
		$forceNull = false;
		if ($this->get(0) === '+') {
			$this->consume();
			$notNull = true;
		}
		if ($this->get(0) === '-') {
			$this->consume();
			$forceNull = true;
		}
		if (!($source = $this->consumePlaceHolderArgument())) {
			$source = AnonymousAnchorNode::$SHARED;
		}
		switch ($type) {
			case 's':
				$node = new StringPlaceHolderNode($source, $notNull, $forceNull);
				break;
			case 'b':
				$node = new BinaryPlaceHolderNode($source, $notNull, $forceNull);
				break;
			case 'n':
				$node = new NumberPlaceHolderNode($source, $notNull, $forceNull);
				break;
			case 'i':
				$node = new IdentityPlaceHolderNode($source, $notNull, $forceNull);
				break;
			case 'd':
				$node = new IntegerPlaceHolderNode($source, $notNull, $forceNull);
				break;
			default:
				throw new ParserException('Unknown node type '.$type);
		}
		return $node;
	}

	/**
	 * @return CompositeNode|mixed
	 * @throws ParserException
	 */
	private function consumeNode()
	{
		$list = array();
		while (true) {
			$c = $this->get(0);
			switch ($c) {
				case null:
					break 2;
				case '{':
					$node = $this->consumePlaceHolderNode();
					break;
				case '"':
				case '\'':
					$node = $this->consumeString();
					break;
				default:
					$node = $this->consumePlain();
					break;
			}
			$list[] = $node;
		}
		return \count($list) === 1 ? $list[0] : new CompositeNode($list);
	}

	/**
	 * @return PlainNode
	 * @throws ParserException
	 */
	private function consumePlain() : PlainNode
	{
		$plain = '';
		while (true) {
			$c = $this->get(0);
			switch ($c) {
				case '\\':
					$this->consume();
					$c = $this->get(0);
					switch ($c) {
						case '{':
						case '}':
							$this->consume();
							$plain .= $c;
							break;
						default:
							$plain .= '\\';
					}
					break;
				case '{':
				case '"':
				case null:
				case '\'':
					break 2;
				case '/':
					$this->consume();
					$c = $this->get(0); // eat '/'
					if ($c === '*') {
						$plain .= $this->consumeBlockComment();
					}else {
						$this->consume();
						$plain .= '/'.$c;
					}
					break;
				default:
					$plain .= $c;
					$this->consume();
			}
		}
		return new PlainNode($plain);
	}

	/**
	 * @return string
	 * @throws ParserException
	 */
	private function consumeBlockComment() : string
	{
		$location = $this->index - 1;
		$this->consume(); // eat '*'
		$string = '/*';
		while (true) {
			$c = $this->get(0);
			switch ($c) {
				case '*':
					$string .= $c;
					$this->consume();
					if ($this->get(0) === '/') {
						$string .= '/';
						$this->consume();
					}
					break 2;
				case null:
					throw new ParserException("Unexpected end of quoted string started at ".$location);
				default:
					$string .= $c;
					$this->consume();
			}
		}
		return $string;
	}

	/**
	 * @return StringNode
	 * @throws ParserException
	 */
	private function consumeString() : StringNode
	{
		$location = $this->index;
		$quota = $this->get(0);
		$string = '';
		$this->consume();
		while (true) {
			$c = $this->get(0);
			switch ($c) {
				case $quota:
					$this->consume();
					break 2;
				case '\\':
					$this->consume();
					$c = $this->get(0);
					switch ($c) {
						case '\\':
							$this->consume();
							$string .= '\\';
							break;
						case 'n':
							$this->consume();
							$string .= "\n";
							break;
						case 't':
							$this->consume();
							$string .= "\t";
							break;
						case 'r':
							$this->consume();
							$string .= "\r";
							break;
						case '\'':
						case '"':
							$this->consume();
							$string .= $c;
							break;
						case null:
							throw new ParserException("Unexpected end of quoted string started at ".$location);
						default:
							$this->consume();
							$string .= '\\'.$c;
					}
					break;
				case null:
					throw new ParserException("Unexpected end of quoted string started at ".$location);
				default:
					$string .= $c;
					$this->consume();
			}
		}
		return new StringNode($quota, $string);
	}

}
