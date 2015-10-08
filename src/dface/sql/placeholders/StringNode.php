<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class StringNode implements Node {

	/** @var int */
	var $location;
	/** @var string */
	var $quote;
	/** @var string */
	var $text;

	function __construct($location, $quote, $text){
		$this->location = $location;
		$this->quote = $quote;
		$this->text = $text;
	}

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitString($this, $args);
	}

	function __toString(){
		return $this->text;
	}

}
