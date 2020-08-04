<?php

namespace dface\sql\placeholders;

interface Formatter
{

	/**
	 * @param $format Node
	 * @param $args
	 * @param $escape_func Callable
	 * @return PlainNode
	 * @throws FormatterException
	 */
	public function format(Node $format, $args, callable $escape_func) : PlainNode;

}
