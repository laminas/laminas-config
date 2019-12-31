# Theory of Operation

Configuration data are made accessible to `Laminas\Config\Config`'s constructor via
an associative array, which may be multi-dimensional so data can be organized
from general to specific. Concrete adapter classes adapt configuration data from
storage to produce the associative array for `Laminas\Config\Config`'s constructor.
If needed, user scripts may provide such arrays directly to
`Laminas\Config\Config`'s constructor, without using a reader class.

Each value in the configuration data array becomes a property of the
`Laminas\Config\Config` object.  The key is used as the property name. If a value
is itself an array, then the resulting object property is created as a new
`Laminas\Config\Config` object, loaded with the array data. This occurs
recursively, such that a hierarchy of configuration data may be created with any
number of levels.

`Laminas\Config\Config` implements the [Countable](http://php.net/manual/en/class.countable.php)
and [Iterator](http://php.net/manual/en/class.iterator.php) interfaces in order
to facilitate simple access to configuration data. Thus, `Laminas\Config\Config`
objects support the [count()](http://php.net/count) function and PHP constructs
such as [foreach](http://php.net/foreach).

By default, configuration data made available through `Laminas\Config\Config` are
read-only, and an assignment (e.g., `$config->database->host = 'example.com';`)
results in an exception. This default behavior may be overridden through the
constructor, allowing modification of data values.  Also, when modifications are
allowed, `Laminas\Config\Config` supports unsetting of values (e.g.,
`unset($config->database->host)`). The `isReadOnly()` method can be used to
determine if modifications to a given `Laminas\Config\Config` object are allowed,
and the `setReadOnly()` method can be used to stop any further modifications to
a `Laminas\Config\Config` object that was created allowing modifications.

> ## Modifying Config does not save changes
>
> It is important not to confuse such in-memory modifications with saving
> configuration data out to specific storage media. Tools for creating and
> modifying configuration data for various storage media are out of scope with
> respect to `Laminas\Config\Config`. Third-party open source solutions are readily
> available for the purpose of creating and modifying configuration data for
> various storage media.

If you have two `Laminas\Config\Config` objects, you can merge them into a single
object using the `merge()` function. For example, given `$config` and
`$localConfig`, you can merge data from `$localConfig` to `$config` using
`$config->merge($localConfig);`. The items in `$localConfig` will override any
items with the same name in `$config`.

> ## Merging requires modifications
>
> The `Laminas\Config\Config` object that is performing the merge must have been
> constructed to allow modifications, by passing `TRUE` as the second parameter
> of the constructor. The `setReadOnly()` method can then be used to prevent any
> further modifications after the merge is complete.
