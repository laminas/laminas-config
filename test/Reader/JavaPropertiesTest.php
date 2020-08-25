<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Reader;

use Laminas\Config\Exception;
use Laminas\Config\Reader\JavaProperties;

/**
 * @group      Laminas_Config
 */
class JavaPropertiesTest extends AbstractReaderTestCase
{
    protected function setUp() : void
    {
        $this->reader = new JavaProperties();
    }

    /**
     * getTestAssetPath(): defined by AbstractReaderTestCase.
     *
     * @see    AbstractReaderTestCase::getTestAssetPath()
     * @return string
     */
    protected function getTestAssetPath($name)
    {
        return __DIR__ . '/TestAssets/JavaProperties/' . $name . '.properties';
    }

    public function testFromFile()
    {
        $arrayJavaProperties = $this->reader->fromFile($this->getTestAssetPath('include-target'));

        self::assertNotEmpty($arrayJavaProperties);
        self::assertEquals($arrayJavaProperties['single.line'], 'test');
        self::assertEquals($arrayJavaProperties['multiple'], 'line test');
    }

    public function testIncludeAsElement()
    {
        $arrayJavaProperties = $this->reader->fromFile($this->getTestAssetPath('include-base'));

        self::assertNotEmpty($arrayJavaProperties);
        self::assertEquals($arrayJavaProperties['single.line'], 'test');
        self::assertEquals($arrayJavaProperties['multiple'], 'line test');
    }

    public function testFromString()
    {
        $JavaProperties = <<<'ASSET'
#comment
!comment
single.line:test
multiple:line \
test
ASSET;

        $arrayJavaProperties = $this->reader->fromString($JavaProperties);

        self::assertNotEmpty($arrayJavaProperties);
        self::assertEquals($arrayJavaProperties['single.line'], 'test');
        self::assertEquals($arrayJavaProperties['multiple'], 'line test');
    }

    public function testInvalidIncludeInString()
    {
        $JavaProperties = '@include:fail.properties';

        $expectedErrorMessage = 'Cannot process @include statement for a string';

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $arrayJavaPropterties = $this->reader->fromString($JavaProperties);
    }

    public function testAllowsSpecifyingAlternateKeyValueDelimiter()
    {
        $reader = new JavaProperties('=');

        $arrayJavaProperties = $reader->fromFile($this->getTestAssetPath('alternate-delimiter'));

        self::assertNotEmpty($arrayJavaProperties);
        self::assertEquals($arrayJavaProperties['single.line'], 'test');
        self::assertEquals($arrayJavaProperties['multiple'], 'line test');
    }

    public function invalidDelimiters()
    {
        return [
            'null'         => [null],
            'true'         => [true],
            'false'        => [false],
            'zero'         => [0],
            'int'          => [1],
            'zero-float'   => [0.0],
            'float'        => [1.1],
            'empty-string' => [''],
            'array'        => [[':']],
            'object'       => [(object) ['delimiter' => ':']],
        ];
    }

    /**
     * @dataProvider invalidDelimiters
     * @param mixed $delimiter
     */
    public function testInvalidDelimiterValuesResultInExceptions($delimiter)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new JavaProperties($delimiter);
    }

    public function testProvidesOptionToTrimWhitespaceFromKeysAndValues()
    {
        $reader = new JavaProperties(JavaProperties::DELIMITER_DEFAULT, JavaProperties::WHITESPACE_TRIM);
        $arrayJavaProperties = $reader->fromFile($this->getTestAssetPath('key-value-whitespace'));

        self::assertNotEmpty($arrayJavaProperties);
        self::assertEquals($arrayJavaProperties['single.line'], 'test');
        self::assertEquals($arrayJavaProperties['multiple'], 'line test');
    }
}
