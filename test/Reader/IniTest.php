<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Reader;

use Laminas\Config\Exception;
use Laminas\Config\Reader\Ini;

/**
 * @group      Laminas_Config
 */
class IniTest extends AbstractReaderTestCase
{
    protected function setUp() : void
    {
        $this->reader = new Ini();
    }

    /**
     * getTestAssetPath(): defined by AbstractReaderTestCase.
     *
     * @see    AbstractReaderTestCase::getTestAssetPath()
     * @return string
     */
    protected function getTestAssetPath($name)
    {
        return __DIR__ . '/TestAssets/Ini/' . $name . '.ini';
    }

    public function testInvalidIniFile()
    {
        $this->reader = new Ini();
        $this->expectException(Exception\RuntimeException::class);
        $arrayIni = $this->reader->fromFile($this->getTestAssetPath('invalid'));
    }

    public function testFromString()
    {
        $ini = <<<ECS
test= "foo"
bar[]= "baz"
bar[]= "foo"

ECS;

        $arrayIni = $this->reader->fromString($ini);
        self::assertEquals('foo', $arrayIni['test']);
        self::assertEquals('baz', $arrayIni['bar'][0]);
        self::assertEquals('foo', $arrayIni['bar'][1]);
    }

    public function testInvalidString()
    {
        $ini = <<<ECS
test== "foo"

ECS;
        $this->expectException(Exception\RuntimeException::class);
        $arrayIni = $this->reader->fromString($ini);
    }

    public function testFromStringWithSection()
    {
        $ini = <<<ECS
[all]
test= "foo"
bar[]= "baz"
bar[]= "foo"

ECS;

        $arrayIni = $this->reader->fromString($ini);
        self::assertEquals('foo', $arrayIni['all']['test']);
        self::assertEquals('baz', $arrayIni['all']['bar'][0]);
        self::assertEquals('foo', $arrayIni['all']['bar'][1]);
    }

    public function testFromStringNested()
    {
        $ini = <<<ECS
bla.foo.bar = foobar
bla.foobar[] = foobarArray
bla.foo.baz[] = foobaz1
bla.foo.baz[] = foobaz2

ECS;

        $arrayIni = $this->reader->fromString($ini);
        self::assertEquals('foobar', $arrayIni['bla']['foo']['bar']);
        self::assertEquals('foobarArray', $arrayIni['bla']['foobar'][0]);
        self::assertEquals('foobaz1', $arrayIni['bla']['foo']['baz'][0]);
        self::assertEquals('foobaz2', $arrayIni['bla']['foo']['baz'][1]);
    }

    public function testFromFileParseSections()
    {
        $arrayIni = $this->reader->fromFile($this->getTestAssetPath('sections'));

        self::assertEquals('production', $arrayIni['production']['env']);
        self::assertEquals('foo', $arrayIni['production']['production_key']);
        self::assertEquals('staging', $arrayIni['staging : production']['env']);
        self::assertEquals('bar', $arrayIni['staging : production']['staging_key']);
    }

    public function testFromFileDontParseSections()
    {
        $reader = $this->reader;
        $reader->setProcessSections(false);

        $arrayIni = $reader->fromFile($this->getTestAssetPath('sections'));

        self::assertEquals('staging', $arrayIni['env']);
        self::assertEquals('foo', $arrayIni['production_key']);
        self::assertEquals('bar', $arrayIni['staging_key']);
    }

    public function testFromFileIgnoresNestingInSectionNamesWhenSectionsNotProcessed()
    {
        $reader = $this->reader;
        $reader->setProcessSections(false);

        $arrayIni = $reader->fromFile($this->getTestAssetPath('nested-sections'));

        self::assertArrayNotHasKey('environments.production', $arrayIni);
        self::assertArrayNotHasKey('environments.staging', $arrayIni);
        self::assertArrayNotHasKey('environments', $arrayIni);
        self::assertArrayNotHasKey('production', $arrayIni);
        self::assertArrayNotHasKey('staging', $arrayIni);
        self::assertEquals('staging', $arrayIni['env']);
        self::assertEquals('foo', $arrayIni['production_key']);
        self::assertEquals('bar', $arrayIni['staging_key']);
    }

