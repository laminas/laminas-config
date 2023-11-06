<?php

declare(strict_types=1);

namespace LaminasTest\Config;

use interop\container\containerinterface;
use InvalidArgumentException;
use Laminas\Config\Config;
use Laminas\Config\Factory;
use Laminas\Config\ReaderPluginManager;
use Laminas\Config\StandaloneReaderPluginManager;
use Laminas\Config\StandaloneWriterPluginManager;
use Laminas\Config\WriterPluginManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

use function chmod;
use function file_exists;
use function file_get_contents;
use function get_include_path;
use function is_writable;
use function set_include_path;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @group      Laminas_Config
 */
class FactoryTest extends TestCase
{
    /** @var array */
    protected $tmpFiles = [];
    /** @var false|string */
    protected $originalIncludePath;

    /**
     * @param string $ext
     * @return mixed|string
     */
    protected function getTestAssetFileName($ext)
    {
        if (empty($this->tmpFiles[$ext])) {
            $this->tmpFiles[$ext] = tempnam(sys_get_temp_dir(), 'laminas-config-writer') . '.' . $ext;
        }
        return $this->tmpFiles[$ext];
    }

    protected function setUp(): void
    {
        $this->originalIncludePath = get_include_path();
        set_include_path(__DIR__ . '/TestAssets');
        $this->resetPluginManagers();
    }

    protected function tearDown(): void
    {
        set_include_path($this->originalIncludePath);

        foreach ($this->tmpFiles as $file) {
            if (file_exists($file)) {
                if (! is_writable($file)) {
                    chmod($file, 0777);
                }
                @unlink($file);
            }
        }

        $this->resetPluginManagers();
    }

    public function resetPluginManagers()
    {
        foreach (['readers', 'writers'] as $pluginManager) {
            $r = new ReflectionProperty(Factory::class, $pluginManager);
            $r->setAccessible(true);
            $r->setValue(null);
        }
    }

    public function testFromIni()
    {
        $config = Factory::fromFile(__DIR__ . '/TestAssets/Ini/include-base.ini');

        self::assertEquals('bar', $config['base']['foo']);
    }

    public function testFromXml()
    {
        $config = Factory::fromFile(__DIR__ . '/TestAssets/Xml/include-base.xml');

        self::assertEquals('bar', $config['base']['foo']);
    }

    public function testFromIniFiles()
    {
        $files  = [
            __DIR__ . '/TestAssets/Ini/include-base.ini',
            __DIR__ . '/TestAssets/Ini/include-base2.ini',
        ];
        $config = Factory::fromFiles($files);

        self::assertEquals('bar', $config['base']['foo']);
        self::assertEquals('baz', $config['test']['bar']);
    }

    public function testFromXmlFiles()
    {
        $files  = [
            __DIR__ . '/TestAssets/Xml/include-base.xml',
            __DIR__ . '/TestAssets/Xml/include-base2.xml',
        ];
        $config = Factory::fromFiles($files);

        self::assertEquals('bar', $config['base']['foo']);
        self::assertEquals('baz', $config['test']['bar']);
    }

    public function testFromPhpFiles()
    {
        $files  = [
            __DIR__ . '/TestAssets/Php/include-base.php',
            __DIR__ . '/TestAssets/Php/include-base2.php',
        ];
        $config = Factory::fromFiles($files);

        self::assertEquals('bar', $config['base']['foo']);
        self::assertEquals('baz', $config['test']['bar']);
    }

    public function testFromIniAndXmlAndPhpFiles()
    {
        $files  = [
            __DIR__ . '/TestAssets/Ini/include-base.ini',
            __DIR__ . '/TestAssets/Xml/include-base2.xml',
            __DIR__ . '/TestAssets/Php/include-base3.php',
        ];
        $config = Factory::fromFiles($files);

        self::assertEquals('bar', $config['base']['foo']);
        self::assertEquals('baz', $config['test']['bar']);
        self::assertEquals('baz', $config['last']['bar']);
    }

    public function testFromIniAndXmlAndPhpFilesFromIncludePath()
    {
        $files  = [
            'Ini/include-base.ini',
            'Xml/include-base2.xml',
            'Php/include-base3.php',
        ];
        $config = Factory::fromFiles($files, false, true);

        self::assertEquals('bar', $config['base']['foo']);
        self::assertEquals('baz', $config['test']['bar']);
        self::assertEquals('baz', $config['last']['bar']);
    }

