<?php

namespace dface\sql\placeholders;

class BinaryPlaceHolderNode extends PlaceHolderNode
{

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitBinaryPlaceHolder($this, $args);
	}

}
