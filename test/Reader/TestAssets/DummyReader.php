<?php

declare(strict_types=1);

namespace LaminasTest\Config\Reader\TestAssets;

use Laminas\Config\Exception;
use Laminas\Config\Reader\ReaderInterface;

use function file_get_contents;
use function is_readable;
use function unserialize;

class DummyReader implements ReaderInterface
{
    /**
     * @param  string $filename
     * @return array
     */
    public function fromFile($filename)
    {
        if (! is_readable($filename)) {
            throw new Exception\RuntimeException("File '{$filename}' doesn't exist or not readable");
        }

        return unserialize(file_get_contents($filename));
    }

    /**
     * @param  string $string
     * @return array|bool
     */
    public function fromString($string)
    {
        if (empty($string)) {
            return [];
        }

        return unserialize($string);
    }
}
