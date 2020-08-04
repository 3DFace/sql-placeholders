<?php

namespace dface\sql\placeholders;

class PlainNode implements Node
{

	private string $text;

	public function __construct(string $text)
	{
		$this->text = $text;
	}

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitPlain($this->text);
	}

	public function __toString() : string
	{
		return $this->text;
	}

}
