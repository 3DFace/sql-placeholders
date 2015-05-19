<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class NumberPlaceHolderNode extends PlaceHolderNode {

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitNumberPlaceHolder($this, $args);
	}

}