    public function testReturnsConfigObjectIfRequestedAndArrayOtherwise()
    {
        $files = [
            __DIR__ . '/TestAssets/Ini/include-base.ini',
        ];

        $configArray = Factory::fromFile($files[0]);
        self::assertIsArray($configArray);

        $configArray = Factory::fromFiles($files);
        self::assertIsArray($configArray);

        $configObject = Factory::fromFile($files[0], true);
        self::assertInstanceOf(Config::class, $configObject);

        $configObject = Factory::fromFiles($files, true);
        self::assertInstanceOf(Config::class, $configObject);
    }

    public function testNonExistentFileThrowsRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $config = Factory::fromFile('foo.bar');
    }

    public function testUnsupportedFileExtensionThrowsRuntimeException()
    {
        $this->expectException(RuntimeException::class);
        $config = Factory::fromFile(__DIR__ . '/TestAssets/bad.ext');
    }

    public function testFactoryCanRegisterCustomReaderInstance()
    {
        Factory::registerReader('dum', new Reader\TestAssets\DummyReader());

        $configObject = Factory::fromFile(__DIR__ . '/TestAssets/dummy.dum', true);
        self::assertInstanceOf(Config::class, $configObject);

        self::assertEquals($configObject['one'], 1);
    }

    public function testFactoryCanRegisterCustomReaderPlugin()
    {
        /** @var containerinterface&MockObject $services */
        $services      = $this->createMock(containerinterface::class);
        $pluginManager = new ReaderPluginManager($services, [
            'services' => [
                'DummyReader' => new Reader\TestAssets\DummyReader(),
            ],
        ]);
        Factory::setReaderPluginManager($pluginManager);
        Factory::registerReader('dum', 'DummyReader');

        $configObject = Factory::fromFile(__DIR__ . '/TestAssets/dummy.dum', true);
        self::assertInstanceOf(Config::class, $configObject);

        self::assertEquals($configObject['one'], 1);
    }

    public function testFactoryToFileInvalidFileExtension()
    {
        $this->expectException(RuntimeException::class);
        Factory::toFile(__DIR__ . '/TestAssets/bad.ext', []);
    }

    public function testFactoryToFileNoDirInHere()
    {
        $this->expectException(RuntimeException::class);
        Factory::toFile(__DIR__ . '/TestAssets/NoDirInHere/nonExisiting/dummy.php', []);
    }

    public function testFactoryWriteToFile()
    {
        $config = ['test' => 'foo', 'bar' => [0 => 'baz', 1 => 'foo']];

        $file   = $this->getTestAssetFileName('php');
        $result = Factory::toFile($file, $config);

        // build string line by line as we are trailing-whitespace sensitive.
        $expected  = "<?php\n";
        $expected .= "return array(\n";
        $expected .= "    'test' => 'foo',\n";
        $expected .= "    'bar' => array(\n";
        $expected .= "        0 => 'baz',\n";
        $expected .= "        1 => 'foo',\n";
        $expected .= "    ),\n";
        $expected .= ");\n";

        self::assertEquals(true, $result);
        self::assertEquals($expected, file_get_contents($file));
    }

    public function testFactoryToFileWrongConfig()
    {
        $this->expectException(InvalidArgumentException::class);
        $result = Factory::toFile('test.ini', 'Im wrong');
    }

    public function testFactoryRegisterInvalidWriter()
    {
        $this->expectException(InvalidArgumentException::class);
        Factory::registerWriter('dum', new Reader\TestAssets\DummyReader());
    }

    public function testFactoryCanRegisterCustomWriterInstance()
    {
        Factory::registerWriter('dum', new Writer\TestAssets\DummyWriter());

        $file = $this->getTestAssetFileName('dum');

        $res = Factory::toFile($file, ['one' => 1]);

        self::assertEquals($res, true);
    }

    public function testFactoryCanRegisterCustomWriterPlugin()
    {
        /** @var containerinterface&MockObject $services */
        $services      = $this->createMock(containerinterface::class);
        $pluginManager = new WriterPluginManager($services, [
            'services' => [
                'DummyWriter' => new Writer\TestAssets\DummyWriter(),
            ],
        ]);
        Factory::setWriterPluginManager($pluginManager);
        Factory::registerWriter('dum', 'DummyWriter');

        $file = $this->getTestAssetFileName('dum');

        $res = Factory::toFile($file, ['one' => 1]);
        self::assertEquals($res, true);
    }

    public function testDefaultReaderPluginManagerIsStandaloneVariant()
    {
        $readers = Factory::getReaderPluginManager();
        self::assertInstanceOf(StandaloneReaderPluginManager::class, $readers);
    }

    public function testDefaultWriterPluginManagerIsStandaloneVariant()
    {
        $writers = Factory::getWriterPluginManager();
        self::assertInstanceOf(StandaloneWriterPluginManager::class, $writers);
    }
}
