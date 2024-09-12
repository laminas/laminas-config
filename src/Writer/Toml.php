<?php

namespace Laminas\Config\Writer;

use Laminas\Config\Exception;

use function function_exists;

class Toml extends AbstractWriter
{
    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     * @throws Exception\RuntimeException If encoding errors occur.
     */
    public function processConfig(array $config)
    {
        if (! function_exists('toml_encode')) {
            throw new Exception\RuntimeException("You didn't install TOML encoder");
        }

        try {
            return toml_encode($config);
        } catch (\Exception) {
            throw new Exception\RuntimeException("Error generating TOML data");
        }
    }
}
