<?php

declare(strict_types=1);

namespace LaminasTest\Config;

use Laminas\Config\Exception\InvalidArgumentException;
use Laminas\Config\Writer\AbstractWriter;
use Laminas\Config\WriterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;

class WriterPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @return WriterPluginManager
     */
    protected function getPluginManager()
    {
        return new WriterPluginManager(new ServiceManager());
    }

    /**
     * @return string
     */
    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    /**
     * @return string
     */
    protected function getInstanceOf()
    {
        return AbstractWriter::class;
    }
}
