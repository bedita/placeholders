# Placeholders plugin for BEdita

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
