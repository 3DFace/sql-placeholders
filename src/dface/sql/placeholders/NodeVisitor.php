<?php /* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

interface NodeVisitor {

	function visitPlain(PlainNode $plain, $args);

	function visitIdentityPlaceHolder(IdentityPlaceHolderNode $placeHolder, $args);

	function visitStringPlaceHolder(StringPlaceHolderNode $placeHolder, $args);

	function visitNumberPlaceHolder(NumberPlaceHolderNode $placeHolder, $args);

	function visitIntegerPlaceHolder(IntegerPlaceHolderNode $placeHolder, $args);

	function visitComposite(CompositeNode $composite, $args);

	function visitKeyAnchor(KeyAnchorNode $keyAnchor, $args);

	function visitIndexAnchor(IndexAnchorNode $indexAnchor, $args);

	function visitAnonymousAnchor(AnonymousAnchorNode $indexAnchor, $args);

}
