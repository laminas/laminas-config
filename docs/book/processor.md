# Laminas\\Config\\Processor

`Laminas\Config\Processor` provides the ability to perform operations on a
`Laminas\Config\Config` object. `Laminas\Config\Processor` is itself an interface that
defining two methods: `process()` and `processValue()`.

laminas-config provides the following concrete implementations:

- `Laminas\Config\Processor\Constant`: manage PHP constant values.
- `Laminas\Config\Processor\Filter`: filter the configuration data using `Laminas\Filter`.
- `Laminas\Config\Processor\Queue`: manage a queue of operations to apply to configuration data.
- `Laminas\Config\Processor\Token`: find and replace specific tokens.
- `Laminas\Config\Processor\Translator`: translate configuration values in other languages using `Laminas\I18n\Translator`.

<!-- markdownlint-disable-next-line header-increment -->
> ### What gets processed?
>
> Typically, you will process configuration _values_. However, there are use
> cases for supplying constant and/or token _keys_; one common one is for
> using class-based constants as keys to avoid using magic "strings":
>
> ```json
> {
>     "Acme\\Compoment::CONFIG_KEY": {}
> }
> ```
>
> As such, as of version 3.1.0, the `Constant` and `Token` processors can
> optionally also process the keys of the `Config` instance provided to them, by
> calling `enableKeyProcessing()` on their instances, or passing a boolean
> `true` value for the fourth constructor argument.

## Laminas\\Config\\Processor\\Constant

### Using Laminas\\Config\\Processor\\Constant

This example illustrates the basic usage of `Laminas\Config\Processor\Constant`:

```php
define ('TEST_CONST', 'bar');

// Provide the second parameter as boolean true to allow modifications:
$config = new Laminas\Config\Config(['foo' => 'TEST_CONST'], true);
$processor = new Laminas\Config\Processor\Constant();

echo $config->foo . ',';
$processor->process($config);
echo $config->foo;
```

This example returns the output: `TEST_CONST,bar`.

As of version 3.1.0, you can also tell the `Constant` processor to process keys:

```php
// At instantiation:
$processor = new Laminas\Config\Processor\Constant(true, '', '', true);

// Or later, via a method call:
$processor->enableKeyProcessing();
```

When enabled, any constant values found in keys will also be replaced.

## Laminas\\Config\\Processor\\Filter

### Using Laminas\\Config\\Processor\\Filter

This example illustrates basic usage of `Laminas\Config\Processor\Filter`:

```php
use Laminas\Filter\StringToUpper;
use Laminas\Config\Processor\Filter as FilterProcessor;
use Laminas\Config\Config;

// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['foo' => 'bar'], true);
$upper = new StringToUpper();

$upperProcessor = new FilterProcessor($upper);

echo $config->foo . ',';
$upperProcessor->process($config);
echo $config->foo;
```

This example returns the output: `bar,BAR`.

## Laminas\\Config\\Processor\\Queue

### Using Laminas\\Config\\Processor\\Queue

This example illustrates basic usage of `Laminas\Config\Processor\Queue`:

```php
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringToUpper;
use Laminas\Config\Processor\Filter as FilterProcessor;
use Laminas\Config\Processor\Queue;
use Laminas\Config\Config;

// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['foo' => 'bar'], true);
$upper  = new StringToUpper();
$lower  = new StringToLower();

$lowerProcessor = new FilterProcessor($lower);
$upperProcessor = new FilterProcessor($upper);

$queue = new Queue();
$queue->insert($upperProcessor);
$queue->insert($lowerProcessor);
$queue->process($config);

echo $config->foo;
```

This example returns the output: `bar`. The filters in the queue are applied in
*FIFO* (First In, First Out) order .

## Laminas\\Config\\Processor\\Token

### Using Laminas\\Config\\Processor\\Token

This example illustrates basic usage of `Laminas\Config\Processor\Token`:

```php
use Laminas\Config\Config;
use Laminas\Config\Processor\Token as TokenProcessor;

// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['foo' => 'Value is TOKEN'], true);
$processor = new TokenProcessor();

$processor->addToken('TOKEN', 'bar');
echo $config->foo . ',';
$processor->process($config);
echo $config->foo;
```

This example returns the output: `Value is TOKEN,Value is bar`.

As of version 3.1.0, you can also tell the `Token` processor to process keys:

```php
// At instantiation:
$processor = new Laminas\Config\Processor\Token($tokens, '', '', true);

// Or later, via a method call:
$processor->enableKeyProcessing();
```

When enabled, any token values found in keys will also be replaced.

### Using Token processor as a simple environment processor

Token processor can be utilized to populate config values using common
format `%env(ENV_VAR)%` with values from environment by setting Token
processor `$prefix` and `$suffix` parameters to `%env(` and `)%` respectively:

```php
use Laminas\Config\Config;
use Laminas\Config\Processor\Token as TokenProcessor;

putenv('AMQP_PASSWORD=guest');

// Populate list if tokens to replace from environment:
$processor = new TokenProcessor(getenv(), '%env(', ')%');

// Provide the second parameter as boolean true to allow modifications:
$config = new Config([
    'host' => '127.0.0.1',
    'port' => 5672,
    'username' => '%env(AMQP_USER)%',
    'password' => '%env(AMQP_PASSWORD)%',
    'vhost' => '/',
], true);

$processor->process($config);
print_r($config->toArray());
// Array
// (
//     [host] => 127.0.0.1
//     [port] => 5672
//     [username] => %env(AMQP_USER)%
//     [password] => guest
//     [vhost] => /
// )
```

Do note, however, that only values present in environment will be replaced.
This allows multiple fallback processors to be provided as a queue.

## Laminas\\Config\\Processor\\Translator

### Using Laminas\\Config\\Processor\\Translator

This example illustrates basic usage of `Laminas\Config\Processor\Translator`:

```php
use Laminas\Config\Config;
use Laminas\Config\Processor\Translator as TranslatorProcessor;
use Laminas\I18n\Translator\Translator;

// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['animal' => 'dog'], true);

/*
 * The following mapping is used for the translation
 * loader provided to the translator instance:
 *
 * $italian = [
 *     'dog' => 'cane'
 * ];
 */

$translator = new Translator();
// ... configure the translator ...
$processor = new TranslatorProcessor($translator);

echo "English: {$config->animal}, ";
$processor->process($config);
echo "Italian: {$config->animal}";
```

This example returns the output: `English: dog,Italian: cane`.
