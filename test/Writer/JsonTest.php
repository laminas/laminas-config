<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Reader\Json as JsonReader;
use Laminas\Config\Writer\Json as JsonWriter;

/**
 * @group      Laminas_Config
 */
class JsonTest extends AbstractWriterTestCase
{
    protected function setUp() : void
    {
        $this->reader = new JsonReader();
        $this->writer = new JsonWriter();
    }

    public function testNoSection()
    {
        $config = new Config(['test' => 'foo', 'test2' => ['test3' => 'bar']]);

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('foo', $config['test']);
        self::assertEquals('bar', $config['test2']['test3']);
    }

    public function testWriteAndReadOriginalFile()
    {
        $config = $this->reader->fromFile(__DIR__ . '/_files/allsections.json');

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('multi', $config['all']['one']['two']['three']);
    }
}
