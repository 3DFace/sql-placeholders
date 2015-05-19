<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class IntegerPlaceHolderNode extends PlaceHolderNode {

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitIntegerPlaceHolder($this, $args);
	}

}
