<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Config;

use Laminas\Config\AbstractConfigFactory;
use Laminas\ServiceManager;
use Laminas\ServiceManager\Config as SMConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractConfigFactoryTest
 */
class AbstractConfigFactoryTest extends TestCase
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->config = [
            'MyModule' => [
                'foo' => [
                    'bar'
                ]
            ],
            'phly-blog' => [
                'foo' => [
                    'bar'
                ]
            ]
        ];

        $this->serviceManager = new ServiceManager\ServiceManager;
        $smConfig = new SMConfig([
            'abstract_factories' => [
                AbstractConfigFactory::class,
            ],
            'services' => [
                'Config' => $this->config,
            ],
        ]);
        $smConfig->configureServiceManager($this->serviceManager);
    }

    /**
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testInvalidPattern()
    {
        $factory = new AbstractConfigFactory();
        $factory->addPattern(new \stdClass());
    }

    /**
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testInvalidPatternIterator()
    {
        $factory = new AbstractConfigFactory();
        $factory->addPatterns('invalid');
    }

    /**
     * @return void
     */
    public function testPatterns()
    {
        $factory = new AbstractConfigFactory();
        $defaults = $factory->getPatterns();

        // Tests that the accessor returns an array
        $this->assertInternalType('array', $defaults);
        $this->assertGreaterThan(0, count($defaults));

        // Tests adding a single pattern
        $this->assertSame($factory, $factory->addPattern('#foobarone#i'));
        $this->assertCount(count($defaults) + 1, $factory->getPatterns());

        // Tests adding multiple patterns at once
        $patterns = $factory->getPatterns();
        $this->assertSame($factory, $factory->addPatterns(['#foobartwo#i', '#foobarthree#i']));
        $this->assertCount(count($patterns) + 2, $factory->getPatterns());

        // Tests whether the latest added pattern is the first in stack
        $patterns = $factory->getPatterns();
        $this->assertSame('#foobarthree#i', $patterns[0]);
    }

    /**
     * @return void
     */
    public function testCanCreateService()
    {
        $factory = new AbstractConfigFactory();
        $serviceLocator = $this->serviceManager;

        $this->assertFalse($factory->canCreate($serviceLocator, 'MyModule\Fail'));
        $this->assertTrue($factory->canCreate($serviceLocator, 'MyModule\Config'));
    }

    /**
     * @depends testCanCreateService
     * @return void
     */
    public function testCreateService()
    {
        $serviceLocator = $this->serviceManager;
        $this->assertInternalType('array', $serviceLocator->get('MyModule\Config'));
        $this->assertInternalType('array', $serviceLocator->get('MyModule_Config'));
        $this->assertInternalType('array', $serviceLocator->get('Config.MyModule'));
        $this->assertInternalType('array', $serviceLocator->get('phly-blog.config'));
        $this->assertInternalType('array', $serviceLocator->get('phly-blog-config'));
        $this->assertInternalType('array', $serviceLocator->get('config-phly-blog'));
    }

    /**
     * @depends testCreateService
     *
     * @group 7142
     * @group 7144
     */
    public function testCreateServiceWithRequestedConfigKey()
    {
        $serviceLocator = $this->serviceManager;
        $this->assertSame($this->config['MyModule'], $serviceLocator->get('MyModule\Config'));
        $this->assertSame($this->config['phly-blog'], $serviceLocator->get('phly-blog-config'));
    }
}
