<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Processor;

use Laminas\Config\Processor\Token as TokenProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Majority of tests are in LaminasTest\Config\ProcessorTest; this class contains
 * tests covering new functionality and/or specific bugs.
 */
class TokenTest extends TestCase
{
    public function testKeyProcessingDisabledByDefault()
    {
        $processor = new TokenProcessor();
        $this->assertAttributeSame(false, 'processKeys', $processor);
    }

    public function testCanEnableKeyProcessingViaConstructorArgument()
    {
        $processor = new TokenProcessor([], '', '', true);
        $this->assertAttributeSame(true, 'processKeys', $processor);
    }
}
