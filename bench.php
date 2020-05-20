<?php

use dface\sql\placeholders\DefaultFormatter;
use dface\sql\placeholders\DefaultParser;

require_once __DIR__ . '/vendor/autoload.php';

$parser = new DefaultParser();
$formatter = new DefaultFormatter();

$node = $parser->parse('{d}');

$start = microtime(true);
for($i=0; $i < 1e5; $i++){
	$formatter->format($node, ['1e1'], 'addslashes');
}
echo microtime(true) - $start;
