<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class DefaultFormatter implements Formatter, NodeVisitor
{

	/** @var Callable */
	private $escape_func;
	private $anonymousIndex;

	/**
	 * @param $format Node
	 * @param $args
	 * @param $escape_func Callable
	 * @return PlainNode
	 */
	function format(Node $format, $args, $escape_func)
	{
		$this->escape_func = $escape_func;
		$this->anonymousIndex = 1;
		return new PlainNode(0, $format->acceptVisitor($this, $args));
	}

	private function formatValue($val, $escape_func)
	{
		if (is_array($val)) {
			if (!empty($val)) {
				$val = array_map($escape_func, $val);
				$val = implode(", ", $val);
			}else {
				$val = 'null';
			}
		}else {
			$val = $escape_func($val);
		}
		return $val;
	}

	function visitPlain(PlainNode $plain, $args)
	{
		return $plain->text;
	}

	function visitString(StringNode $string, $args)
	{
		return $string->quote.call_user_func($this->escape_func, $string->text).$string->quote;
	}

	function visitIdentityPlaceHolder(IdentityPlaceHolderNode $placeHolder, $args)
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) {
			return "`".\str_replace("`", "``", $v)."`";
		});
	}

	function visitStringPlaceHolder(StringPlaceHolderNode $placeHolder, $args)
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		$escape_func = $this->escape_func;
		return $this->formatValue($val, static function ($v) use ($escape_func, $placeHolder) {
			if (!$placeHolder->notNull && $v === null) {
				$v = 'null';
			}elseif ($placeHolder->forceNull && $v === '') {
				$v = 'null';
			}else {
				$v = "'".$escape_func((string)$v)."'";
			}
			return $v;
		});
	}

	function visitBinaryPlaceHolder(BinaryPlaceHolderNode $placeHolder, $args)
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) {
			if ($v === null) {
				return 'null';
			}
			if ($v === '') {
				return "''";
			}
			return '0x'.bin2hex($v);
		});
	}

	function visitNumberPlaceHolder(NumberPlaceHolderNode $placeHolder, $args)
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) use ($placeHolder) {
			if (!$placeHolder->notNull && $v === null) {
				$v = 'null';
			}/** @noinspection TypeUnsafeComparisonInspection */
			elseif ($placeHolder->forceNull && $v == 0) {
				$v = 'null';
			}else {
				$v = \str_replace(',', '.', (string)(0 + $v));
			}
			return $v;
		});
	}

	function visitIntegerPlaceHolder(IntegerPlaceHolderNode $placeHolder, $args)
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) use ($placeHolder) {
			if (!$placeHolder->notNull && $v === null) {
				$v = 'null';
			}/** @noinspection TypeUnsafeComparisonInspection */
			elseif ($placeHolder->forceNull && $v == 0) {
				$v = 'null';
			}else {
				if (!\is_int($v)) {
					$v = (string)$v;
					if(\preg_match('/^\s*([-+])?0*(\d+)\s*$/', $v, $m)){
						$v = ($m[1] === '-' ? '-' : '').$m[2];
					}else{
						$v = (int)$v;
					}
				}
			}
			return $v;
		});
	}

	function visitComposite(CompositeNode $composite, $args)
	{
		$result = '';
		foreach ($composite->nodes as $node) {
			$result .= $node->acceptVisitor($this, $args);
		}
		return $result;
	}

	/**
	 * @param KeyAnchorNode $keyAnchor
	 * @param $args
	 * @return mixed
	 * @throws FormatterException
	 */
	function visitKeyAnchor(KeyAnchorNode $keyAnchor, $args)
	{
		if (is_array($args)) {
			if (array_key_exists($keyAnchor->key, $args)) {
				return $args[$keyAnchor->key];
			}
			throw new FormatterException('There is no argument at key "'.$keyAnchor->key.'"');
		}
		if (is_object($args)) {
			if (property_exists($args, $keyAnchor->key)) {
				return $args->{$keyAnchor->key};
			}
			throw new FormatterException('There is no argument at key "'.$keyAnchor->key.'"');
		}
		throw new FormatterException('Argument not supports access by key');
	}

	/**
	 * @param IndexAnchorNode $indexAnchor
	 * @param $args
	 * @return mixed
	 * @throws FormatterException
	 */
	function visitIndexAnchor(IndexAnchorNode $indexAnchor, $args)
	{
		$args = $this->castToIndexArray($args);
		$i = $indexAnchor->index - 1;
		if (\array_key_exists($i, $args)) {
			return $args[$i];
		}
		throw new FormatterException('There is no argument at index '.$indexAnchor->index);
	}

	/**
	 * @param AnonymousAnchorNode $indexAnchor
	 * @param $args
	 * @return mixed
	 * @throws FormatterException
	 */
	function visitAnonymousAnchor(AnonymousAnchorNode $indexAnchor, $args)
	{
		$args = $this->castToIndexArray($args);
		$i = $this->anonymousIndex++ - 1;
		if (\array_key_exists($i, $args)) {
			return $args[$i];
		}
		throw new FormatterException('There is no argument at index '.($i + 1));
	}

	/**
	 * @param $args
	 * @return array
	 * @throws FormatterException
	 */
	protected function castToIndexArray($args)
	{
		if (\is_array($args)) {
			$args = \array_values($args);
		}elseif (\is_object($args)) {
			$args = \get_object_vars($args);
			$args = \array_values($args);
		}else {
			throw new FormatterException("Argument of type '".\gettype($args)."' not supports access by index");
		}
		return $args;
	}

}
