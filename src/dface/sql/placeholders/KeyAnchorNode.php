<?php

namespace dface\sql\placeholders;

class KeyAnchorNode implements Anchor
{

	private string $key;

	public function __construct(string $key)
	{
		$this->key = $key;
	}

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitKeyAnchor($this->key, $args);
	}

	public function __toString() : string
	{
		return '@'.$this->key;
	}

}
