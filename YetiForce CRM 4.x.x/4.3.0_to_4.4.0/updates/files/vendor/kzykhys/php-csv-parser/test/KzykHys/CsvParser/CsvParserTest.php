<?php

use KzykHys\CsvParser\CsvParser;

class CsvParserTest extends \PHPUnit_Framework_TestCase
{
	public function providePatterns()
	{
		$dir = __DIR__ . '/Resources/csv/';

		$names = [
			'1-plain', '3-quote-escaping', '4-multiline', '5-multiline-2'
		];

		$patterns = [];

		foreach ($names as $name) {
			$patterns[] = [
				$name,
				[
					'CR'   => $dir . $name . '.CR.csv',
					'CRLF' => $dir . $name . '.CRLF.csv',
					'LF'   => $dir . $name . '.LF.csv'
				],
				json_decode(file_get_contents($dir . $name . '.json'), true)
			];
		}

		return $patterns;
	}

	/**
	 * @dataProvider providePatterns
	 */
	public function testCompareResultsFromFileAndString($name, array $files, $out)
	{
		$results = [];

		foreach ($files as $eol => $file) {
			$fromFile   = CsvParser::fromFile($file)->parse();
			$fromString = CsvParser::fromString(file_get_contents($file))->parse();
			$results[$eol] = $fromFile;

			$this->assertSame($fromFile, $fromString, $name . '(' . $eol . ')');
		}

		$this->assertSame($out, $results['LF'], $name . '(LF)');

		foreach ($out as &$line) {
			foreach ($line as &$column) {
				$column = str_replace("\n", "\r\n", $column);
			}
		}
		$this->assertSame($out, $results['CRLF'], $name . '(CRLF)');

		foreach ($out as &$line) {
			foreach ($line as &$column) {
				$column = str_replace("\n", '', $column);
			}
		}

		$this->assertSame($out, $results['CR'], $name . '(CR)');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidFile()
	{
		CsvParser::fromFile('foo.csv');
	}

	public function testIterator()
	{
		$parser = new CsvParser(new ArrayIterator());

		foreach ($parser as $row);
	}

	public function testOffsetAndLimitOption()
	{
		$input = new ArrayIterator(['1,2,3', '4,5,6', '7,8,9', '10,11,12']);

		$parser = new CsvParser($input, ['offset' => 1]);
		$this->assertSame([1 => [4, 5, 6], 2 => [7, 8, 9], 3 => [10, 11, 12]], $parser->parse());

		$parser = new CsvParser($input, ['limit' => 1]);
		$this->assertSame([0 => [1, 2, 3]], $parser->parse());

		$parser = new CsvParser($input, ['offset' => 1, 'limit' => 2]);
		$this->assertSame([1 => [4, 5, 6], 2 => [7, 8, 9]], $parser->parse());
	}

	public function testMultibyteString()
	{
		$dir = __DIR__ . '/Resources/csv/';
		$files = [
			$dir . '6-cp932-excel-win.csv', $dir . '6-cp932-excel-mac.csv'
		];
		$expected = json_decode(file_get_contents($dir . '6-cp932-excel.json'));

		foreach ($files as $file) {
			$fromFile   = CsvParser::fromFile($file)->parse();
			$fromString = CsvParser::fromString(file_get_contents($file))->parse();
			$this->assertSame($fromFile, $fromString);
			$this->assertSame($expected, $fromFile);
		}
	}
}
