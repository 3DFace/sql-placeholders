<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class IndexAnchorNode implements Anchor {

	var $index;

	function __construct($index){
		$this->index = $index;
	}

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitIndexAnchor($this, $args);
	}

	function __toString(){
		return '@'.$this->index;
	}

}
