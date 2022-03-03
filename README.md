# Placeholders plugin for BEdita

[![GitHub Actions tests](https://github.com/bedita/placeholders/actions/workflows/test.yml/badge.svg?event=push&branch=main)](https://github.com/bedita/placeholders/actions/workflows/test.yml?query=event%3Apush+branch%3Amain)
[![codecov](https://codecov.io/gh/bedita/placeholders/branch/main/graph/badge.svg)](https://codecov.io/gh/bedita/placeholders)

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require bedita/placeholders
```

## Usage

Add the plugin to your application:

**src/Application.php**
```php
$this->addPlugin('BEdita/Placeholders');
```

and run plugin migrations to create the `placeholder` relation:

```sh
$ bin/cake migrations migrate -p BEdita/Placeholders
```
