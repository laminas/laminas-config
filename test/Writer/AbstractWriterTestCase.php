<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Exception\InvalidArgumentException;
use Laminas\Config\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Config
 */
abstract class AbstractWriterTestCase extends TestCase
{
    /**
     * @var \Laminas\Config\Reader\ReaderInterface
     */
    protected $reader;

    /**
     *
     * @var \Laminas\Config\Writer\WriterInterface
     */
    protected $writer;

    /**
     *
     * @var string
     */
    protected $tmpfile;

    /**
     * Get test asset name for current test case.
     *
     * @return string
     */
    protected function getTestAssetFileName()
    {
        if (empty($this->tmpfile)) {
            $this->tmpfile = tempnam(sys_get_temp_dir(), 'laminas-config-writer');
        }
        return $this->tmpfile;
    }

    protected function tearDown() : void
    {
        if (file_exists($this->getTestAssetFileName())) {
            if (! is_writable($this->getTestAssetFileName())) {
                chmod($this->getTestAssetFileName(), 0777);
            }
            @unlink($this->getTestAssetFileName());
        }
    }

    public function testNoFilenameSet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No file name specified');
        $this->writer->toFile('', '');
    }

    public function testFileNotValid()
    {
        $this->expectException(RuntimeException::class);
        $this->writer->toFile('.', new Config([]));
    }

    public function testFileNotWritable()
    {
        $this->expectException(RuntimeException::class);
        chmod($this->getTestAssetFileName(), 0444);
        $this->writer->toFile($this->getTestAssetFileName(), new Config([]));
    }

    public function testWriteAndRead()
    {
        $config = new Config(['default' => ['test' => 'foo']]);

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('foo', $config['default']['test']);
    }
}
