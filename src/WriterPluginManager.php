<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config;

use Laminas\ServiceManager\AbstractPluginManager;

class WriterPluginManager extends AbstractPluginManager
{
    protected $invokableClasses = array(
        'ini'  => 'Laminas\Config\Writer\Ini',
        'json' => 'Laminas\Config\Writer\Json',
        'php'  => 'Laminas\Config\Writer\PhpArray',
        'yaml' => 'Laminas\Config\Writer\Yaml',
        'xml'  => 'Laminas\Config\Writer\Xml',
    );

    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Writer\AbstractWriter) {
            return;
        }

        $type = is_object($plugin) ? get_class($plugin) : gettype($plugin);

        throw new Exception\InvalidArgumentException(
            "Plugin of type {$type} is invalid. Plugin must extend ".  __NAMESPACE__ . '\Writer\AbstractWriter'
        );
    }
}
