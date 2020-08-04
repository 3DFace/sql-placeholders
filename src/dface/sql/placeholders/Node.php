<?php

namespace dface\sql\placeholders;

interface Node
{

	/**
	 * @param NodeVisitor $visitor
	 * @param mixed $args
	 * @return mixed
	 */
	public function acceptVisitor(NodeVisitor $visitor, $args);

}
