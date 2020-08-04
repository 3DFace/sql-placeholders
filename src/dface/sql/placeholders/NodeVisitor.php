<?php

namespace dface\sql\placeholders;

interface NodeVisitor
{

	public function visitPlain(string $text);

	public function visitString(string $text, string $quote);

	public function visitIdentityPlaceHolder(IdentityPlaceHolderNode $placeHolder, $args);

	public function visitStringPlaceHolder(StringPlaceHolderNode $placeHolder, $args);

	public function visitBinaryPlaceHolder(BinaryPlaceHolderNode $placeHolder, $args);

	public function visitNumberPlaceHolder(NumberPlaceHolderNode $placeHolder, $args);

	public function visitIntegerPlaceHolder(IntegerPlaceHolderNode $placeHolder, $args);

	public function visitComposite(array $nodes, $args);

	public function visitKeyAnchor(string $key, $args);

	public function visitIndexAnchor(int $index, $args);

	public function visitAnonymousAnchor($args);

}
