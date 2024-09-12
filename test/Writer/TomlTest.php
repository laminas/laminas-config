<?php

declare(strict_types=1);

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Reader\Toml as TomlReader;
use Laminas\Config\Writer\Toml as TomlWriter;

/**
 * @group      Laminas_Config
 */
class TomlTest extends AbstractWriterTestCase
{
    protected function setUp(): void
    {
        $this->reader = new TomlReader();
        $this->writer = new TomlWriter();
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
        $config = $this->reader->fromFile(__DIR__ . '/_files/allsections.toml');

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('multi', $config['all']['one']['two']['three']);
    }
}
