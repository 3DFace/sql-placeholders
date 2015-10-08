<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class PlaceholdersTest extends \PHPUnit_Framework_TestCase {

	function testDoubleParseAndFormatEqualsOriginal(){
		$parser = new DefaultParser();
		$formatter = new DefaultFormatter();
		$original = <<<'EOT'
UPDATE table WHERE field='asd\\\''
EOT;
		$node = $parser->parse($original);
		$formatted = $formatter->format($node, [], 'addslashes');
		$node = $parser->parse($formatted);
		$formatted = $formatter->format($node, [], 'addslashes');
		$this->assertEquals($original, $formatted);
	}

	function testPlaceholders(){
		$parser = new DefaultParser();
		$formatter = new DefaultFormatter();
		$node = $parser->parse("plain 'ignored {s}' \"ignored {d}\" {i} {s} {d}");
		$formatted = $formatter->format($node, ['asd', '\'zxc\\', '1a'], 'addslashes');
		$this->assertEquals("plain 'ignored {s}' \"ignored {d}\" `asd` '\\'zxc\\\\' 1", $formatted->__toString());
	}

}
