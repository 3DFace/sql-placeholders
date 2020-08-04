<?php

namespace dface\sql\placeholders;

class StringNode implements Node
{

	private string $quote;
	private string $text;

	public function __construct(string $quote, string $text)
	{
		$this->quote = $quote;
		$this->text = $text;
	}

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitString($this->text, $this->quote);
	}

	public function __toString() : string
	{
		return $this->text;
	}

}
