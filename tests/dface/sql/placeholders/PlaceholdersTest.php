<?php

/** @noinspection SpellCheckingInspection */

namespace dface\sql\placeholders;

use PHPUnit\Framework\TestCase;

class PlaceholdersTest extends TestCase {

	private DefaultParser $parser;
	private DefaultFormatter $formatter;

	protected function setUp() : void
	{
		parent::setUp();
		$this->parser = new DefaultParser();
		$this->formatter = new DefaultFormatter();
	}

	public function testDoubleParseAndFormatEqualsOriginal() : void
	{
		$original = <<<'EOT'
UPDATE table WHERE field='asd\\\''
EOT;
		$node = $this->parser->parse($original);
		$formatted = $this->formatter->format($node, [], 'addslashes');
		$node = $this->parser->parse($formatted);
		$formatted = $this->formatter->format($node, [], 'addslashes');
		self::assertEquals($original, $formatted);
	}

	public function testPlaceholders() : void
	{
		$node = $this->parser->parse("plain 'ignored {s}' \"ignored {d}\" {i} {s} {d}");
		$formatted = $this->formatter->format($node, ['asd', '\'zxc\\', '1a'], 'addslashes');
		self::assertEquals("plain 'ignored {s}' \"ignored {d}\" `asd` '\\'zxc\\\\' 1", $formatted->__toString());
	}

	public function testBinary() : void
	{
		$node = $this->parser->parse('{b}');
		$formatted = $this->formatter->format($node, [\hex2bin('af80')], 'addslashes');
		self::assertEquals('0xaf80', $formatted->__toString());
	}

	public function testS() : void
	{
		// null
		self::assertSame('null', $this->formatAs('s', null));
		self::assertSame('null', $this->formatAs('s-', ''));
		self::assertSame("''", $this->formatAs('s+', null));
	}

	public function testN() : void
	{
		self::assertSame('null', $this->formatAs('n', null));
		self::assertSame('null', $this->formatAs('n-', 0));
		self::assertSame('null', $this->formatAs('n-', ' '));
	}

	public function testD() : void
	{
		// null
		self::assertSame('null', $this->formatAs('d', null));
		self::assertSame('null', $this->formatAs('d-', 0));
		self::assertSame('null', $this->formatAs('d-', ''));
		self::assertSame('null', $this->formatAs('d-', '0'));
		self::assertSame('null', $this->formatAs('d-', ' '));

		// 0
		self::assertSame('0', $this->formatAs('d+', null));
		self::assertSame('0', $this->formatAs('d+', 0));
		self::assertSame('0', $this->formatAs('d+', ''));
		self::assertSame('0', $this->formatAs('d+', '0'));
		self::assertSame('0', $this->formatAs('d+', ' '));

		// val
		self::assertSame('1', $this->formatAs('d', 1));
		self::assertSame('-1', $this->formatAs('d', -1));
		self::assertSame('1', $this->formatAs('d-', '1'));
		self::assertSame('-1', $this->formatAs('d+', '-1'));

		if(PHP_VERSION_ID >= 70100){
			self::assertSame('100', $this->formatAs('d', '1e2'));
		}else{
			self::assertSame('1', $this->formatAs('d', '1e2'));
		}
		self::assertSame('111', $this->formatAs('d', '0111'));
		self::assertSame('1', $this->formatAs('d', '0001'));
		self::assertSame('0', $this->formatAs('d', '+-12'));
		self::assertSame('0', $this->formatAs('d', ''));
		self::assertSame('-1', $this->formatAs('d', '-1'));
		self::assertSame('1', $this->formatAs('d', '+1'));
		self::assertSame('-11111111111111111111111',  $this->formatAs('d', ' -11111111111111111111111'));
		self::assertSame('11111111111111111111111', $this->formatAs('d', '+0011111111111111111111111'));
		self::assertSame('11111111111111111111111', $this->formatAs('d', '  11111111111111111111111  '));
	}

	private function formatAs($placeholder, $val) : string
	{
		$node = $this->parser->parse("{".$placeholder."}");
		return (string)$this->formatter->format($node, [$val], 'addslashes');
	}

}
