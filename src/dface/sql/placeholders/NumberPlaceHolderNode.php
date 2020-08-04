<?php

namespace dface\sql\placeholders;

class NumberPlaceHolderNode extends PlaceHolderNode
{

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitNumberPlaceHolder($this, $args);
	}

}
