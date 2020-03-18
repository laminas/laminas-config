# Introduction

laminas-config is designed to simplify access to configuration data within applications. It provides
a nested object, property-based user interface for accessing this configuration data within
application code. The configuration data may come from a variety of formats supporting hierarchical
data storage. Currently, laminas-config provides adapters that read and write configuration data
stored in INI, JSON, YAML, and XML files.

## Using Reader Classes

Normally, users will use one of the [reader classes](reader.md) to read a
configuration file, but if configuration data is available in a PHP array, one
may simply pass the data to `Laminas\Config\Config`'s constructor in order to
utilize a simple object-oriented interface:

```php
// An array of configuration data is given
$configArray = [
    'webhost'  => 'www.example.com',
    'database' => [
        'adapter' => 'pdo_mysql',
        'params'  => [
            'host'     => 'db.example.com',
            'username' => 'dbuser',
            'password' => 'secret',
            'dbname'   => 'mydatabase',
        ],
    ],
];

// Create the object-oriented wrapper using the configuration data
$config = new Laminas\Config\Config($configArray);

// Print a configuration datum (results in 'www.example.com')
echo $config->webhost;
```

As illustrated in the example above, `Laminas\Config\Config` provides nested object
property syntax to access configuration data passed to its constructor.

Along with the object-oriented access to the data values, `Laminas\Config\Config`
also has a `get()` method that accepts a default value to return if the data
element requested doesn't exist in the configuration array. For example:

```php
$host = $config->database->get('host', 'localhost');
```

## Using PHP Configuration Files

PHP-based configuration files are often recommended due to the speed with which
they are parsed, and the fact that they can be cached by opcode caches.

The following code illustrates how to use PHP configuration files.

Create a sepatared PHP file which contains the configuration, e.g. `config.php`:

```php
return [
    'webhost'  => 'www.example.com',
    'database' => [
        'adapter' => 'pdo_mysql',
        'params'  => [
            'host'     => 'db.example.com',
            'username' => 'dbuser',
            'password' => 'secret',
            'dbname'   => 'mydatabase',
        ],
    ],
];
```

Use the configuration array from this file in another PHP script, e.g.
`index.php`:

```php
$config = new Laminas\Config\Config(include 'config.php');

echo $config->webhost; // 'www.example.com'
```
