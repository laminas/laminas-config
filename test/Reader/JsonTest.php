<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Reader;

use Laminas\Config\Exception;
use Laminas\Config\Reader\Json;

/**
 * @group      Laminas_Config
 */
class JsonTest extends AbstractReaderTestCase
{
    public function setUp()
    {
        $this->reader = new Json();
    }

    /**
     * getTestAssetPath(): defined by AbstractReaderTestCase.
     *
     * @see    AbstractReaderTestCase::getTestAssetPath()
     * @return string
     */
    protected function getTestAssetPath($name)
    {
        return __DIR__ . '/TestAssets/Json/' . $name . '.json';
    }

    public function testInvalidJsonFile()
    {
        $this->expectException(Exception\RuntimeException::class);
        $arrayJson = $this->reader->fromFile($this->getTestAssetPath('invalid'));
    }

    public function testIncludeAsElement()
    {
        $arrayJson = $this->reader->fromFile($this->getTestAssetPath('include-base_nested'));
        $this->assertEquals($arrayJson['bar']['foo'], 'foo');
    }

    public function testFromString()
    {
        $json = '{ "test" : "foo", "bar" : [ "baz", "foo" ] }';

        $arrayJson = $this->reader->fromString($json);

        $this->assertEquals($arrayJson['test'], 'foo');
        $this->assertEquals($arrayJson['bar'][0], 'baz');
        $this->assertEquals($arrayJson['bar'][1], 'foo');
    }

    public function testInvalidString()
    {
        $json = '{"foo":"bar"';

        $this->expectException(Exception\RuntimeException::class);
        $arrayIni = $this->reader->fromString($json);
    }
}
