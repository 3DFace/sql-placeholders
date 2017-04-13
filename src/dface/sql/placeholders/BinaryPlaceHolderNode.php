<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class BinaryPlaceHolderNode extends PlaceHolderNode {

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitBinaryPlaceHolder($this, $args);
	}

}
