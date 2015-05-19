<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class KeyAnchorNode implements Anchor {

	public $key;

	function __construct($key){
		$this->key = $key;
	}

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitKeyAnchor($this, $args);
	}

	function __toString(){
		return '@'.$this->key;
	}

}
