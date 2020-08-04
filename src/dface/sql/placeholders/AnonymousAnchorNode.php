<?php

namespace dface\sql\placeholders;

class AnonymousAnchorNode implements Anchor
{

	static AnonymousAnchorNode $SHARED;

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitAnonymousAnchor($args);
	}

	public function __toString() : string
	{
		return '@?';
	}

}

AnonymousAnchorNode::$SHARED = new AnonymousAnchorNode();
