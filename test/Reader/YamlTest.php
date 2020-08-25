<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Reader;

use Laminas\Config\Exception;
use Laminas\Config\Reader\Yaml as YamlReader;

/**
 * @group      Laminas_Config
 */
class YamlTest extends AbstractReaderTestCase
{
    protected function setUp() : void
    {
        if (! getenv('TESTS_LAMINAS_CONFIG_YAML_ENABLED')) {
            $this->markTestSkipped('Yaml test for Laminas\Config skipped');
        }

        if ($lib = getenv('TESTS_LAMINAS_CONFIG_YAML_LIB_INCLUDE')) {
            require_once $lib;
        }

        if ($readerCalback = getenv('TESTS_LAMINAS_CONFIG_READER_YAML_CALLBACK')) {
            $yamlReader = explode('::', $readerCalback);
            if (isset($yamlReader[1])) {
                $this->reader = new YamlReader([$yamlReader[0], $yamlReader[1]]);
            } else {
                $this->reader = new YamlReader([$yamlReader[0]]);
            }
        } else {
            $this->reader = new YamlReader();
        }
    }

    /**
     * getTestAssetPath(): defined by AbstractReaderTestCase.
     *
     * @see    AbstractReaderTestCase::getTestAssetPath()
     * @return string
     */
    protected function getTestAssetPath($name)
    {
        return __DIR__ . '/TestAssets/Yaml/' . $name . '.yaml';
    }

    public function testInvalidIniFile()
    {
        $this->expectException(Exception\RuntimeException::class);
        $arrayIni = $this->reader->fromFile($this->getTestAssetPath('invalid'));
    }

    public function testFromString()
    {
        $yaml = <<<ECS
test: foo
bar:
    baz
    foo

ECS;

        $arrayYaml = $this->reader->fromString($yaml);
        self::assertEquals($arrayYaml['test'], 'foo');
        self::assertEquals($arrayYaml['bar'][0], 'baz');
        self::assertEquals($arrayYaml['bar'][1], 'foo');
    }

    public function testFromStringWithSection()
    {
        $yaml = <<<ECS
all:
    test: foo
    bar:
        baz
        foo

ECS;

        $arrayYaml = $this->reader->fromString($yaml);
        self::assertEquals($arrayYaml['all']['test'], 'foo');
        self::assertEquals($arrayYaml['all']['bar'][0], 'baz');
        self::assertEquals($arrayYaml['all']['bar'][1], 'foo');
    }
}