    public function testFromStringParseSections()
    {
        $ini = <<<ECS
[production]
env='production'
production_key='foo'

[staging : production]
env='staging'
staging_key='bar'

ECS;
        $arrayIni = $this->reader->fromString($ini);

        self::assertEquals('production', $arrayIni['production']['env']);
        self::assertEquals('foo', $arrayIni['production']['production_key']);
        self::assertEquals('staging', $arrayIni['staging : production']['env']);
        self::assertEquals('bar', $arrayIni['staging : production']['staging_key']);
    }

    public function testFromStringDontParseSections()
    {
        $ini = <<<ECS
[production]
env='production'
production_key='foo'

[staging : production]
env='staging'
staging_key='bar'

ECS;
        $reader = $this->reader;
        $reader->setProcessSections(false);

        $arrayIni = $reader->fromString($ini);

        self::assertEquals('staging', $arrayIni['env']);
        self::assertEquals('foo', $arrayIni['production_key']);
        self::assertEquals('bar', $arrayIni['staging_key']);
    }

    public function testFromStringIgnoresNestingInSectionNamesWhenSectionsNotProcessed()
    {
        $ini = <<<ECS
[environments.production]
env='production'
production_key='foo'

[environments.staging]
env='staging'
staging_key='bar'
ECS;
        $reader = $this->reader;
        $reader->setProcessSections(false);

        $arrayIni = $reader->fromString($ini);

        self::assertArrayNotHasKey('environments.production', $arrayIni);
        self::assertArrayNotHasKey('environments.staging', $arrayIni);
        self::assertArrayNotHasKey('environments', $arrayIni);
        self::assertArrayNotHasKey('production', $arrayIni);
        self::assertArrayNotHasKey('staging', $arrayIni);
        self::assertEquals('staging', $arrayIni['env']);
        self::assertEquals('foo', $arrayIni['production_key']);
        self::assertEquals('bar', $arrayIni['staging_key']);
    }

    public function testFromFileWithoutTypes()
    {
        $arrayIni = $this->reader->fromFile($this->getTestAssetPath('types'));

        self::assertSame('Bob Smith', $arrayIni['production']['name']);
        self::assertSame('55', $arrayIni['production']['age']);
        self::assertSame('55', $arrayIni['production']['age_str']);
        self::assertSame('1', $arrayIni['production']['is_married']);
        self::assertSame('', $arrayIni['production']['is_employed']);
        self::assertSame('', $arrayIni['production']['employer']);
    }

    public function testFromFileWithTypes()
    {
        $reader = $this->reader;
        $reader->setTypedMode(true);
        $arrayIni = $reader->fromFile($this->getTestAssetPath('types'));

        self::assertSame('Bob Smith', $arrayIni['production']['name']);
        self::assertSame(55, $arrayIni['production']['age']);
        self::assertSame('55', $arrayIni['production']['age_str']);
        self::assertSame(true, $arrayIni['production']['is_married']);
        self::assertSame(false, $arrayIni['production']['is_employed']);
        self::assertSame(null, $arrayIni['production']['employer']);
    }

    public function testFromStringWithoutTypes()
    {
        $ini = <<<ECS
[production]
name="Bob Smith"
age=55
age_str="55"
is_married=yes
is_employed=FALSE
employer=null
ECS;
        $arrayIni = $this->reader->fromString($ini);

        self::assertSame('Bob Smith', $arrayIni['production']['name']);
        self::assertSame('55', $arrayIni['production']['age']);
        self::assertSame('55', $arrayIni['production']['age_str']);
        self::assertSame('1', $arrayIni['production']['is_married']);
        self::assertSame('', $arrayIni['production']['is_employed']);
        self::assertSame('', $arrayIni['production']['employer']);
    }

    public function testFromStringWithTypes()
    {
        $ini = <<<ECS
[production]
name="Bob Smith"
age=55
age_str="55"
is_married=yes
is_employed=FALSE
employer=null
ECS;
        $reader = $this->reader;
        $reader->setTypedMode(true);
        $arrayIni = $reader->fromString($ini);

        self::assertSame('Bob Smith', $arrayIni['production']['name']);
        self::assertSame(55, $arrayIni['production']['age']);
        self::assertSame('55', $arrayIni['production']['age_str']);
        self::assertSame(true, $arrayIni['production']['is_married']);
        self::assertSame(false, $arrayIni['production']['is_employed']);
        self::assertSame(null, $arrayIni['production']['employer']);
    }
}
