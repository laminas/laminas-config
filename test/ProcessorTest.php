<?php

namespace LaminasTest\Config;

use Laminas\Config\Config;
use Laminas\Config\Exception;
use Laminas\Config\Processor\Constant as ConstantProcessor;
use Laminas\Config\Processor\Filter as FilterProcessor;
use Laminas\Config\Processor\Queue;
use Laminas\Config\Processor\Token as TokenProcessor;
use Laminas\Config\Processor\Translator as TranslatorProcessor;
use Laminas\Filter\PregReplace;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringToUpper;
use Laminas\I18n\Exception as I18nException;
use Laminas\I18n\Translator\Translator;
use PHPUnit\Framework\TestCase;

use function define;
use function extension_loaded;
use function gettype;
use function realpath;

use const PHP_VERSION;

/**
 * @group      Laminas_Config
 */
class ProcessorTest extends TestCase
{
    protected $nested;
    protected $tokenBare;
    protected $tokenPrefix;
    protected $tokenSuffix;
    protected $tokenSurround;
    protected $tokenSurroundMixed;
    protected $translatorData;
    protected $translatorFile;
    protected $userConstants;
    protected $phpConstants;
    protected $filter;

    protected function setUp() : void
    {
        // Arrays representing common config configurations
        $this->nested = [
            'a' => 1,
            'b' => 2,
            'c' => [
                'ca' => 3,
                'cb' => 4,
                'cc' => 5,
                'cd' => [
                    'cda' => 6,
                    'cdb' => 7
                ],
            ],
            'd' => [
                'da' => 8,
                'db' => 9
            ],
            'e' => 10
        ];

        $this->tokenBare = [
            'simple' => 'BARETOKEN',
            'inside' => 'some text with BARETOKEN inside',
            'nested' => [
                'simple' => 'BARETOKEN',
                'inside' => 'some text with BARETOKEN inside',
            ],
        ];

        $this->tokenPrefix = [
            'simple' => '::TOKEN',
            'inside' => ':: some text with ::TOKEN inside ::',
            'nested' => [
                'simple' => '::TOKEN',
                'inside' => ':: some text with ::TOKEN inside ::',
            ],
        ];

        $this->tokenSuffix = [
            'simple' => 'TOKEN::',
            'inside' => ':: some text with TOKEN:: inside ::',
            'nested' => [
                'simple' => 'TOKEN::',
                'inside' => ':: some text with TOKEN:: inside ::',
            ],
        ];

        $this->tokenSurround = [
            'simple' => '##TOKEN##',
            'inside' => '## some text with ##TOKEN## inside ##',
            'nested' => [
                'simple' => '##TOKEN##',
                'inside' => '## some text with ##TOKEN## inside ##',
            ],
        ];

        $this->tokenSurroundMixed = [
            'simple' => '##TOKEN##',
            'inside' => '## some text with ##TOKEN## inside ##',
            'nested' => [
                'simple' => '@@TOKEN@@',
                'inside' => '@@ some text with @@TOKEN@@ inside @@',
            ],
        ];

        $this->translatorData = [
            'pages' => [
                [
                    'id' => 'oneDog',
                    'label' => 'one dog',
                    'route' => 'app-one-dog'
                ],
                [
                    'id' => 'twoDogs',
                    'label' => 'two dogs',
                    'route' => 'app-two-dogs'
                ],
            ]
        ];

        $this->translatorFile = realpath(__DIR__ . '/_files/translations-de_DE.php');

        $this->filter = [
            'simple' => 'some MixedCase VALue',
            'nested' => [
                'simple' => 'OTHER mixed Case Value',
            ],
        ];

        $this->userConstants = [
            'simple' => 'SOME_USERLAND_CONSTANT',
            'inside' => 'some text with SOME_USERLAND_CONSTANT inside',
            'nested' => [
                'simple' => 'SOME_USERLAND_CONSTANT',
                'inside' => 'some text with SOME_USERLAND_CONSTANT inside',
            ],
        ];

        $this->phpConstants = [
            'phpVersion' => 'PHP_VERSION',
            'phpVersionInside' => 'Current PHP version is: PHP_VERSION',
            'nested' => [
                'phpVersion' => 'PHP_VERSION',
                'phpVersionInside' => 'Current PHP version is: PHP_VERSION',
            ],
        ];
    }

