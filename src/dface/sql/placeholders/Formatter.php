<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

interface Formatter {

	/**
	 * @abstract
	 * @param $format Node
	 * @param $args
	 * @param $escape_func Callable
	 * @throws FormatterException
	 * @return PlainNode
	 */
	function format(Node $format, $args, $escape_func);

}
