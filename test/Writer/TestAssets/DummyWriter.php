<?php

declare(strict_types=1);

namespace LaminasTest\Config\Writer\TestAssets;

use Laminas\Config\Writer\AbstractWriter;

use function serialize;

class DummyWriter extends AbstractWriter
{
    /**
     * @param array $config
     * @return string
     */
    public function processConfig(array $config)
    {
        return serialize($config);
    }
}
