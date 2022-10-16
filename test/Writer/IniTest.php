<?php

declare(strict_types=1);

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Reader\Ini as IniReader;
use Laminas\Config\Writer\Ini as IniWriter;

/**
 * @group      Laminas_Config
 */
class IniTest extends AbstractWriterTestCase
{
    protected function setUp(): void
    {
        $this->reader = new IniReader();
        $this->writer = new IniWriter();
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
        $config = $this->reader->fromFile(__DIR__ . '/_files/allsections.ini');

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('multi', $config['all']['one']['two']['three']);
    }
}
