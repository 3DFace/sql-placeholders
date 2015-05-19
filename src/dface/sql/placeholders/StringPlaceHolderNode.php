<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class StringPlaceHolderNode extends PlaceHolderNode {

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitStringPlaceHolder($this, $args);
	}

}
