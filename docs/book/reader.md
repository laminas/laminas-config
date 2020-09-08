# Laminas\\Config\\Reader

`Laminas\Config\Reader` gives you the ability to read a config file. It works with
concrete implementations for different file formats. `Laminas\Config\Reader` itself
is only an interface, defining the methods `fromFile()` and `fromString()`. The
concrete implementations of this interface are:

- `Laminas\Config\Reader\Ini`
- `Laminas\Config\Reader\Xml`
- `Laminas\Config\Reader\Json`
- `Laminas\Config\Reader\Yaml`
- `Laminas\Config\Reader\JavaProperties`

`fromFile()` and `fromString()` are expected to return a PHP array containing
the data from the specified configuration.

<!-- markdownlint-disable-next-line header-increment -->
> ### Differences from Laminas1
>
> The `Laminas\Config\Reader` component no longer supports the following features:
>
> - Inheritance of sections.
> - Reading of specific sections.

## Laminas\\Config\\Reader\\Ini

`Laminas\Config\Reader\Ini` enables developers to store configuration data in a
familiar INI format, and then to read them in the application by using an array
syntax.

`Laminas\Config\Reader\Ini` utilizes the [`parse_ini_file()`](http://php.net/parse_ini_file) PHP
function. Please review this documentation to be aware of its specific behaviors, which propagate to
`Laminas\Config\Reader\Ini`, such as how the special values of `TRUE`, `FALSE`, "yes", "no", and
`NULL` are handled.

> ### Key Separator
>
> By default, the key separator character is the period character (`.`). This can be changed,
> however, using the `setNestSeparator()` method. For example:
>
> ```php
> $reader = new Laminas\Config\Reader\Ini();
> $reader->setNestSeparator('-');
> ```

> ### Process Sections
>
> By default, the INI reader executes `parse_ini_file()`  with the optional parameter `$process_sections` being `true`. The result is a multidimensional array, with the section names and settings included.
>
> To merge sections, set the parameter via `setProcessSections()` to `false` as follows.
>
> ```php
> $reader = new Laminas\Config\Reader\Ini();
> $reader->setProcessSections(false);
> ```

> ### Typed Mode
>
> By default, the INI reader executes `parse_ini_file()`  with the optional parameter `$scanner_mode` set to `INI_SCANNER_NORMAL`. This results in all config values being returned as strings.
>
> To automatically return integer, boolean, and null values as the appropriate types, switch to typed mode with `setTypedMode()`, and `parse_ini_file()` will be called with `INI_SCANNER_TYPED` instead.
>
> ```php
> $reader = new Laminas\Config\Reader\Ini();
> $reader->setTypedMode(true);
> ```

The following example illustrates basic usage of `Laminas\Config\Reader\Ini` for
loading configuration data from an INI file. In this example, configuration data
for both a production system and for a staging system exists.

```ini
webhost                  = 'www.example.com'
database.adapter         = 'pdo_mysql'
database.params.host     = 'db.example.com'
database.params.username = 'dbuser'
database.params.password = 'secret'
database.params.dbname   = 'dbproduction'
```

We can use `Laminas\Config\Reader\Ini` to read this INI file:

```php
$reader = new Laminas\Config\Reader\Ini();
$data   = $reader->fromFile('/path/to/config.ini');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

`Laminas\Config\Reader\Ini` supports a feature to include the content of a INI file
in a specific section of another INI file. For instance, suppose we have an INI
file with the database configuration:

```ini
database.adapter         = 'pdo_mysql'
database.params.host     = 'db.example.com'
database.params.username = 'dbuser'
database.params.password = 'secret'
database.params.dbname   = 'dbproduction'
```

We can include this configuration in another INI file by using the `@include`
notation:

```ini
webhost  = 'www.example.com'
@include = 'database.ini'
```

If we read this file using the component `Laminas\Config\Reader\Ini`, we will obtain the same
configuration data structure as in the previous example.

The `@include = 'file-to-include.ini'` notation can be used also in a subelement
of a value. For instance we can have an INI file like the following:

```ini
adapter         = 'pdo_mysql'
params.host     = 'db.example.com'
params.username = 'dbuser'
params.password = 'secret'
params.dbname   = 'dbproduction'
```

And assign the `@include` as a subelement of the `database` value:

```ini
webhost           = 'www.example.com'
database.@include = 'database.ini'
```

## Laminas\\Config\\Reader\\Xml

`Laminas\Config\Reader\Xml` enables developers to provide configuration data in a
familiar XML format and consume it in the application using an array syntax.
The root element of the XML file or string is irrelevant and may be named
arbitrarily.

The following example illustrates basic usage of `Laminas\Config\Reader\Xml` for loading configuration
data from an XML file. First, our XML configuration in the file `config.xml`:

```markup
<?xml version="1.0" encoding="utf-8"?>
<config>
    <webhost>www.example.com</webhost>
    <database>
        <adapter value="pdo_mysql"/>
        <params>
            <host value="db.example.com"/>
            <username value="dbuser"/>
            <password value="secret"/>
            <dbname value="dbproduction"/>
        </params>
    </database>
</config>
```

We can use the `Laminas\Config\Reader\Xml` to read the XML configuration:

```php
$reader = new Laminas\Config\Reader\Xml();
$data   = $reader->fromFile('/path/to/config.xml');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname']['value'];  // prints "dbproduction"
```

`Laminas\Config\Reader\Xml` utilizes PHP's [XMLReader](http://php.net/xmlreader) class. Please
review its documentation to be aware of its specific behaviors, which propagate to
`Laminas\Config\Reader\Xml`.

Using `Laminas\Config\Reader\Xml`, we can include the content of XML files in a
specific XML element.  This is provided using the standard
[XInclude](http://www.w3.org/TR/xinclude/) functionality of XML. To use this
functionality, you must add the namespace
`xmlns:xi="http://www.w3.org/2001/XInclude"` to the XML file.

Suppose we have an XML file that contains only the database configuration:

```markup
<?xml version="1.0" encoding="utf-8"?>
<config>
    <database>
        <adapter>pdo_mysql</adapter>
        <params>
            <host>db.example.com</host>
            <username>dbuser</username>
            <password>secret</password>
            <dbname>dbproduction</dbname>
        </params>
    </database>
</config>
```

We can include this configuration in another XML file using an xinclude:

```markup
<?xml version="1.0" encoding="utf-8"?>
<config xmlns:xi="http://www.w3.org/2001/XInclude">
    <webhost>www.example.com</webhost>
    <xi:include href="database.xml"/>
</config>
```

The syntax to include an XML file in a specific element is `<xi:include
href="file-to-include.xml"/>`

## Laminas\\Config\\Reader\\Json

`Laminas\Config\Reader\Json` enables developers to consume configuration data in
JSON, and read it in the application by using an array syntax.

The following example illustrates a basic use of `Laminas\Config\Reader\Json` for
loading configuration data from a JSON file.

Consider the following JSON configuration file:

```json
{
  "webhost"  : "www.example.com",
  "database" : {
    "adapter" : "pdo_mysql",
    "params"  : {
      "host"     : "db.example.com",
      "username" : "dbuser",
      "password" : "secret",
      "dbname"   : "dbproduction"
    }
  }
}
```

We can use `Laminas\Config\Reader\Json` to read the file:

```php
$reader = new Laminas\Config\Reader\Json();
$data   = $reader->fromFile('/path/to/config.json');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

`Laminas\Config\Reader\Json` utilizes [laminas-json](https://github.com/laminas/laminas-json).

Using `Laminas\Config\Reader\Json`, we can include the content of a JSON file in a
specific JSON section or element. This is provided using the special syntax
`@include`. Suppose we have a JSON file that contains only the database
configuration:

```json
{
  "database" : {
    "adapter" : "pdo_mysql",
    "params"  : {
      "host"     : "db.example.com",
      "username" : "dbuser",
      "password" : "secret",
      "dbname"   : "dbproduction"
    }
  }
}
```

Now let's include it via another configuration file:

```json
{
    "webhost"  : "www.example.com",
    "@include" : "database.json"
}
```

## Laminas\\Config\\Reader\\Yaml

`Laminas\Config\Reader\Yaml` enables developers to consume configuration data in a
YAML format, and read them in the application by using an array syntax. In order
to use the YAML reader, we need to pass a callback to an external PHP library or
use the [YAML PECL extension](http://www.php.net/manual/en/book.yaml.php).

The following example illustrates basic usage of `Laminas\Config\Reader\Yaml`,
using the YAML PECL extension.

Consider the following YAML file:

```yaml
webhost: www.example.com
database:
    adapter: pdo_mysql
    params:
      host:     db.example.com
      username: dbuser
      password: secret
      dbname:   dbproduction
```

We can use `Laminas\Config\Reader\Yaml` to read this YAML file:

```php
$reader = new Laminas\Config\Reader\Yaml();
$data   = $reader->fromFile('/path/to/config.yaml');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

If you want to use an external YAML reader, you must pass a callback function to
the class constructor.  For instance, if you want to use the
[Spyc](https://github.com/mustangostang/spyc/) library:

```php
// include the Spyc library
require_once 'path/to/spyc.php';

$reader = new Laminas\Config\Reader\Yaml(['Spyc', 'YAMLLoadString']);
$data   = $reader->fromFile('/path/to/config.yaml');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

You can also instantiate `Laminas\Config\Reader\Yaml` without any parameters, and
specify the YAML reader using the `setYamlDecoder()` method.

Using `Laminas\Config\ReaderYaml`, we can include the content of another YAML file
in a specific YAML section or element. This is provided using the special syntax
`@include`.

Consider the following YAML file containing only database configuration:

```yaml
database:
    adapter: pdo_mysql
    params:
      host:     db.example.com
      username: dbuser
      password: secret
      dbname:   dbproduction
```

We can include this configuration in another YAML file:

```yaml
webhost:  www.example.com
@include: database.yaml
```

## Laminas\\Config\\Reader\\JavaProperties

`Laminas\Config\Reader\JavaProperties` enables developers to provide configuration
data in the popular JavaProperties format, and read it in the application by
using array syntax.

The following example illustrates basic usage of `Laminas\Config\Reader\JavaProperties`
for loading configuration data from a JavaProperties file.

Suppose we have the following JavaProperties configuration file:

```properties
#comment
!comment
webhost:www.example.com
database.adapter:pdo_mysql
database.params.host:db.example.com
database.params.username:dbuser
database.params.password:secret
database.params.dbname:dbproduction
```

We can use `Laminas\Config\Reader\JavaProperties` to read it:

```php
$reader = new Laminas\Config\Reader\JavaProperties();
$data   = $reader->fromFile('/path/to/config.properties');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database.params.dbname'];  // prints "dbproduction"
```

### Alternate delimiters

- Since 3.2.0

By default, the `JavaProperties` reader will assume that the delimiter between
key/value pairs is `:`. If you wish to use an alternate delimiter, pass it as
the first argument to the constructor:

```php
$reader = new JavaProperties('='); // Use = as the delimiter
```

When specifying the default delimiter, you can use either `:` or the constant
`JavaProperties::DELIMITER_DEFAULT`.

### Trimming whitespace

- Since 3.2.0

By default, whitespace is considered significant in JavaProperties files,
including trailing whitespace. If you wish to have keys and values trimmed
during parsing, you can pass a boolean `true` value, or the constant
`JavaProperties::WHITESPACE_TRIM`, as the second argument to the constructor:

```php
$reader = new JavaProperties(
    JavaProperties::DELIMITER_DEFAULT, // use default delimiter
    JavaProperties::WHITESPACE_TRIM
);
```

This can be useful particularly when surrounding the delimiter with whitespace:

```properties
webhost = www.example.com
database.adapter = pdo_mysql
database.params.host = db.example.com
database.params.username = dbuser
database.params.password = secret
database.params.dbname = dbproduction
```
