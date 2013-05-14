# Loader - Active Development Version

## Description

The "loader" package for the Forall Framework. This package handles loading and
initializing of packages and classes within packages. It performs the following tasks:

* Provides a much more convenient way to initialize your package through having a Loader
  class in `yourPackage/.package/Loader.php` that extends the `AbstractLoader` provided.
* handles a lot of the class loading automatically according to
  [FIG standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).

## Features

* Lazy loading
* Load order handling
* Dependency loading

## Change log

The change-log can be found in `CHANGES.md` in this directory.

## License

Copyright (c) 2013 Avaq, https://github.com/Avaq

Forall is licensed under the MIT license. The license is included as LICENSE.md in the 
[Forall environment repository](https://github.com/ForallFramework/Forall).
