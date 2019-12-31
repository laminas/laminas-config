<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Writer\TestAssets;

use Laminas\Config\Exception;
use Laminas\Config\Writer\AbstractWriter;

class DummyWriter extends AbstractWriter
{
    public function processConfig(array $config)
    {
        return serialize($config);
    }
}
