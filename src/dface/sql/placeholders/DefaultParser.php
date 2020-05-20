<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class DefaultParser implements Parser {

	private $expression;
	private $index;
	private $len;

	/**
	 * @param $expression string
	 * @throws ParserException
	 * @return Node
	 */
	function parse($expression){
		$this->expression = (string)$expression;
		$this->len = \strlen($expression);
		$this->index = 0;
		$node = $this->consumeNode();
		if(($c = $this->get(0)) !== null){
			throw new ParserException("Unexpected character '".$c."' at ".$this->index);
		}
		return $node;
	}

	private function consume(){
		$this->index++;
	}

	private function get($i){
		$j = $this->index + $i;
		return $j < $this->len ? $this->expression[$j] : null;
	}

	/**
	 * @param $match
	 * @throws ParserException
	 */
	private function sureConsume($match){
		if($this->get(0) !== $match){
			throw new ParserException($match." expected at ".$this->index, $this->index);
		}
		$this->consume();
	}

	private function consumeSpace(){
		while(ctype_space($this->get(0))){
			$this->consume();
		}
	}

	private function consumeIndexAnchor(){
		$anchor = '';
		while(true){
			$c = $this->get(0);
			if(ctype_digit($c)){
				$this->consume();
				$anchor .= $c;
			}else{
				break;
			}
		}
		return new IndexAnchorNode((int)$anchor);
	}

	private function consumeKeyAnchor(){
		$anchor = $this->get(0);
		$this->consume();
		while(true){
			$c = $this->get(0);
			if($c === '_' || \ctype_alnum($c)){
				$this->consume();
				$anchor .= $c;
			}else{
				break;
			}
		}
		return new KeyAnchorNode($anchor);
	}

	private function consumeAnchor(){
		$this->consumeSpace();
		$c = $this->get(0);
		if(is_numeric($c)){
			$anchor = $this->consumeIndexAnchor();
		}else{
			$anchor = $this->consumeKeyAnchor();
		}
		return $anchor;
	}

	/**
	 * @return BinaryPlaceHolderNode|IdentityPlaceHolderNode|IntegerPlaceHolderNode|NumberPlaceHolderNode|StringPlaceHolderNode
	 * @throws ParserException
	 */
	private function consumeCommandNode(){
		$location = $this->index;
		$this->sureConsume('{');
		$this->consumeSpace();
		$command = '';
		while(true){
			$c = $this->get(0);
			if($c === '_' || \ctype_alnum($c)){
				$this->consume();
				$command .= $c;
			}else{
				break;
			}
		}
		switch($command){
			case 's':
			case 'b':
			case 'n':
			case 'i':
			case 'd':
				$node = $this->consumePlaceHolder($command, $location);
				break;
			default:
				throw new ParserException("Unknown type '$command'");
		}
		$this->consumeSpace();
		$this->sureConsume('}');
		return $node;
	}

	/**
	 * @throws ParserException
	 * @return Node | null
	 */
	private function consumeCommandArgument(){
		$this->consumeSpace();
		$c = $this->get(0);
		if($c === ':'){
			$this->consume();
			$this->consumeSpace();
			$c = $this->get(0);
			switch($c){
				case '}':
					$node = AnonymousAnchorNode::$SHARED;
					break;
				case null:
					throw new ParserException("Unexpected end of string");
				default:
					$node = $this->consumeAnchor();
			}
		}else{
			$node = null;
		}
		return $node;
	}

	/**
	 * @param $type
	 * @param $location
	 * @return BinaryPlaceHolderNode|IdentityPlaceHolderNode|IntegerPlaceHolderNode|NumberPlaceHolderNode|StringPlaceHolderNode
	 * @throws ParserException
	 */
	private function consumePlaceHolder($type, $location){
		$notNull = false;
		$forceNull = false;
		if($this->get(0) === '+'){
			$this->consume();
			$notNull = true;
		}
		if($this->get(0) === '-'){
			$this->consume();
			$forceNull = true;
		}
		if(!($source = $this->consumeCommandArgument())){
			$source = AnonymousAnchorNode::$SHARED;
		}
		switch($type){
			case 's':
				$node = new StringPlaceHolderNode($location, $source, $notNull, $forceNull);
				break;
			case 'b':
				$node = new BinaryPlaceHolderNode($location, $source, $notNull, $forceNull);
				break;
			case 'n':
				$node = new NumberPlaceHolderNode($location, $source, $notNull, $forceNull);
				break;
			case 'i':
				$node = new IdentityPlaceHolderNode($location, $source, $notNull, $forceNull);
				break;
			case 'd':
				$node = new IntegerPlaceHolderNode($location, $source, $notNull, $forceNull);
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
	private function consumeNode(){
		$list = array();
		while(true){
			$c = $this->get(0);
			switch($c){
				case null:
					break 2;
				case '{':
					$node = $this->consumeCommandNode();
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
	private function consumePlain(){
		$location = $this->index;
		$plain = '';
		while(true){
			$c = $this->get(0);
			switch($c){
				case '\\':
					$this->consume();
					$c = $this->get(0);
					switch($c){
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
					if($c === '*'){
						$plain .= $this->consumeBlockComment();
					}else{
						$this->consume();
						$plain .= '/'.$c;
					}
					break;
				default:
					$plain .= $c;
					$this->consume();
			}
		}
		return new PlainNode($location, $plain);
	}

	/**
	 * @return string
	 * @throws ParserException
	 */
	private function consumeBlockComment(){
		$location = $this->index - 1;
		$this->consume(); // eat '*'
		$string = '/*';
		while(true){
			$c = $this->get(0);
			switch($c){
				case '*':
					$string .= $c;
					$this->consume();
					if($this->get(0) === '/'){
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
	private function consumeString(){
		$location = $this->index;
		$quota = $this->get(0);
		$string = '';
		$this->consume();
		while(true){
			$c = $this->get(0);
			switch($c){
				case $quota:
					$this->consume();
					break 2;
				case '\\':
					$this->consume();
					$c = $this->get(0);
					switch($c){
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
		return new StringNode($location, $quota, $string);
	}

}
