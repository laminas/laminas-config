<?php

declare(strict_types=1);

namespace LaminasTest\Config;

use Laminas\Config\Exception;
use Laminas\Config\StandaloneWriterPluginManager;
use Laminas\Config\Writer;
use PHPUnit\Framework\TestCase;

use function sprintf;

class StandaloneWriterPluginManagerTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function supportedConfigTypes()
    {
        return [
            'ini'      => ['ini', Writer\Ini::class],
            'INI'      => ['INI', Writer\Ini::class],
            'json'     => ['json', Writer\Json::class],
            'JSON'     => ['JSON', Writer\Json::class],
            'php'      => ['php', Writer\PhpArray::class],
            'PHP'      => ['PHP', Writer\PhpArray::class],
            'phparray' => ['phparray', Writer\PhpArray::class],
            'phpArray' => ['phpArray', Writer\PhpArray::class],
            'PhpArray' => ['PhpArray', Writer\PhpArray::class],
            'xml'      => ['xml', Writer\Xml::class],
            'XML'      => ['XML', Writer\Xml::class],
            'yaml'     => ['yaml', Writer\Yaml::class],
            'YAML'     => ['YAML', Writer\Yaml::class],
        ];
    }

    /**
     * @dataProvider supportedConfigTypes
     * @param string $type Configuration type.
     * @param string $expectedType Expected plugin class.
     */
    public function testCanRetrieveWriterByType($type, $expectedType)
    {
        $manager = new StandaloneWriterPluginManager();
        self::assertTrue(
            $manager->has($type),
            sprintf('Failed to assert plugin manager has plugin %s', $type)
        );

        $plugin = $manager->get($type);
        self::assertInstanceOf($expectedType, $plugin);
    }

    /**
     * @return string[][]
     */
    public function supportedConfigClassNames()
    {
        return [
            Writer\Ini::class      => [Writer\Ini::class],
            Writer\Json::class     => [Writer\Json::class],
            Writer\PhpArray::class => [Writer\PhpArray::class],
            Writer\Xml::class      => [Writer\Xml::class],
            Writer\Yaml::class     => [Writer\Yaml::class],
        ];
    }

    /**
     * @dataProvider supportedConfigClassNames
     * @param string $class Plugin class to retrieve and expect.
     */
    public function testCanRetrieveWriterByPluginClassName($class)
    {
        $manager = new StandaloneWriterPluginManager();
        self::assertTrue(
            $manager->has($class),
            sprintf('Failed to assert plugin manager has plugin %s', $class)
        );

        $plugin = $manager->get($class);
        self::assertInstanceOf($class, $plugin);
    }

    public function testGetThrowsExceptionIfPluginNotFound()
    {
        $manager = new StandaloneWriterPluginManager();
        $this->expectException(Exception\PluginNotFoundException::class);
        $this->expectExceptionMessage('Config writer plugin by name bogus not found');
        $manager->get('bogus');
    }
}
