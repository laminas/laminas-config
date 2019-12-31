<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @category   Laminas
 * @package    Laminas_Config
 * @subpackage UnitTests
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

    public function tearDown()
    {
        if (file_exists($this->getTestAssetFileName())) {
            if (!is_writable($this->getTestAssetFileName())) {
                chmod($this->getTestAssetFileName(), 0777);
            }
            @unlink($this->getTestAssetFileName());
        }
    }

    public function testNoFilenameSet()
    {
        $this->setExpectedException('Laminas\Config\Exception\InvalidArgumentException', 'No file name specified');
        $this->writer->toFile('', '');
    }

    public function testFileNotValid()
    {
        $this->setExpectedException('Laminas\Config\Exception\RuntimeException');
        $this->writer->toFile('.', new Config(array()));
    }

    public function testFileNotWritable()
    {
        $this->setExpectedException('Laminas\Config\Exception\RuntimeException');
        chmod($this->getTestAssetFileName(), 0444);
        $this->writer->toFile($this->getTestAssetFileName(), new Config(array()));
    }

    public function testWriteAndRead()
    {
        $config = new Config(array('default' => array('test' => 'foo')));

        $this->writer->toFile($this->getTestAssetFileName(), $config);

        $config = $this->reader->fromFile($this->getTestAssetFileName());

        $this->assertEquals('foo', $config['default']['test']);
    }
}
