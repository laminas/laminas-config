<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config;

use Laminas\Stdlib\ArrayUtils;

/**
 * @category  Laminas
 * @package   Laminas_Config
 */
class Factory
{
    /**
     * Plugin manager for loading readers
     *
     * @var null|ReaderPluginManager
     */
    public static $readers = null;

    /**
     * Registered config file extensions.
     * key is extension, value is reader instance or plugin name
     *
     * @var array
     */
    protected static $extensions = array(
        'ini'  => 'ini',
        'json' => 'json',
        'xml'  => 'xml',
        'yaml' => 'yaml',
    );


    /**
     * Read a config from a file.
     *
     * @param  string  $filename
     * @param  boolean $returnConfigObject
     * @return array|Config
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public static function fromFile($filename, $returnConfigObject = false)
    {
        $pathinfo = pathinfo($filename);

        if (!isset($pathinfo['extension'])) {
            throw new Exception\RuntimeException(sprintf(
                'Filename "%s" is missing an extension and cannot be auto-detected',
                $filename
            ));
        }

        $extension = strtolower($pathinfo['extension']);

        if ($extension === 'php') {
            if (!is_file($filename) || !is_readable($filename)) {
                throw new Exception\RuntimeException(sprintf(
                    "File '%s' doesn't exist or not readable",
                    $filename
                ));
            }

            $config = include $filename;
        } elseif (isset(self::$extensions[$extension])) {
            $reader = self::$extensions[$extension];
            if (!$reader instanceof Reader\ReaderInterface) {
                $reader = self::getReaderPluginManager()->get($reader);
                self::$extensions[$extension] = $reader;
            }

            /** @var Reader\ReaderInterface $reader  */
            $config = $reader->fromFile($filename);
        } else {
            throw new Exception\RuntimeException(sprintf(
                'Unsupported config file extension: .%s',
                $pathinfo['extension']
            ));
        }

        return ($returnConfigObject) ? new Config($config) : $config;
    }

    /**
     * Read configuration from multiple files and merge them.
     *
     * @param  array   $files
     * @param  boolean $returnConfigObject
     * @return array|Config
     */
    public static function fromFiles(array $files, $returnConfigObject = false)
    {
        $config = array();

        foreach ($files as $file) {
            $config = ArrayUtils::merge($config, self::fromFile($file));
        }

        return ($returnConfigObject) ? new Config($config) : $config;
    }

    /**
     * Set reader plugin manager
     *
     * @param ReaderPluginManager $readers
     */
    public static function setReaderPluginManager(ReaderPluginManager $readers)
    {
        self::$readers = $readers;
    }

    /**
     * Get the reader plugin manager
     *
     * @return ReaderPluginManager
     */
    public static function getReaderPluginManager()
    {
        if (static::$readers === null) {
            static::$readers = new ReaderPluginManager();
        }
        return static::$readers;
    }

    /**
     * Set config reader for file extension
     *
     * @param  string $extension
     * @param  string|Reader\ReaderInterface $reader
     * @throws Exception\InvalidArgumentException
     */
    public static function registerReader($extension, $reader)
    {
        $extension = strtolower($extension);

        if (!is_string($reader) && !$reader instanceof Reader\ReaderInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Reader should be plugin name, class name or ' .
                'instance of %s\Reader\ReaderInterface; received "%s"',
                __NAMESPACE__,
                (is_object($reader) ? get_class($reader) : gettype($reader))
            ));
        }

        self::$extensions[$extension] = $reader;
    }
}
