<?php

namespace dface\sql\placeholders;

class DefaultFormatter implements Formatter, NodeVisitor
{

	/** @var callable */
	private $escape_func;
	private int $anonymousIndex;

	/**
	 * @param $format Node
	 * @param $args
	 * @param $escape_func Callable
	 * @return PlainNode
	 */
	public function format(Node $format, $args, callable $escape_func) : PlainNode
	{
		$this->escape_func = $escape_func;
		$this->anonymousIndex = 1;
		return new PlainNode($format->acceptVisitor($this, $args));
	}

	private function formatValue($val, $escape_func) : string
	{
		if (\is_array($val)) {
			if (!empty($val)) {
				$val = \array_map($escape_func, $val);
				$val = \implode(", ", $val);
			}else {
				$val = 'null';
			}
		}else {
			$val = $escape_func($val);
		}
		return $val;
	}

	public function visitPlain(string $text) : string
	{
		return $text;
	}

	public function visitString(string $text, string $quote) : string
	{
		return $quote.($this->escape_func)($text).$quote;
	}

	public function visitIdentityPlaceHolder(IdentityPlaceHolderNode $placeHolder, $args) : string
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) : string {
			return "`".\str_replace("`", "``", $v)."`";
		});
	}

	public function visitStringPlaceHolder(StringPlaceHolderNode $placeHolder, $args) : string
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		$escape_func = $this->escape_func;
		return $this->formatValue($val, static function ($v) use ($escape_func, $placeHolder) : string {
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

	public function visitBinaryPlaceHolder(BinaryPlaceHolderNode $placeHolder, $args) : string
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) : string {
			if ($v === null) {
				return 'null';
			}
			if ($v === '') {
				return "''";
			}
			return '0x'.\bin2hex($v);
		});
	}

	public function visitNumberPlaceHolder(NumberPlaceHolderNode $placeHolder, $args) : string
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) use ($placeHolder) {
			if (!$placeHolder->notNull && $v === null) {
				return 'null';
			}
			if (!\is_numeric($v)) {
				$v = (string)$v;
				if (\preg_match('/^\s*([+-])?([0-9]+([.][0-9]*)?|[.][0-9]+)\s*$/', $v, $m)) {
					$v = (($m[1] === '-' ? '-' : '').$m[2]);
				} else {
					$v = (int)$v;
				}
			}
			if ($placeHolder->forceNull && (int)$v === 0) {
				return 'null';
			}
			return $v;
		});
	}

	public function visitIntegerPlaceHolder(IntegerPlaceHolderNode $placeHolder, $args) : string
	{
		$val = $placeHolder->source->acceptVisitor($this, $args);
		return $this->formatValue($val, static function ($v) use ($placeHolder) {
			if (!$placeHolder->notNull && $v === null) {
				return 'null';
			}
			if (!\is_int($v)) {
				$v = (string)$v;
				if (\preg_match('/^\s*([-+])?0*(\d+)\s*$/', $v, $m)) {
					$v = ($m[1] === '-' ? '-' : '').$m[2];
				}else {
					$v = (int)$v;
				}
			}
			if ($placeHolder->forceNull && (int)$v === 0) {
				return 'null';
			}
			return $v;
		});
	}

	public function visitComposite(array $nodes, $args) : string
	{
		$result = '';
		foreach ($nodes as $node) {
			$result .= $node->acceptVisitor($this, $args);
		}
		return $result;
	}

	/**
	 * @param string $key
	 * @param $args
	 * @return mixed
	 * @throws FormatterException
	 */
	public function visitKeyAnchor(string $key, $args)
	{
		if (\is_array($args)) {
			if (\array_key_exists($key, $args)) {
				return $args[$key];
			}
			throw new FormatterException('There is no argument at key "'.$key.'"');
		}
		if (\is_object($args)) {
			if (\property_exists($args, $key)) {
				return $args->{$key};
			}
			throw new FormatterException('There is no argument at key "'.$key.'"');
		}
		throw new FormatterException('Argument not supports access by key');
	}

	/**
	 * @param int $index
	 * @param $args
	 * @return mixed
	 * @throws FormatterException
	 */
	public function visitIndexAnchor(int $index, $args)
	{
		$args = $this->castToIndexArray($args);
		$i = $index - 1;
		if (\array_key_exists($i, $args)) {
			return $args[$i];
		}
		throw new FormatterException('There is no argument at index '.$index);
	}

	/**
	 * @param $args
	 * @return mixed
	 * @throws FormatterException
	 */
	public function visitAnonymousAnchor($args)
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
	protected function castToIndexArray($args) : array
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
