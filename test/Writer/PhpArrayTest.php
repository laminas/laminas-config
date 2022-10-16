<?php

declare(strict_types=1);

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;
use LaminasTest\Config\Writer\TestAssets\DummyClassA;
use LaminasTest\Config\Writer\TestAssets\DummyClassB;
use LaminasTest\Config\Writer\TestAssets\PhpReader;

use function class_exists;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
use function sprintf;
use function trim;
use function version_compare;

use const PHP_EOL;
use const PHP_VERSION;

/**
 * @group      Laminas_Config
 */
class PhpArrayTest extends AbstractWriterTestCase
{
    protected function setUp(): void
    {
        $this->writer = new PhpArray();
        $this->reader = new PhpReader();
    }

    /**
     * @group Laminas-8234
     */
    public function testRender()
    {
        $config = new Config([
            'test'       => 'foo',
            'bar'        => [0 => 'baz', 1 => 'foo'],
            'emptyArray' => [],
            'object'     => (object) ['foo' => 'bar'],
            'integer'    => 123,
            'boolean'    => false,
            'null'       => null,
        ]);

        $configString = $this->writer->toString($config);

        $object = "stdClass::__set_state(array(\n"
            . "   'foo' => 'bar',\n"
            . ')),';

        if (version_compare(PHP_VERSION, '7.3.0') !== -1) {
            $object = '(object) array(' . "\n"
                . "   'foo' => 'bar',\n"
                . '),';
        }

        // build string line by line as we are trailing-whitespace sensitive.
        $expected  = "<?php\n";
        $expected .= "return array(\n";
        $expected .= "    'test' => 'foo',\n";
        $expected .= "    'bar' => array(\n";
        $expected .= "        0 => 'baz',\n";
        $expected .= "        1 => 'foo',\n";
        $expected .= "    ),\n";
        $expected .= "    'emptyArray' => array(),\n";
        $expected .= "    'object' => " . $object . "\n";
        $expected .= "    'integer' => 123,\n";
        $expected .= "    'boolean' => false,\n";
        $expected .= "    'null' => null,\n";
        $expected .= ");\n";

        self::assertEquals($expected, $configString);
    }

    public function testRenderWithBracketArraySyntax()
    {
        $config = new Config(['test' => 'foo', 'bar' => [0 => 'baz', 1 => 'foo'], 'emptyArray' => []]);

        $this->writer->setUseBracketArraySyntax(true);
        $configString = $this->writer->toString($config);

        // build string line by line as we are trailing-whitespace sensitive.
        $expected  = "<?php\n";
        $expected .= "return [\n";
        $expected .= "    'test' => 'foo',\n";
        $expected .= "    'bar' => [\n";
        $expected .= "        0 => 'baz',\n";
        $expected .= "        1 => 'foo',\n";
        $expected .= "    ],\n";
        $expected .= "    'emptyArray' => [],\n";
        $expected .= "];\n";

        self::assertEquals($expected, $configString);
    }

    public function testRenderWithQuotesInString()
    {
        $config = new Config(['one' => 'Test with "double" quotes', 'two' => 'Test with \'single\' quotes']);

        $configString = $this->writer->toString($config);

        $expected  = "<?php\n";
        $expected .= "return array(\n";
        $expected .= "    'one' => 'Test with \"double\" quotes',\n";
        $expected .= "    'two' => 'Test with \\'single\\' quotes',\n";
        $expected .= ");\n";

        self::assertEquals($expected, $configString);
    }

    public function testWriteConvertsPathToDirWhenWritingBackToFile()
    {
        $filename = $this->getTestAssetFileName();
        file_put_contents($filename, file_get_contents(__DIR__ . '/_files/array.php'));

        $this->writer->toFile($filename, include $filename);

        // Ensure file endings are same
        $expected = trim(file_get_contents(__DIR__ . '/_files/array.php'));
        $expected = preg_replace("~\r\n|\n|\r~", PHP_EOL, $expected);

        $result = trim(file_get_contents($filename));
        $result = preg_replace("~\r\n|\n|\r~", PHP_EOL, $result);

        self::assertSame($expected, $result);
    }

    public function testRenderWithClassNameScalarsEnabled()
    {
        $this->writer->setUseClassNameScalars(true);

        $dummyFqnA = DummyClassA::class;
        $dummyFqnB = DummyClassB::class;

        // Dummy classes should not be loaded prior this test
        $message = sprintf('class %s should not be loaded prior test', $dummyFqnA);
        self::assertFalse(class_exists($dummyFqnA, false), $message);

        $message = sprintf('class %s should not be loaded prior test', $dummyFqnB);
        self::assertFalse(class_exists($dummyFqnB, false), $message);

        $config = new Config([
            "\\~"                   => 'bar',
            'PhpArrayTest'          => 'PhpArrayTest',
            ''                      => 'emptyString',
            'TestAssets\DummyClass' => 'foo',
            $dummyFqnA              => [
                'fqnValue' => $dummyFqnB,
            ],
            '\\' . $dummyFqnA       => '',
        ]);

        $expected = <<<ECS
<?php
return array(
    '\\\~' => 'bar',
    'PhpArrayTest' => 'PhpArrayTest',
    '' => 'emptyString',
    'TestAssets\\\\DummyClass' => 'foo',
    \\$dummyFqnA::class => array(
        'fqnValue' => \\$dummyFqnB::class,
    ),
    \\$dummyFqnA::class => '',
);

ECS;
        $result   = $this->writer->toString($config);

        self::assertSame($expected, $result);
    }

    public function testUseClassNameScalarsIsFalseByDefault()
    {
        self::assertFalse($this->writer->getUseClassNameScalars(), 'useClassNameScalars should be false by default');
    }

    public function testSetUseBracketArraySyntaxReturnsFluentInterface()
    {
        self::assertSame($this->writer, $this->writer->setUseBracketArraySyntax(true));
    }

    public function testSetUseClassNameScalarsReturnsFluentInterface()
    {
        self::assertSame($this->writer, $this->writer->setUseClassNameScalars(true));
    }
}
