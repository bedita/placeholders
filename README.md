# Placeholders plugin for BEdita

[![Github Actions PHP](https://github.com/bedita/placeholders/workflows/php/badge.svg)](https://github.com/bedita/placeholders/actions?query=workflow%3Aphp)
[![codecov](https://codecov.io/gh/bedita/placeholders/branch/main/graph/badge.svg)](https://codecov.io/gh/bedita/placeholders)
[![phpstan](https://img.shields.io/badge/PHPStan-level%205-brightgreen.svg)](https://phpstan.org)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bedita/placeholders/badges/quality-score.png)](https://scrutinizer-ci.com/g/bedita/placeholders/)
[![Version](https://img.shields.io/packagist/v/bedita/placeholders.svg?label=stable)](https://packagist.org/packages/bedita/placeholders)
[![License](https://img.shields.io/badge/License-LGPL_v3-orange.svg)](https://github.com/bedita/placeholders/blob/main/LICENSE.LGPL)

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
