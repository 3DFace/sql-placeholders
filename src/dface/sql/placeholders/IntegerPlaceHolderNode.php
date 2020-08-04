<?php

namespace dface\sql\placeholders;

class IntegerPlaceHolderNode extends PlaceHolderNode
{

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitIntegerPlaceHolder($this, $args);
	}

}
