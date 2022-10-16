<?php

declare(strict_types=1);

namespace LaminasTest\Config;

use Laminas\Config\Exception\InvalidArgumentException;
use Laminas\Config\Reader\ReaderInterface;
use Laminas\Config\ReaderPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;

class ReaderPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @return ReaderPluginManager
     */
    protected function getPluginManager()
    {
        return new ReaderPluginManager(new ServiceManager());
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
        return ReaderInterface::class;
    }
}
