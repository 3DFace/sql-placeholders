<?php

namespace dface\sql\placeholders;

abstract class PlaceHolderNode implements Node
{

	public Node $source;
	public bool $notNull;
	public bool $forceNull;

	public function __construct(Node $source, bool $notNull, bool $forceNull)
	{
		$this->source = $source;
		$this->notNull = $notNull;
		$this->forceNull = $forceNull;
	}

	public function __toString() : string
	{
		return \sprintf('placeholder{%s}', $this->source);
	}

}
