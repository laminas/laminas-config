<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Writer;

/**
 * @category   Laminas
 * @package    Laminas_Config
 * @subpackage Writer
 */
class PhpArray extends AbstractWriter
{
    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     */
    public function processConfig(array $config)
    {
        $arrayString = "<?php\n"
                     . "return " . var_export($config, true) . ";\n";

        return $arrayString;
    }
}
