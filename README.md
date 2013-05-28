# Loader - Version 0.1.0 Beta

## Description

The "loader" package for the Forall Framework. This package handles loading and
initializing of packages and classes within packages. It performs the following tasks:

* Provides a much more convenient way to initialize your package through specifying
  includes that either load directly, or lazily as the package starts being used.
* Handles a lot of the class loading automatically according to
  [FIG standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).

## Features

* Lazy loading
* Load order handling
* Dependency loading
* Complies with 
  [FIG standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
  to ensure higher compatibility with other packages.

## Change log

The change-log can be found in `CHANGES.md` in this directory.

## License

Copyright (c) 2013 Avaq, https://github.com/Avaq

Forall is licensed under the MIT license. The license is included as LICENSE.md in the 
[Forall environment repository](https://github.com/ForallFramework/Forall).
