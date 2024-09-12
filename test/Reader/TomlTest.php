<?php

declare(strict_types=1);

namespace LaminasTest\Config\Reader;

use Laminas\Config\Exception;
use Laminas\Config\Reader\Toml;

/**
 * @group      Laminas_Config
 */
class TomlTest extends AbstractReaderTestCase
{
    protected function setUp(): void
    {
        $this->reader = new Toml();
    }

    /**
     * getTestAssetPath(): defined by AbstractReaderTestCase.
     *
     * @see    AbstractReaderTestCase::getTestAssetPath()
     *
     * @param  string $name
     * @return string
     */
    protected function getTestAssetPath($name)
    {
        return __DIR__ . '/TestAssets/Toml/' . $name . '.toml';
    }

    public function testInvalidTomlFile()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->reader->fromFile($this->getTestAssetPath('invalid'));
    }

    public function testIncludeAsElement()
    {
        $arrayToml = $this->reader->fromFile($this->getTestAssetPath('include-base_nested'));
        self::assertEquals($arrayToml['bar']['foo'], 'foo');
    }

    public function testFromString()
    {
        $toml = "test = \"foo\"\nbar = [ \"baz\", \"foo\" ]";

        $arrayToml = $this->reader->fromString($toml);

        self::assertEquals($arrayToml['test'], 'foo');
        self::assertEquals($arrayToml['bar'][0], 'baz');
        self::assertEquals($arrayToml['bar'][1], 'foo');
    }

    public function testInvalidString()
    {
        $toml = '"foo":"bar"';

        $this->expectException(Exception\RuntimeException::class);
        $this->reader->fromString($toml);
    }
}
