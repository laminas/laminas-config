<?php

namespace LaminasTest\Config;

use Laminas\Config\Config;
use Laminas\Config\Exception;
use PHPUnit\Framework\TestCase;

use function count;
use function is_string;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function print_r;
use function range;

/**
 * @group      Laminas_Config
 */
class ConfigTest extends TestCase
{
    protected $iniFileConfig;
    protected $iniFileNested;

    protected function setUp() : void
    {
        // Arrays representing common config configurations
        $this->all = [
            'hostname' => 'all',
            'name' => 'thisname',
            'db' => [
                'host' => '127.0.0.1',
                'user' => 'username',
                'pass' => 'password',
                'name' => 'live'
                ],
            'one' => [
                'two' => [
                    'three' => 'multi'
                    ]
                ]
            ];

        $this->numericData = [
             0 => 34,
             1 => 'test',
            ];

        $this->menuData1 = [
            'button' => [
                'b0' => [
                    'L1' => 'button0-1',
                    'L2' => 'button0-2',
                    'L3' => 'button0-3'
                ],
                'b1' => [
                    'L1' => 'button1-1',
                    'L2' => 'button1-2'
                ],
                'b2' => [
                    'L1' => 'button2-1'
                    ]
                ]
            ];

        $this->toCombineA = [
            'foo' => 1,
            'bar' => 2,
            'text' => 'foo',
            'numerical' => [
                'first',
                'second',
                [
                    'third'
                ]
            ],
            'misaligned' => [
                2 => 'foo',
                3 => 'bar'
            ],
            'mixed' => [
                'foo' => 'bar'
            ],
            'replaceAssoc' => [
                'foo' => 'bar'
            ],
            'replaceNumerical' => [
                'foo'
            ]
        ];

        $this->toCombineB = [
            'foo' => 3,
            'text' => 'bar',
            'numerical' => [
                'fourth',
                'fifth',
                [
                    'sixth'
                ]
            ],
            'misaligned' => [
                3 => 'baz'
            ],
            'mixed' => [
                false
            ],
            'replaceAssoc' => null,
            'replaceNumerical' => true
        ];

        $this->leadingdot = ['.test' => 'dot-test'];
        $this->invalidkey = [' ' => 'test', '' => 'test2'];
    }

    public function testLoadSingleSection()
    {
        $config = new Config($this->all, false);

        self::assertEquals('all', $config->hostname);
        self::assertEquals('live', $config->db->name);
        self::assertEquals('multi', $config->one->two->three);
        self::assertNull($config->nonexistent); // property doesn't exist
    }

    public function testIsset()
    {
        $config = new Config($this->all, false);

        self::assertFalse(isset($config->notarealkey));
        self::assertTrue(isset($config->hostname)); // top level
        self::assertTrue(isset($config->db->name)); // one level down
    }

    public function testModification()
    {
        $config = new Config($this->all, true);

        // overwrite an existing key
        self::assertEquals('thisname', $config->name);
        $config->name = 'anothername';
        self::assertEquals('anothername', $config->name);

        // overwrite an existing multi-level key
        self::assertEquals('multi', $config->one->two->three);
        $config->one->two->three = 'anothername';
        self::assertEquals('anothername', $config->one->two->three);

        // create a new multi-level key
        $config->does = ['not' => ['exist' => 'yet']];
        self::assertEquals('yet', $config->does->not->exist);
    }

