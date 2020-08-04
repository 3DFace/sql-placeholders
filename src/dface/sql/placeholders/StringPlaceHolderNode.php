<?php

namespace dface\sql\placeholders;

class StringPlaceHolderNode extends PlaceHolderNode
{

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitStringPlaceHolder($this, $args);
	}

}
