<?php

namespace dface\sql\placeholders;

class IndexAnchorNode implements Anchor
{

	private int $index;

	public function __construct(int $index)
	{
		$this->index = $index;
	}

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitIndexAnchor($this->index, $args);
	}

	public function __toString() : string
	{
		return '@'.$this->index;
	}

}
