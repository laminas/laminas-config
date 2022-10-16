<?php

declare(strict_types=1);

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Exception\InvalidArgumentException;
use Laminas\Config\Exception\RuntimeException;
use Laminas\Config\Reader\ReaderInterface;
use Laminas\Config\Writer\WriterInterface;
use PHPUnit\Framework\TestCase;

use function chmod;
use function file_exists;
use function getenv;
use function is_writable;
use function sys_get_temp_dir;
use function system;
use function tempnam;
use function unlink;

/**
 * @group      Laminas_Config
 */
abstract class AbstractWriterTestCase extends TestCase
{
    /** @var ReaderInterface */
    protected $reader;

    /** @var WriterInterface */
    protected $writer;

    /** @var string */
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

    protected function tearDown(): void
    {
        $testAssetFileName = $this->getTestAssetFileName();
        if (file_exists($testAssetFileName)) {
            if (getenv('USER') === 'root') {
                system('chattr -i ' . $testAssetFileName);
            }
            if (! is_writable($testAssetFileName)) {
                chmod($testAssetFileName, 0777);
            }
            @unlink($testAssetFileName);
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
        $testAssetFileName = $this->getTestAssetFileName();
        chmod($testAssetFileName, 0444);
        if (getenv('USER') === 'root') {
            system('chattr +i ' . $testAssetFileName);
        }
        $this->expectException(RuntimeException::class);
        $this->writer->toFile($testAssetFileName, new Config([]));
    }

    public function testWriteAndRead()
    {
        $config = new Config(['default' => ['test' => 'foo']]);

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        self::assertEquals('foo', $config['default']['test']);
    }
}
