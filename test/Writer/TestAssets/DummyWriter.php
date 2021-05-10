<?php

namespace LaminasTest\Config\Writer\TestAssets;

use Laminas\Config\Writer\AbstractWriter;

use function serialize;

class DummyWriter extends AbstractWriter
{
    public function processConfig(array $config)
    {
        return serialize($config);
    }
}
