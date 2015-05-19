<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

interface Parser {

	/**
	 * @abstract
	 * @param $expression string
	 * @throws ParserException
	 * @return Node
	 */
	function parse($expression);

}
