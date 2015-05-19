<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class AnonymousAnchorNode implements Anchor {

	/** @var  AnonymousAnchorNode */
	static $SHARED;

	function acceptVisitor(NodeVisitor $visitor, $args){
		return $visitor->visitAnonymousAnchor($this, $args);
	}

	function __toString(){
		return '@?';
	}

}

AnonymousAnchorNode::$SHARED = new AnonymousAnchorNode();
