<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config;

use Laminas\ServiceManager\AbstractPluginManager;

class ReaderPluginManager extends AbstractPluginManager
{
    /**
     * Default set of readers
     *
     * @var array
     */
    protected $invokableClasses = array(
        'ini'             => 'Laminas\Config\Reader\Ini',
        'json'            => 'Laminas\Config\Reader\Json',
        'xml'             => 'Laminas\Config\Reader\Xml',
        'yaml'            => 'Laminas\Config\Reader\Yaml',
        'javaproperties'  => 'Laminas\Config\Reader\JavaProperties',
    );

    /**
     * Validate the plugin
     * Checks that the reader loaded is an instance of Reader\ReaderInterface.
     *
     * @param  Reader\ReaderInterface $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Reader\ReaderInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Reader\ReaderInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