    public function testProcessorsQueue()
    {
        $processor1 = new TokenProcessor();
        $processor2 = new TokenProcessor();
        $queue = new Queue();
        $queue->insert($processor1);
        $queue->insert($processor2);

        self::assertInstanceOf('\Laminas\Config\Processor\Queue', $queue);
        self::assertEquals(2, $queue->count());
        self::assertTrue($queue->contains($processor1));
        self::assertTrue($queue->contains($processor2));
    }

    public function testBareTokenPost()
    {
        $config = new Config($this->tokenBare, true);
        $processor = new TokenProcessor();
        $processor->addToken('BARETOKEN', 'some replaced value');
        $processor->process($config);

        self::assertEquals(['BARETOKEN' => 'some replaced value'], $processor->getTokens());
        self::assertEquals('some replaced value', $config->simple);
        self::assertEquals('some text with some replaced value inside', $config->inside);
        self::assertEquals('some replaced value', $config->nested->simple);
        self::assertEquals('some text with some replaced value inside', $config->nested->inside);
    }

    public function testAddInvalidToken()
    {
        $processor = new TokenProcessor();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot use ' . gettype([]) . ' as token name.');
        $processor->addToken([], 'bar');
    }

    public function testSingleValueToken()
    {
        $processor = new TokenProcessor();
        $processor->addToken('BARETOKEN', 'test');
        $data = 'BARETOKEN';
        $out = $processor->processValue($data);
        self::assertEquals($out, 'test');
    }

    public function testTokenReadOnly()
    {
        $config = new Config($this->tokenBare, false);
        $processor = new TokenProcessor();
        $processor->addToken('BARETOKEN', 'some replaced value');

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot process config because it is read-only');
        $processor->process($config);
    }

    public function testTokenPrefix()
    {
        $config = new Config($this->tokenPrefix, true);
        $processor = new TokenProcessor(['TOKEN' => 'some replaced value'], '::');
        $processor->process($config);

        self::assertEquals('some replaced value', $config->simple);
        self::assertEquals(':: some text with some replaced value inside ::', $config->inside);
        self::assertEquals('some replaced value', $config->nested->simple);
        self::assertEquals(':: some text with some replaced value inside ::', $config->nested->inside);
    }

    public function testTokenSuffix()
    {
        $config = new Config($this->tokenSuffix, true);
        $processor = new TokenProcessor(['TOKEN' => 'some replaced value'], '', '::');
        $processor->process($config);

        self::assertEquals('some replaced value', $config->simple);
        self::assertEquals(':: some text with some replaced value inside ::', $config->inside);
        self::assertEquals('some replaced value', $config->nested->simple);
        self::assertEquals(':: some text with some replaced value inside ::', $config->nested->inside);
    }

    /**
     * @depends testTokenSuffix
     * @depends testTokenPrefix
     */
    public function testTokenSurround()
    {
        $config = new Config($this->tokenSurround, true);
        $processor = new TokenProcessor(['TOKEN' => 'some replaced value'], '##', '##');
        $processor->process($config);

        self::assertEquals('some replaced value', $config->simple);
        self::assertEquals('## some text with some replaced value inside ##', $config->inside);
        self::assertEquals('some replaced value', $config->nested->simple);
        self::assertEquals('## some text with some replaced value inside ##', $config->nested->inside);
    }

    /**
     * @depends testTokenSurround
     */
    public function testTokenChangeParams()
    {
        $config = new Config($this->tokenSurroundMixed, true);
        $processor = new TokenProcessor(['TOKEN' => 'some replaced value'], '##', '##');
        $processor->process($config);
        self::assertEquals('some replaced value', $config->simple);
        self::assertEquals('## some text with some replaced value inside ##', $config->inside);
        self::assertEquals('@@TOKEN@@', $config->nested->simple);
        self::assertEquals('@@ some text with @@TOKEN@@ inside @@', $config->nested->inside);

        /**
         * Now change prefix and suffix on the processor
         */
        $processor->setPrefix('@@');
        $processor->setSuffix('@@');

        /**
         * Parse the config again
         */
        $processor->process($config);

        self::assertEquals('some replaced value', $config->simple);
        self::assertEquals('## some text with some replaced value inside ##', $config->inside);
        self::assertEquals('some replaced value', $config->nested->simple);
        self::assertEquals('@@ some text with some replaced value inside @@', $config->nested->inside);
    }

