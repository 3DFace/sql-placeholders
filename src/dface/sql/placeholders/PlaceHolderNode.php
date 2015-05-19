<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

abstract class PlaceHolderNode implements Node {

	/** @var int */
	var $location;
	/** @var Node */
	var $source;
	/** @var bool */
	var $notNull;
	/** @var bool */
	var $forceNull;

	function __construct($location, Node $source, $notNull, $forceNull){
		$this->location = $location;
		$this->source = $source;
		$this->notNull = $notNull;
		$this->forceNull = $forceNull;
	}

	function __toString(){
		return sprintf('placeholder{%s}', $this->source);
	}

}
