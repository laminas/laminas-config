<?php

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Reader\JavaProperties as JavaPropertiesReader;
use Laminas\Config\Writer\JavaProperties as JavaPropertiesWriter;

class JavaPropertiesTest extends AbstractWriterTestCase
{
    protected function setUp() : void
    {
        $this->reader = new JavaPropertiesReader();
        $this->writer = new JavaPropertiesWriter();
    }

    public function testNoSection()
    {
        $config = new Config(['test' => 'foo', 'test2.test3' => 'bar']);

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('foo', $config['test']);
        self::assertEquals('bar', $config['test2.test3']);
    }

    public function testWriteAndRead()
    {
        $this->markTestSkipped('JavaProperties writer cannot handle multi-dimensional configuration');
    }

    public function testWriteAndReadOriginalFile()
    {
        $config = $this->reader->fromFile(__DIR__ . '/_files/allsections.properties');

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('multi', $config['one.two.three']);
    }

    public function testWriteAndReadOriginalFileWithCustomDelimiter()
    {
        $config = $this->reader->fromFile(__DIR__ . '/_files/allsections.properties');

        $writer = new JavaPropertiesWriter('=');
        $writer->toFile($this->getTestAssetFileName(), $config);

        $reader = new JavaPropertiesReader('=');
        $config = $reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('multi', $config['one.two.three']);
    }
}
