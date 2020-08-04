<?php

namespace dface\sql\placeholders;

interface Parser
{

	/**
	 * @param string $expression
	 * @return Node
	 * @throws ParserException
	 */
	public function parse(string $expression) : Node;

}