    /**
     * @group Laminas-5772
     */
    public function testTokenChangeParamsRetainsType()
    {
        $config = new Config(
            [
                'trueBoolKey' => true,
                'falseBoolKey' => false,
                'intKey' => 123,
                'floatKey' => (float) 123.456,
                'doubleKey' => (double) 456.789,
            ],
            true
        );

        $processor = new TokenProcessor();

        $processor->process($config);

        self::assertTrue($config['trueBoolKey']);
        self::assertFalse($config['falseBoolKey']);
        self::assertSame(123, $config['intKey']);
        self::assertSame((float) 123.456, $config['floatKey']);
        self::assertSame((double) 456.789, $config['doubleKey']);
    }

    /**
     * @group Laminas-5772
     */
    public function testTokenChangeParamsReplacesInNumerics()
    {
        $config = new Config(
            [
                'foo' => 'bar1',
                'trueBoolKey' => true,
                'falseBoolKey' => false,
                'intKey' => 123,
                'floatKey' => (float) 123.456,
                'doubleKey' => (double) 456.789,
            ],
            true
        );

        $processor = new TokenProcessor(['1' => 'R', '9' => 'R']);

        $processor->process($config);

        self::assertSame('R', $config['trueBoolKey']);
        self::assertSame('barR', $config['foo']);
        self::assertFalse($config['falseBoolKey']);
        self::assertSame('R23', $config['intKey']);
        self::assertSame('R23.456', $config['floatKey']);
        self::assertSame('456.78R', $config['doubleKey']);
    }

    /**
     * @group Laminas-5772
     */
    public function testIgnoresEmptyStringReplacement()
    {
        $config    = new Config(['foo' => 'bar'], true);
        $processor = new TokenProcessor(['' => 'invalid']);

        $processor->process($config);

        self::assertSame('bar', $config['foo']);
    }

    /**
     * @depends testTokenSurround
     */
    public function testUserConstants()
    {
        define('SOME_USERLAND_CONSTANT', 'some constant value');

        $config = new Config($this->userConstants, true);
        $processor = new ConstantProcessor(false);
        $processor->process($config);

        $tokens = $processor->getTokens();
        self::assertIsArray($tokens);
        self::assertArrayHasKey('SOME_USERLAND_CONSTANT', $tokens);
        self::assertFalse($processor->getUserOnly());

        self::assertEquals('some constant value', $config->simple);
        self::assertEquals('some text with some constant value inside', $config->inside);
        self::assertEquals('some constant value', $config->nested->simple);
        self::assertEquals('some text with some constant value inside', $config->nested->inside);
    }

    /**
     * @depends testUserConstants
     */
    public function testUserOnlyConstants()
    {
        $config = new Config($this->userConstants, true);
        $processor = new ConstantProcessor();
        $processor->process($config);

        $tokens = $processor->getTokens();

        self::assertIsArray($tokens);
        self::assertArrayHasKey('SOME_USERLAND_CONSTANT', $tokens);
        self::assertTrue($processor->getUserOnly());

        self::assertEquals('some constant value', $config->simple);
        self::assertEquals('some text with some constant value inside', $config->inside);
        self::assertEquals('some constant value', $config->nested->simple);
        self::assertEquals('some text with some constant value inside', $config->nested->inside);
    }

    /**
     * @depends testTokenSurround
     */
    public function testPHPConstants()
    {
        $config = new Config($this->phpConstants, true);
        $processor = new ConstantProcessor(false);
        $processor->process($config);

        self::assertEquals(PHP_VERSION, $config->phpVersion);
        self::assertEquals('Current PHP version is: ' . PHP_VERSION, $config->phpVersionInside);
        self::assertEquals(PHP_VERSION, $config->nested->phpVersion);
        self::assertEquals('Current PHP version is: ' . PHP_VERSION, $config->nested->phpVersionInside);
    }

    public function testTranslator()
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $config     = new Config($this->translatorData, true);
        $translator = new Translator();
        $translator->addTranslationFile('phparray', $this->translatorFile);
        $processor  = new TranslatorProcessor($translator);

        $processor->process($config);

