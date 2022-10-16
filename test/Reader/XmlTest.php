<?php

declare(strict_types=1);

namespace LaminasTest\Config\Reader;

use Error;
use Laminas\Config\Exception;
use Laminas\Config\Reader\Xml;
use ReflectionProperty;
use XMLReader;

use function restore_error_handler;
use function sys_get_temp_dir;

/**
 * @group      Laminas_Config
 * @covers \Laminas\Config\Reader\Xml
 */
class XmlTest extends AbstractReaderTestCase
{
    protected function setUp(): void
    {
        $this->reader = new Xml();
    }

    protected function tearDown(): void
    {
        restore_error_handler();
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
        return __DIR__ . '/TestAssets/Xml/' . $name . '.xml';
    }

    public function testInvalidXmlFile()
    {
        $this->reader = new Xml();
        $this->expectException(Exception\RuntimeException::class);
        $this->reader->fromFile($this->getTestAssetPath('invalid'));
    }

    public function testFromString()
    {
        $xml = <<<ECS
            <?xml version="1.0" encoding="UTF-8"?>
            <laminas-config>
                <test>foo</test>
                <bar>baz</bar>
                <bar>foo</bar>
            </laminas-config>
            
            ECS;

        $arrayXml = $this->reader->fromString($xml);
        self::assertEquals($arrayXml['test'], 'foo');
        self::assertEquals($arrayXml['bar'][0], 'baz');
        self::assertEquals($arrayXml['bar'][1], 'foo');
    }

    public function testInvalidString()
    {
        $xml = <<<ECS
            <?xml version="1.0" encoding="UTF-8"?>
            <laminas-config>
                <bar>baz</baz>
            </laminas-config>
            
            ECS;
        $this->expectException(Exception\RuntimeException::class);
        $this->reader->fromString($xml);
    }

    public function testLaminas00MultipleKeysOfTheSameName()
    {
        $config = $this->reader->fromFile($this->getTestAssetPath('array'));

        self::assertEquals('2a', $config['one']['two'][0]);
        self::assertEquals('2b', $config['one']['two'][1]);
        self::assertEquals('4', $config['three']['four'][1]);
        self::assertEquals('5', $config['three']['four'][0]['five']);
    }

    public function testLaminas00ArraysWithMultipleChildren()
    {
        $config = $this->reader->fromFile($this->getTestAssetPath('array'));

        self::assertEquals('1', $config['six']['seven'][0]['eight']);
        self::assertEquals('2', $config['six']['seven'][1]['eight']);
        self::assertEquals('3', $config['six']['seven'][2]['eight']);
        self::assertEquals('1', $config['six']['seven'][0]['nine']);
        self::assertEquals('2', $config['six']['seven'][1]['nine']);
        self::assertEquals('3', $config['six']['seven'][2]['nine']);
    }

    /**
     * @group laminas6279
     */
    public function testElementWithBothAttributesAndAStringValueIsProcessedCorrectly()
    {
        $this->reader = new Xml();
        $arrayXml     = $this->reader->fromFile($this->getTestAssetPath('attributes'));
        self::assertArrayHasKey('one', $arrayXml);
        self::assertIsArray($arrayXml['one']);

        // No attribute + text value == string
        self::assertArrayHasKey(0, $arrayXml['one']);
        self::assertEquals('bazbat', $arrayXml['one'][0]);

        // Attribute(s) + text value == array
        self::assertArrayHasKey(1, $arrayXml['one']);
        self::assertIsArray($arrayXml['one'][1]);
        // Attributes stored in named array keys
        self::assertArrayHasKey('foo', $arrayXml['one'][1]);
        self::assertEquals('bar', $arrayXml['one'][1]['foo']);
        // Element value stored in special key '_'
        self::assertArrayHasKey('_', $arrayXml['one'][1]);
        self::assertEquals('bazbat', $arrayXml['one'][1]['_']);
    }

    /**
     * @group 6761
     * @group 6730
     */
    public function testReadNonExistingFilesWillFailWithException()
    {
        $configReader = new Xml();

        $this->expectException(Exception\RuntimeException::class);

        $configReader->fromFile(sys_get_temp_dir() . '/path/that/does/not/exist');
    }

    /**
     * @group 6761
     * @group 6730
     */
    public function testCloseWhenCallFromFileReaderGetInvalid()
    {
        $configReader = new Xml();

        $configReader->fromFile($this->getTestAssetPath('attributes'));

        $xmlReader = $this->getInternalXmlReader($configReader);

        $this->expectException(Error::class);

        // following operation should fail because the internal reader is closed (and expected to be closed)
        $xmlReader->setParserProperty(XMLReader::VALIDATE, true);
    }

    /**
     * @group 6761
     * @group 6730
     */
    public function testCloseWhenCallFromStringReaderGetInvalid()
    {
        $xml = <<<ECS
            <?xml version="1.0" encoding="UTF-8"?>
            <laminas-config>
                <test>foo</test>
                <bar>baz</bar>
                <bar>foo</bar>
            </laminas-config>
            
            ECS;

        $configReader = new Xml();

        $configReader->fromString($xml);

        $xmlReader = $this->getInternalXmlReader($configReader);

        $this->expectException(Error::class);

        // following operation should fail because the internal reader is closed (and expected to be closed)
        $xmlReader->setParserProperty(XMLReader::VALIDATE, true);
    }

    /**
     * Reads the internal XML reader from a given Xml config reader
     *
     * @return XMLReader
     */
    private function getInternalXmlReader(Xml $xml)
    {
        $reflectionReader = new ReflectionProperty(Xml::class, 'reader');

        $reflectionReader->setAccessible(true);

        $xmlReader = $reflectionReader->getValue($xml);

        self::assertInstanceOf('XMLReader', $xmlReader);

        return $xmlReader;
    }
}
