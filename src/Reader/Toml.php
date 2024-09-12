<?php

namespace Laminas\Config\Reader;

use Laminas\Config\Exception;

use function array_replace_recursive;
use function dirname;
use function file_get_contents;
use function function_exists;
use function is_array;
use function is_file;
use function is_readable;
use function sprintf;
use function trim;

/**
 * TOML config reader.
 */
class Toml implements ReaderInterface
{
    /**
     * Directory of the TOML file
     *
     * @var string
     */
    protected $directory;

    /**
     * fromFile(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromFile()
     *
     * @param  string $filename
     * @return array
     * @throws Exception\RuntimeException
     */
    public function fromFile($filename)
    {
        if (! is_file($filename) || ! is_readable($filename)) {
            throw new Exception\RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }

        $this->directory = dirname($filename);

        $config = $this->decode(file_get_contents($filename));

        return $this->process($config);
    }

    /**
     * fromString(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromString()
     *
     * @param  string $string
     * @return array|bool
     * @throws Exception\RuntimeException
     */
    public function fromString($string)
    {
        if (empty($string)) {
            return [];
        }

        $this->directory = null;

        $config = $this->decode($string);

        return $this->process($config);
    }

    /**
     * Process the array for @include
     *
     * @param  array $data
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function process(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->process($value);
            }
            if (trim($key) === '@include') {
                if ($this->directory === null) {
                    throw new Exception\RuntimeException('Cannot process @include statement for a TOML string');
                }
                $reader = clone $this;
                unset($data[$key]);
                $data = array_replace_recursive($data, $reader->fromFile($this->directory . '/' . $value));
            }
        }
        return $data;
    }

    /**
     * Decode TOML configuration.
     *
     * Determines if devium/toml is present, and, if so, uses that to decode the
     * configuration. Otherwise, if that is missing, raises an exception
     * indicating inability to decode.
     *
     * @param string $data
     * @return array
     * @throws Exception\RuntimeException For any decoding errors.
     */
    private function decode($data)
    {
        if (! function_exists('toml_decode')) {
            throw new Exception\RuntimeException("You didn't install TOML decoder");
        }

        try {
            return toml_decode($data, true, true);
        } catch (\Exception) {
            throw new Exception\RuntimeException('Invalid TOML configuration; did not return an array');
        }
    }
}