        self::assertEquals('oneDog', $config->pages[0]->id);
        self::assertEquals('ein Hund', $config->pages[0]->label);
        self::assertEquals('twoDogs', $config->pages[1]->id);
        self::assertEquals('zwei Hunde', $config->pages[1]->label);
    }

    public function testTranslatorWithoutIntl()
    {
        if (extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl enabled');
        }

        $this->expectException(I18nException\ExtensionNotLoadedException::class);
        $this->expectExceptionMessage(
            'Laminas\I18n\Translator component requires the intl PHP extension'
        );

        $config     = new Config($this->translatorData, true);
        $translator = new Translator();
        $translator->addTranslationFile('phparray', $this->translatorFile);
        $processor  = new TranslatorProcessor($translator);

        $processor->process($config);
    }

    public function testTranslatorReadOnly()
    {
        $config     = new Config($this->translatorData, false);
        $translator = new Translator();
        $processor  = new TranslatorProcessor($translator);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot process config because it is read-only');
        $processor->process($config);
    }

    public function testTranslatorSingleValue()
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $translator = new Translator();
        $translator->addTranslationFile('phparray', $this->translatorFile);
        $processor  = new TranslatorProcessor($translator);

        self::assertEquals('ein Hund', $processor->processValue('one dog'));
    }

    public function testTranslatorSingleValueWithoutIntl()
    {
        if (extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl enabled');
        }

        $this->expectException(I18nException\ExtensionNotLoadedException::class);
        $this->expectExceptionMessage('Laminas\I18n\Translator component requires the intl PHP extension');

        $translator = new Translator();
        $translator->addTranslationFile('phparray', $this->translatorFile);
        $processor  = new TranslatorProcessor($translator);

        self::assertEquals('ein Hund', $processor->processValue('one dog'));
    }

    public function testFilter()
    {
        $config = new Config($this->filter, true);
        $filter = new StringToLower();
        $processor = new FilterProcessor($filter);

        self::assertInstanceOf('Laminas\Filter\StringToLower', $processor->getFilter());
        $processor->process($config);

        self::assertEquals('some mixedcase value', $config->simple);
        self::assertEquals('other mixed case value', $config->nested->simple);
    }

    public function testFilterReadOnly()
    {
        $config = new Config($this->filter, false);
        $filter = new StringToLower();
        $processor = new FilterProcessor($filter);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot process config because it is read-only');
        $processor->process($config);
    }

    public function testFilterValue()
    {
        $filter = new StringToLower();
        $processor = new FilterProcessor($filter);

        $value = 'TEST';
        self::assertEquals('test', $processor->processValue($value));
    }

    /**
     * @depends testFilter
     */
    public function testQueueFIFO()
    {
        $config = new Config($this->filter, true);
        $lower = new StringToLower();
        $upper = new StringToUpper();
        $lowerProcessor = new FilterProcessor($lower);
        $upperProcessor = new FilterProcessor($upper);

        /**
         * Default queue order (FIFO)
         */
        $queue = new Queue();
        $queue->insert($upperProcessor);
        $queue->insert($lowerProcessor);
        $queue->process($config);

        self::assertEquals('some mixedcase value', $config->simple);
        self::assertEquals('other mixed case value', $config->nested->simple);
    }

    public function testQueueReadOnly()
    {
        $config = new Config($this->filter, false);
        $lower = new StringToLower();
        $lowerProcessor = new FilterProcessor($lower);

        /**
         * Default queue order (FIFO)
         */
        $queue = new Queue();
        $queue->insert($lowerProcessor);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot process config because it is read-only');
        $queue->process($config);
    }

    public function testQueueSingleValue()
    {
        $lower = new StringToLower();
        $upper = new StringToUpper();
        $lowerProcessor = new FilterProcessor($lower);
        $upperProcessor = new FilterProcessor($upper);

        /**
         * Default queue order (FIFO)
         */
        $queue = new Queue();
        $queue->insert($upperProcessor);
        $queue->insert($lowerProcessor);

        $data = 'TeSt';
        self::assertEquals('test', $queue->processValue($data));
    }

    /**
     * @depends testQueueFIFO
     */
    public function testQueuePriorities()
    {
        $config = new Config($this->filter, 1);
        $lower = new StringToLower();
        $upper = new StringToUpper();
        $replace = new PregReplace('/[a-z]/', '');
        $lowerProcessor = new FilterProcessor($lower);
        $upperProcessor = new FilterProcessor($upper);
        $replaceProcessor = new FilterProcessor($replace);
        $queue = new Queue();

        /**
         * Insert lower case filter with higher priority
         */
        $queue->insert($upperProcessor, 10);
        $queue->insert($lowerProcessor, 1000);

        $config->simple = 'some MixedCase VALue';
        $queue->process($config);
        self::assertEquals('SOME MIXEDCASE VALUE', $config->simple);

        /**
         * Add even higher priority replace processor that will remove all lowercase letters
         */
        $queue->insert($replaceProcessor, 10000);
        $config->newValue = 'THIRD mixed CASE value';
        $queue->process($config);
        self::assertEquals('THIRD  CASE ', $config->newValue);
    }
}