    public function testNoModifications()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Config is read only');
        $config = new Config($this->all);
        $config->hostname = 'test';
    }

    public function testNoNestedModifications()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Config is read only');
        $config = new Config($this->all);
        $config->db->host = 'test';
    }

    public function testNumericKeys()
    {
        $data = new Config($this->numericData);
        self::assertEquals('test', $data->{1});
        self::assertEquals(34, $data->{0});
    }

    public function testCount()
    {
        $data = new Config($this->menuData1);
        self::assertCount(3, $data->button);
    }

    public function testCountAfterMerge()
    {
        $data = new Config($this->toCombineB);
        $data->merge(
            new Config($this->toCombineA)
        );
        self::assertEquals(count($data->toArray()), $data->count());
    }

    public function testCountWithDoubleKeys()
    {
        $config = new Config([], true);

        $config->foo = 1;
        $config->foo = 2;
        self::assertSame(2, $config->foo);
        self::assertCount(1, $config->toArray());
        self::assertCount(1, $config);
    }

    public function testIterator()
    {
        // top level
        $config = new Config($this->all);
        $var = '';
        foreach ($config as $key => $value) {
            if (is_string($value)) {
                $var .= "\nkey = $key, value = $value";
            }
        }
        self::assertStringContainsString('key = name, value = thisname', $var);

        // 1 nest
        $var = '';
        foreach ($config->db as $key => $value) {
            $var .= "\nkey = $key, value = $value";
        }
        self::assertStringContainsString('key = host, value = 127.0.0.1', $var);

        // 2 nests
        $config = new Config($this->menuData1);
        $var = '';
        foreach ($config->button->b1 as $key => $value) {
            $var .= "\nkey = $key, value = $value";
        }
        self::assertStringContainsString('key = L1, value = button1-1', $var);
    }

    public function testArray()
    {
        $config = new Config($this->all);

        ob_start();
        print_r($config->toArray());
        $contents = ob_get_contents();
        ob_end_clean();

        self::assertStringContainsString('Array', $contents);
        self::assertStringContainsString('[hostname] => all', $contents);
        self::assertStringContainsString('[user] => username', $contents);
    }

    public function testErrorWriteToReadOnly()
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Config is read only');
        $config = new Config($this->all);
        $config->test = '32';
    }

    public function testLaminas43()
    {
        $config_array = [
            'controls' => [
                'visible' => [
                    'name' => 'visible',
                    'type' => 'checkbox',
                    'attribs' => [], // empty array
                ],
            ],
        ];
        $form_config = new Config($config_array, true);
        self::assertSame([], $form_config->controls->visible->attribs->toArray());
    }

    public function testLaminas402()
    {
        $configArray = [
            'data1'  => 'someValue',
            'data2'  => 'someValue',
            'false1' => false,
            'data3'  => 'someValue'
            ];
        $config = new Config($configArray);
        self::assertEquals(count($configArray), count($config));
        foreach ($config as $key => $value) {
            self::assertEquals($configArray[$key], $value);
        }
    }

    public function testLaminas1019HandlingInvalidKeyNames()
    {
        $config = new Config($this->leadingdot);
        $array = $config->toArray();
        self::assertStringContainsString('dot-test', $array['.test']);
    }

    public function testLaminas1019EmptyKeys()
    {
        $config = new Config($this->invalidkey);
        $array = $config->toArray();
        self::assertStringContainsString('test', $array[' ']);
        self::assertStringContainsString('test', $array['']);
    }

    public function testLaminas1417DefaultValues()
    {
        $config = new Config($this->all);
        $value = $config->get('notthere', 'default');
        self::assertEquals('default', $value);
        self::assertNull($config->notThere);
    }

    public function testUnsetException()
    {
        // allow modifications is off - expect an exception
        $config = new Config($this->all, false);

        self::assertTrue(isset($config->hostname)); // top level

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('is read only');
        unset($config->hostname);
    }

    public function testUnset()
    {
        // allow modifications is on
        $config = new Config($this->all, true);

        self::assertTrue(isset($config->hostname));
        self::assertTrue(isset($config->db->name));

        unset($config->hostname);
        unset($config->db->name);

        self::assertFalse(isset($config->hostname));
        self::assertFalse(isset($config->db->name));
    }

    public function testMerge()
    {
        $configA = new Config($this->toCombineA);
        $configB = new Config($this->toCombineB);
        $configA->merge($configB);

        // config->
        self::assertEquals(3, $configA->foo);
        self::assertEquals(2, $configA->bar);
        self::assertEquals('bar', $configA->text);

        // config->numerical-> ...
        self::assertInstanceOf('\Laminas\Config\Config', $configA->numerical);
        self::assertEquals('first', $configA->numerical->{0});
        self::assertEquals('second', $configA->numerical->{1});

        // config->numerical->{2}-> ...
        self::assertInstanceOf('\Laminas\Config\Config', $configA->numerical->{2});
        self::assertEquals('third', $configA->numerical->{2}->{0});
        self::assertEquals(null, $configA->numerical->{2}->{1});

        // config->numerical->  ...
        self::assertEquals('fourth', $configA->numerical->{3});
        self::assertEquals('fifth', $configA->numerical->{4});

        // config->numerical->{5}
        self::assertInstanceOf('\Laminas\Config\Config', $configA->numerical->{5});
        self::assertEquals('sixth', $configA->numerical->{5}->{0});
        self::assertEquals(null, $configA->numerical->{5}->{1});

        // config->misaligned
        self::assertInstanceOf('\Laminas\Config\Config', $configA->misaligned);
        self::assertEquals('foo', $configA->misaligned->{2});
        self::assertEquals('bar', $configA->misaligned->{3});
        self::assertEquals('baz', $configA->misaligned->{4});
        self::assertEquals(null, $configA->misaligned->{0});

        // config->mixed
        self::assertInstanceOf('\Laminas\Config\Config', $configA->mixed);
        self::assertEquals('bar', $configA->mixed->foo);
        self::assertFalse($configA->mixed->{0});
        self::assertNull($configA->mixed->{1});

        // config->replaceAssoc
        self::assertNull($configA->replaceAssoc);

        // config->replaceNumerical
        self::assertTrue($configA->replaceNumerical);
    }

    public function testArrayAccess()
    {
        $config = new Config($this->all, true);

        self::assertEquals('thisname', $config['name']);
        $config['name'] = 'anothername';
        self::assertEquals('anothername', $config['name']);
        self::assertEquals('multi', $config['one']['two']['three']);

        self::assertTrue(isset($config['hostname']));
        self::assertTrue(isset($config['db']['name']));

        unset($config['hostname']);
        unset($config['db']['name']);

        self::assertFalse(isset($config['hostname']));
        self::assertFalse(isset($config['db']['name']));
    }

    public function testArrayAccessModification()
    {
        $config = new Config($this->numericData, true);

        // Define some values we'll be using
        $poem = [
            'poem' => [
                'line 1' => 'Roses are red, bacon is also red,',
                'line 2' => 'Poems are hard,',
                'line 3' => 'Bacon.',
            ],
        ];

        $bacon = 'Bacon';

        // Add a value
        $config[] = $bacon;

        // Check if bacon now has a key that equals to 2
        self::assertEquals($bacon, $config[2]);

        // Now let's try setting an array with no key supplied
        $config[] = $poem;

        // This should now be set with key 3
        self::assertEquals($poem, $config[3]->toArray());
    }

    /**
     * Ensures that toArray() supports objects of types other than Laminas_Config
     *
     * @return void
     */
    public function testToArraySupportsObjects()
    {
        $configData = [
            'a' => new \stdClass(),
            'b' => [
                'c' => new \stdClass(),
                'd' => new \stdClass()
                ]
            ];
        $config = new Config($configData);
        self::assertEquals($config->toArray(), $configData);
        self::assertInstanceOf('stdClass', $config->a);
        self::assertInstanceOf('stdClass', $config->b->c);
        self::assertInstanceOf('stdClass', $config->b->d);
    }

    /**
     * ensure that modification is not allowed after calling setReadOnly()
     *
     */
    public function testSetReadOnly()
    {
        $configData = [
            'a' => 'a'
            ];
        $config = new Config($configData, true);
        $config->b = 'b';

        $config->setReadOnly();
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Config is read only');
        $config->c = 'c';
    }

    public function testLaminas408countNotDecreasingOnUnset()
    {
        $configData = [
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            ];
        $config = new Config($configData, true);
        self::assertCount(3, $config);
        unset($config->b);
        self::assertCount(2, $config);
    }

    public function testLaminas4107ensureCloneDoesNotKeepNestedReferences()
    {
        $parent = new Config(['key' => ['nested' => 'parent']], true);
        $newConfig = clone $parent;
        $newConfig->merge(new Config(['key' => ['nested' => 'override']], true));

        self::assertEquals('override', $newConfig->key->nested, '$newConfig is not overridden');
        self::assertEquals('parent', $parent->key->nested, '$parent has been overridden');
    }

    /**
     * @group Laminas-3575
     *
     */
    public function testMergeHonoursAllowModificationsFlagAtAllLevels()
    {
        $config = new Config(['key' => ['nested' => 'yes'], 'key2' => 'yes'], false);
        $config2 = new Config([], true);

        $config2->merge($config);

        $config2->key2 = 'no';

        self::assertEquals('no', $config2->key2);

        $config2->key->nested = 'no';

        self::assertEquals('no', $config2->key->nested);
    }

    /**
     * @group Laminas-5771a
     *
     */
    public function testUnsettingFirstElementDuringForeachDoesNotSkipAnElement()
    {
        $config = new Config([
            'first'  => [1],
            'second' => [2],
            'third'  => [3]
        ], true);

        $keyList = [];
        foreach ($config as $key => $value) {
            $keyList[] = $key;
            if ($key == 'first') {
                unset($config->$key); // uses magic Laminas\Config\Config::__unset() method
            }
        }

        self::assertEquals('first', $keyList[0]);
        self::assertEquals('second', $keyList[1]);
        self::assertEquals('third', $keyList[2]);
    }

    /**
     * @group Laminas-5771
     *
     */
    public function testUnsettingAMiddleElementDuringForeachDoesNotSkipAnElement()
    {
        $config = new Config([
            'first'  => [1],
            'second' => [2],
            'third'  => [3]
        ], true);

        $keyList = [];
        foreach ($config as $key => $value) {
            $keyList[] = $key;
            if ($key == 'second') {
                unset($config->$key); // uses magic Laminas\Config\Config::__unset() method
            }
        }

        self::assertEquals('first', $keyList[0]);
        self::assertEquals('second', $keyList[1]);
        self::assertEquals('third', $keyList[2]);
    }

    /**
     * @group Laminas-5771
     *
     */
    public function testUnsettingLastElementDuringForeachDoesNotSkipAnElement()
    {
        $config = new Config([
            'first'  => [1],
            'second' => [2],
            'third'  => [3]
        ], true);

        $keyList = [];
        foreach ($config as $key => $value) {
            $keyList[] = $key;
            if ($key == 'third') {
                unset($config->$key); // uses magic Laminas\Config\Config::__unset() method
            }
        }

        self::assertEquals('first', $keyList[0]);
        self::assertEquals('second', $keyList[1]);
        self::assertEquals('third', $keyList[2]);
    }

    /**
     * @group Laminas-4728
     *
     */
    public function testSetReadOnlyAppliesToChildren()
    {
        $config = new Config($this->all, true);

        $config->setReadOnly();
        self::assertTrue($config->isReadOnly());
        self::assertTrue($config->one->isReadOnly(), 'First level children are writable');
        self::assertTrue($config->one->two->isReadOnly(), 'Second level children are writable');
    }

    public function testLaminas6995toArrayDoesNotDisturbInternalIterator()
    {
        $config = new Config(range(1, 10));
        $config->rewind();
        self::assertEquals(1, $config->current());

        $config->toArray();
        self::assertEquals(1, $config->current());
    }

    /**
     * @depends testMerge
     * @link https://getlaminas.org/issues/browse/Laminas-186
     */
    public function testLaminas186mergeReplacingUnnamedConfigSettings()
    {
        $arrayA = [
            'flag' => true,
            'text' => 'foo',
            'list' => [ 'a', 'b', 'c' ],
            'aSpecific' => 12
        ];

        $arrayB = [
            'flag' => false,
            'text' => 'bar',
            'list' => [ 'd', 'e' ],
            'bSpecific' => 100
        ];

        $mergeResult = [
            'flag' => false,
            'text' => 'bar',
            'list' => [ 'a', 'b', 'c', 'd', 'e' ],
            'aSpecific' => 12,
            'bSpecific' => 100
        ];

        $configA = new Config($arrayA);
        $configB = new Config($arrayB);

        $configA->merge($configB); // merge B onto A
        self::assertEquals($mergeResult, $configA->toArray());
    }

    public function testExtendedConfigHasSubnodesTheSameType()
    {
        $config = new TestAssets\ExtendedConfig([
            'node' => [
                'key' => 'value',
            ],
        ]);

        self::assertInstanceOf(TestAssets\ExtendedConfig::class, $config->node);
    }
}
