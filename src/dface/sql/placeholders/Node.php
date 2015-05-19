<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

interface Node {

	/**
	 * @abstract
	 * @param NodeVisitor $visitor
	 * @param $args mixed
	 * @return mixed
	 */
	function acceptVisitor(NodeVisitor $visitor, $args);

}
