<?php
namespace Laminas\Config;

use Laminas\ServiceManager\AbstractPluginManager;

class WriterPluginManager extends AbstractPluginManager
{
    protected $invokableClasses = array(
        'php'  => 'Laminas\Config\Writer\PhpArray',
        'ini'  => 'Laminas\Config\Writer\Ini',
        'json' => 'Laminas\Config\Writer\Json',
        'yaml' => 'Laminas\Config\Writer\Yaml',
        'xml'  => 'Laminas\Config\Writer\Xml',
    );

    public function validatePlugin($plugin)
    {
        if ($plugin instanceOf Writer\AbstractWriter) {
            return;
        }

        $type = is_object($plugin) ? get_class($plugin) : gettype($plugin);

        throw new Exception\InvalidArgumentException(
            "Plugin of type {$type} is invalid. Plugin must extend ".
                __NAMESPACE__.'\Writer\AbstractWriter'
        );
    }
}
