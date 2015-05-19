<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class CompositeNode implements Node {

	/** @var Node[] */
	var $nodes;

	function __construct($nodes){
		$this->nodes = $nodes;
	}

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitComposite($this, $args);
	}

	function __toString(){
		return sprintf('composite{%s}', implode(", ", $this->nodes));
	}
}
