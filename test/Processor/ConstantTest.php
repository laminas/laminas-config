<?php

declare(strict_types=1);

namespace LaminasTest\Config\Processor;

use Laminas\Config\Config;
use Laminas\Config\Processor\Constant as ConstantProcessor;
use LaminasTest\Config\DeprecatedAssertionTrait;
use PHPUnit\Framework\TestCase;

use function define;
use function defined;

class ConstantTest extends TestCase
{
    use DeprecatedAssertionTrait;

    public const CONFIG_TEST = 'config';

    /**
     * @return array
     */
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
     * @param string $constantString
     * @param string $constantValue
     */
    public function testCanResolveConstantValues($constantString, $constantValue)
    {
        $config = new Config(['test' => $constantString], true);

        $processor = new ConstantProcessor();
        $processor->process($config);

        self::assertEquals($constantValue, $config->get('test'));
    }

    /**
     * @dataProvider constantProvider
     * @param string $constantString
     * @param string $constantValue
     */
    public function testWillNotProcessConstantValuesInKeysByDefault($constantString, $constantValue)
    {
        $config    = new Config([$constantString => 'value'], true);
        $processor = new ConstantProcessor();
        $processor->process($config);

        self::assertNotEquals('value', $config->get($constantValue));
        self::assertEquals('value', $config->get($constantString));
    }

    /**
     * @dataProvider constantProvider
     * @param string $constantString
     * @param string $constantValue
     */
    public function testCanProcessConstantValuesInKeys($constantString, $constantValue)
    {
        $config    = new Config([$constantString => 'value'], true);
        $processor = new ConstantProcessor();
        $processor->enableKeyProcessing();
        $processor->process($config);

        self::assertEquals('value', $config->get($constantValue));
        self::assertNotEquals('value', $config->get($constantString));
    }

    public function testKeyProcessingDisabledByDefault()
    {
        $processor = new ConstantProcessor();
        self::assertAttributeSame(false, 'processKeys', $processor);
    }

    public function testCanEnableKeyProcessingViaConstructorArgument()
    {
        $processor = new ConstantProcessor(true, '', '', true);
        self::assertAttributeSame(true, 'processKeys', $processor);
    }
}
