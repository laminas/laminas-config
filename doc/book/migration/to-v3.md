# Migration to version 3

Version 3 is essentially fully backwards compatible with previous versions, with
one key exception: `Laminas\Config\Factory` no longer requires usage of
laminas-servicemanager for resolving plugins.

The reason this is considered a backwards compatibility break is due to
signature changes:

- `Factory::setReaderPluginManager()` now accepts a
  `Psr\Container\ContainerInterface`, and not a `Laminas\Config\ReaderPluginManager`
  instance; `ReaderPluginManager`, however, still fulfills that typehint.

- `Factory::getReaderPluginManager()` now returns a
  `Psr\Container\ContainerInterface` &mdash; specifically, a
  `Laminas\Config\StandaloneReaderPluginManager` &mdash;  and not a
  `Laminas\Config\ReaderPluginManager` instance, by default; `ReaderPluginManager`,
  however, still fulfills that typehint.

- `Factory::setWriterPluginManager()` now accepts a
  `Psr\Container\ContainerInterface`, and not a `Laminas\Config\WriterPluginManager`
  instance; `WriterPluginManager`, however, still fulfills that typehint.

- `Factory::getWriterPluginManager()` now returns a
  `Psr\Container\ContainerInterface` &mdash; specifically, a
  `Laminas\Config\StandaloneWriterPluginManager` &mdash;  and not a
  `Laminas\Config\WriterPluginManager` instance, by default; `WriterPluginManager`,
  however, still fulfills that typehint.

If you were extending the class, you will need to update your signatures
accordingly.

This particular update means that you may use any PSR-11 container as a reader
or writer plugin manager, and no longer require installation of
laminas-servicemanager to use the plugin manager facilities.
