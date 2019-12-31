<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config\Writer;

use Laminas\Config\Config;
use Laminas\Config\Reader\Xml as XmlReader;
use Laminas\Config\Writer\Xml as XmlWriter;

/**
 * @category   Laminas
 * @package    Laminas_Config
 * @subpackage UnitTests
 * @group      Laminas_Config
 */
class XmlTest extends AbstractWriterTestCase
{
    protected $_tempName;

    public function setUp()
    {
        $this->writer = new XmlWriter();
        $this->reader = new XmlReader();
    }

    public function testToString()
    {
        $config = new Config(array('test' => 'foo', 'bar' => array(0 => 'baz', 1 => 'foo')));

        $configString = $this->writer->toString($config);

        $expected = <<<ECS
<?xml version="1.0" encoding="UTF-8"?>
<laminas-config>
    <test>foo</test>
    <bar>baz</bar>
    <bar>foo</bar>
</laminas-config>

ECS;

        $this->assertEquals($expected, $configString);
    }

    public function testSectionsToString()
    {
        $config = new Config(array(), true);
        $config->production = array();

        $config->production->webhost = 'www.example.com';
        $config->production->database = array();
        $config->production->database->params = array();
        $config->production->database->params->host = 'localhost';
        $config->production->database->params->username = 'production';
        $config->production->database->params->password = 'secret';
        $config->production->database->params->dbname = 'dbproduction';

        $configString = $this->writer->toString($config);

        $expected = <<<ECS
<?xml version="1.0" encoding="UTF-8"?>
<laminas-config>
    <production>
        <webhost>www.example.com</webhost>
        <database>
            <params>
                <host>localhost</host>
                <username>production</username>
                <password>secret</password>
                <dbname>dbproduction</dbname>
            </params>
        </database>
    </production>
</laminas-config>

ECS;

        $this->assertEquals($expected, $configString);
    }
}
