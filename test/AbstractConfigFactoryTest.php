<?php

namespace LaminasTest\Config;

use InvalidArgumentException;
use Laminas\Config\AbstractConfigFactory;
use Laminas\ServiceManager;
use Laminas\ServiceManager\Config as SMConfig;
use PHPUnit\Framework\TestCase;

use function count;

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
    protected function setUp() : void
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
                'config' => $this->config,
            ],
        ]);
        $smConfig->configureServiceManager($this->serviceManager);
    }

    public function testInvalidPattern()
    {
        $factory = new AbstractConfigFactory();

        $this->expectException(InvalidArgumentException::class);
        $factory->addPattern(new \stdClass());
    }

    public function testInvalidPatternIterator()
    {
        $factory = new AbstractConfigFactory();

        $this->expectException(InvalidArgumentException::class);
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
        self::assertIsArray($defaults);
        self::assertGreaterThan(0, count($defaults));

        // Tests adding a single pattern
        self::assertSame($factory, $factory->addPattern('#foobarone#i'));
        self::assertCount(count($defaults) + 1, $factory->getPatterns());

        // Tests adding multiple patterns at once
        $patterns = $factory->getPatterns();
        self::assertSame($factory, $factory->addPatterns(['#foobartwo#i', '#foobarthree#i']));
        self::assertCount(count($patterns) + 2, $factory->getPatterns());

        // Tests whether the latest added pattern is the first in stack
        $patterns = $factory->getPatterns();
        self::assertSame('#foobarthree#i', $patterns[0]);
    }

    /**
     * @return void
     */
    public function testCanCreateService()
    {
        $factory = new AbstractConfigFactory();
        $serviceLocator = $this->serviceManager;

        self::assertFalse($factory->canCreate($serviceLocator, 'MyModule\Fail'));
        self::assertTrue($factory->canCreate($serviceLocator, 'MyModule\Config'));
    }

    /**
     * @depends testCanCreateService
     * @return void
     */
    public function testCreateService()
    {
        $serviceLocator = $this->serviceManager;
        self::assertIsArray($serviceLocator->get('MyModule\Config'));
        self::assertIsArray($serviceLocator->get('MyModule_Config'));
        self::assertIsArray($serviceLocator->get('Config.MyModule'));
        self::assertIsArray($serviceLocator->get('phly-blog.config'));
        self::assertIsArray($serviceLocator->get('phly-blog-config'));
        self::assertIsArray($serviceLocator->get('config-phly-blog'));
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
        self::assertSame($this->config['MyModule'], $serviceLocator->get('MyModule\Config'));
        self::assertSame($this->config['phly-blog'], $serviceLocator->get('phly-blog-config'));
    }
}
