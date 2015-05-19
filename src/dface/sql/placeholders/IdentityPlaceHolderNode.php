<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class IdentityPlaceHolderNode extends PlaceHolderNode {

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitIdentityPlaceHolder($this, $args);
	}

}
