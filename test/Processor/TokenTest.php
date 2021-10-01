<?php

namespace LaminasTest\Config\Processor;

use Laminas\Config\Processor\Token as TokenProcessor;
use LaminasTest\Config\DeprecatedAssertionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Majority of tests are in LaminasTest\Config\ProcessorTest; this class contains
 * tests covering new functionality and/or specific bugs.
 */
class TokenTest extends TestCase
{
    use DeprecatedAssertionTrait;

    public function testKeyProcessingDisabledByDefault()
    {
        $processor = new TokenProcessor();
        self::assertAttributeSame(false, 'processKeys', $processor);
    }

    public function testCanEnableKeyProcessingViaConstructorArgument()
    {
        $processor = new TokenProcessor([], '', '', true);
        self::assertAttributeSame(true, 'processKeys', $processor);
    }
}
