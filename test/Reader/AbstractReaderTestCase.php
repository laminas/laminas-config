<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Reader;

use Laminas\Config\Exception;
use Laminas\Config\Reader\ReaderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Config
 */
abstract class AbstractReaderTestCase extends TestCase
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * Get test asset name for current test case.
     *
     * @param  string $name
     * @return string
     */
    abstract protected function getTestAssetPath($name);

    public function testMissingFile()
    {
        $filename = $this->getTestAssetPath('no-file');
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage("doesn't exist or not readable");
        $config = $this->reader->fromFile($filename);
    }

    public function testFromFile()
    {
        $config = $this->reader->fromFile($this->getTestAssetPath('include-base'));
        $this->assertEquals('foo', $config['foo']);
    }

    public function testFromEmptyString()
    {
        $config = $this->reader->fromString('');
        $this->assertEmpty($config);
    }
}
