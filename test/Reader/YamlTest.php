<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Reader;

use Laminas\Config\Reader\Yaml as YamlReader;

/**
 * @group      Laminas_Config
 */
class YamlTest extends AbstractReaderTestCase
{
    public function setUp()
    {
        if (!getenv('TESTS_LAMINAS_CONFIG_YAML_ENABLED')) {
            $this->markTestSkipped('Yaml test for Laminas\Config skipped');
        }

        if (getenv('TESTS_LAMINAS_CONFIG_YAML_LIB_INCLUDE')) {
            require_once getenv('TESTS_LAMINAS_CONFIG_YAML_LIB_INCLUDE');
        }

        $yamlReader = explode('::', getenv('TESTS_LAMINAS_CONFIG_READER_YAML_CALLBACK'));
        if (isset($yamlReader[1])) {
            $this->reader = new YamlReader([$yamlReader[0], $yamlReader[1]]);
        } else {
            $this->reader = new YamlReader([$yamlReader[0]]);
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
        $this->setExpectedException('Laminas\Config\Exception\RuntimeException');
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
        $this->assertEquals($arrayYaml['test'], 'foo');
        $this->assertEquals($arrayYaml['bar'][0], 'baz');
        $this->assertEquals($arrayYaml['bar'][1], 'foo');
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
        $this->assertEquals($arrayYaml['all']['test'], 'foo');
        $this->assertEquals($arrayYaml['all']['bar'][0], 'baz');
        $this->assertEquals($arrayYaml['all']['bar'][1], 'foo');
    }
}
