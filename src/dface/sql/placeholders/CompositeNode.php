<?php

namespace dface\sql\placeholders;

class CompositeNode implements Node
{

	/** @var Node[] */
	private array $nodes;

	public function __construct(array $nodes)
	{
		$this->nodes = $nodes;
	}

	public function acceptVisitor(NodeVisitor $visitor, $args)
	{
		return $visitor->visitComposite($this->nodes, $args);
	}

	public function __toString() : string
	{
		return \sprintf('composite{%s}', \implode(", ", $this->nodes));
	}
}
