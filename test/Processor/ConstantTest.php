<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Processor;

use Laminas\Config\Config;
use Laminas\Config\Processor\Constant as ConstantProcessor;
use PHPUnit\Framework\TestCase;

class ConstantTest extends TestCase
{
    const CONFIG_TEST = 'config';

    public function constantProvider()
    {
        if (! defined('LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST')) {
            define('LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST', 'test-key');
        }

        // @codingStandardsIgnoreStart
        //                                    constantString,                        constantValue
        return [
            'constant'                    => ['LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST', LAMINAS_CONFIG_PROCESSOR_CONSTANT_TEST],
            'class-constant'              => [__CLASS__ . '::CONFIG_TEST',           self::CONFIG_TEST],
            'class-pseudo-constant'       => [__CLASS__ . '::class',                 self::class],
            'class-pseudo-constant-upper' => [__CLASS__ . '::CLASS',                 self::class],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider constantProvider
     *
     * @param string $constantString
     * @param string $constantValue
     */
    public function testCanResolveConstantValues($constantString, $constantValue)
    {
        $config = new Config(['test' => $constantString], true);

        $processor = new ConstantProcessor();
        $processor->process($config);

        $this->assertEquals($constantValue, $config->get('test'));
    }

    /**
     * @dataProvider constantProvider
     *
     * @param string $constantString
     * @param string $constantValue
     */
    public function testWillNotProcessConstantValuesInKeysByDefault($constantString, $constantValue)
    {
        $config = new Config([$constantString => 'value'], true);
        $processor = new ConstantProcessor();
        $processor->process($config);

        $this->assertNotEquals('value', $config->get($constantValue));
        $this->assertEquals('value', $config->get($constantString));
    }

    /**
     * @dataProvider constantProvider
     *
     * @param string $constantString
     * @param string $constantValue
     */
    public function testCanProcessConstantValuesInKeys($constantString, $constantValue)
    {
        $config = new Config([$constantString => 'value'], true);
        $processor = new ConstantProcessor();
        $processor->enableKeyProcessing();
        $processor->process($config);

        $this->assertEquals('value', $config->get($constantValue));
        $this->assertNotEquals('value', $config->get($constantString));
    }

    public function testKeyProcessingDisabledByDefault()
    {
        $processor = new ConstantProcessor();
        $this->assertAttributeSame(false, 'processKeys', $processor);
    }

    public function testCanEnableKeyProcessingViaConstructorArgument()
    {
        $processor = new ConstantProcessor(true, '', '', true);
        $this->assertAttributeSame(true, 'processKeys', $processor);
    }
}
