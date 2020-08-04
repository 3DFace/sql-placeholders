<?php

namespace dface\sql\placeholders;

class IdentityPlaceHolderNode extends PlaceHolderNode
{

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitIdentityPlaceHolder($this, $args);
	}

}
