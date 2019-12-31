<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Reader\TestAssets;

use Laminas\Config\Exception;
use Laminas\Config\Reader\ReaderInterface;

class DummyReader implements ReaderInterface
{
    public function fromFile($filename)
    {
        if (!is_readable($filename)) {
            throw new Exception\RuntimeException("File '{$filename}' doesn't exist or not readable");
        }

        return unserialize(file_get_contents($filename));
    }

    public function fromString($string)
    {
        if (empty($string)) {
            return array();
        }

        return unserialize($string);
    }
}
