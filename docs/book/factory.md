# The Factory

`Laminas\Config\Factory` provides the ability to load configuration files to an
array or to a `Laminas\Config\Config` object. The factory has two purposes

- Loading configuration file(s)
- Storing a configuration file

<!-- markdownlint-disable-next-line header-increment -->
> ### Storage writes to a single file
>
> Storing the configuration always writes to a **single** file. The factory is
> not aware of merged configuration files, and as such cannot split
> configuration to multiple files.  If you want to store particular
> configuration sections to separate files, you should separate them manually.

## Loading configuration files

The first example illustrates loading a single configuration file:

```php
// Load a PHP file as array:
$config = Laminas\Config\Factory::fromFile(__DIR__ . '/config/my.config.php');

// Load an XML file as Config object; the second parameter, when true,
// casts the configuration to a Config instance:
$config = Laminas\Config\Factory::fromFile(__DIR__.'/config/my.config.xml', true);
```

The next example demonstrates merging multiple files; note that they are in
separate formats!

```php
$config = Laminas\Config\Factory::fromFiles([
    __DIR__.'/config/my.config.php',
    __DIR__.'/config/my.config.xml',
]);
```

## Storing configuration

Sometimes you may want to write configuration to a file. To do this, use the
factory's `toFile()` method:

```php
$config = new Laminas\Config\Config([], true);
$config->settings = [];
$config->settings->myname = 'framework';
$config->settings->date   = '2012-12-12 12:12:12';

//Store the configuration
Laminas\Config\Factory::toFile(__DIR__ . '/config/my.config.php', $config);

//Store an array
$config = [
    'settings' => [
        'myname' => 'framework',
        'data'   => '2012-12-12 12:12:12',
    ],
];

Laminas\Config\Factory::toFile(__DIR__ . '/config/my.config.php', $config);
```
