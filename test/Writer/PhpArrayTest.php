<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;
use LaminasTest\Config\Writer\TestAssets\PhpReader;

/**
 * @group      Laminas_Config
 */
class PhpArrayTest extends AbstractWriterTestCase
{
    protected $_tempName;

    public function setUp()
    {
        $this->writer = new PhpArray();
        $this->reader = new PhpReader();
    }

    /**
     * @group Laminas-8234
     */
    public function testRender()
    {
        $config = new Config(array('test' => 'foo', 'bar' => array(0 => 'baz', 1 => 'foo')));

        $configString = $this->writer->toString($config);

        // build string line by line as we are trailing-whitespace sensitive.
        $expected = "<?php\n";
        $expected .= "return array (\n";
        $expected .= "  'test' => 'foo',\n";
        $expected .= "  'bar' => \n";
        $expected .= "  array (\n";
        $expected .= "    0 => 'baz',\n";
        $expected .= "    1 => 'foo',\n";
        $expected .= "  ),\n";
        $expected .= ");\n";

        $this->assertEquals($expected, $configString);
    }
}
