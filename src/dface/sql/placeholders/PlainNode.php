<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class PlainNode implements Node {

	var $location;
	/** @var string */
	var $text;

	function __construct($location, $text){
		$this->location = $location;
		$this->text = $text;
	}

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitPlain($this, $args);
	}

	function __toString(){
		return (string)$this->text;
	}

}
