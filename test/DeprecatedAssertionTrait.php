<?php

declare(strict_types=1);

namespace LaminasTest\Config;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function sprintf;
use function var_export;

trait DeprecatedAssertionTrait
{
    /** @param mixed $value */
    public static function assertAttributeSame($value, string $property, object $instance, string $message = ''): void
    {
        $r = new ReflectionProperty($instance, $property);
        $r->setAccessible(true);

        if ($message === '') {
            $message = sprintf(
                'Failed asserting property %s::$%s with value %s matches value %s',
                $instance::class,
                $property,
                var_export($value, true),
                var_export($r->getValue($instance), true)
            );
        }

        TestCase::assertSame($value, $r->getValue($instance), $message);
    }
}
