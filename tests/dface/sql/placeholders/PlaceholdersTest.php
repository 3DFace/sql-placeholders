<?php
/* author: Ponomarev Denis <ponomarev@gmail.com> */

namespace dface\sql\placeholders;

class PlaceholdersTest extends \PHPUnit_Framework_TestCase {

	/** @var DefaultParser */
	private $parser;
	/** @var DefaultFormatter */
	private $formatter;

	protected function setUp()
	{
		parent::setUp();
		$this->parser = new DefaultParser();
		$this->formatter = new DefaultFormatter();
	}

	function testDoubleParseAndFormatEqualsOriginal(){
		$original = <<<'EOT'
UPDATE table WHERE field='asd\\\''
EOT;
		$node = $this->parser->parse($original);
		$formatted = $this->formatter->format($node, [], 'addslashes');
		$node = $this->parser->parse($formatted);
		$formatted = $this->formatter->format($node, [], 'addslashes');
		$this->assertEquals($original, $formatted);
	}

	function testPlaceholders(){
		$node = $this->parser->parse("plain 'ignored {s}' \"ignored {d}\" {i} {s} {d}");
		$formatted = $this->formatter->format($node, ['asd', '\'zxc\\', '1a'], 'addslashes');
		$this->assertEquals("plain 'ignored {s}' \"ignored {d}\" `asd` '\\'zxc\\\\' 1", $formatted->__toString());
	}

	function testBinary(){
		$node = $this->parser->parse('{b}');
		$formatted = $this->formatter->format($node, [hex2bin('af80')], 'addslashes');
		$this->assertEquals('0xaf80', $formatted->__toString());
	}

	function testD(){
		// null
		$this->assertSame('null', $this->formatAs('d', null));

		//int val
		$this->assertSame('1', $this->formatAs('d', 1));
		$this->assertSame('-1', $this->formatAs('d', -1));

		//str val
		$this->assertSame('1', $this->formatAs('d', '1'));
		$this->assertSame('1', $this->formatAs('d', '1 '));
		$this->assertSame('1', $this->formatAs('d', ' 1'));
		$this->assertSame('1', $this->formatAs('d', ' 1'));
		if(PHP_VERSION_ID >= 70100){
			$this->assertSame('100', $this->formatAs('d', '1e2'));
		}else{
			$this->assertSame('1', $this->formatAs('d', '1e2'));
		}
		$this->assertSame('111', $this->formatAs('d', '0111'));
		$this->assertSame('1', $this->formatAs('d', '0001'));
		$this->assertSame('0', $this->formatAs('d', '+-12'));
		$this->assertSame('0', $this->formatAs('d', ''));
		$this->assertSame('-1', $this->formatAs('d', '-1'));
		$this->assertSame('1', $this->formatAs('d', '+1'));
		$this->assertSame('-11111111111111111111111',  $this->formatAs('d', ' -11111111111111111111111'));
		$this->assertSame('11111111111111111111111', $this->formatAs('d', '+0011111111111111111111111'));
		$this->assertSame('11111111111111111111111', $this->formatAs('d', '  11111111111111111111111  '));
	}

	private function formatAs($placeholder, $val){
		$node = $this->parser->parse("{".$placeholder."}");
		return (string)$this->formatter->format($node, [$val], 'addslashes');
	}

}
