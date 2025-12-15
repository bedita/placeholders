<?php
declare(strict_types=1);

use BEdita\Core\Filesystem\FilesystemRegistry;
use BEdita\Core\ORM\Locator\TableLocator;
use BEdita\Placeholders\Test\TestApp\Application;
use BEdita\Placeholders\Test\TestApp\Filesystem\Adapter\NullAdapter;
use Cake\Cache\Cache;
use Cake\Cache\Engine\ArrayEngine;
use Cake\Cache\Engine\NullEngine;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Engine\ConsoleLog;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Migrations\TestSuite\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

define('ROOT', dirname(__DIR__));
define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);

require CORE_PATH . 'config' . DS . 'bootstrap.php';
require CAKE . 'functions.php';

define('APP', ROOT . DS . 'tests' . DS . 'TestApp' . DS);

define('TMP', sys_get_temp_dir() . DS);
define('LOGS', ROOT . DS . 'logs' . DS);
define('CONFIG', ROOT . DS . 'config' . DS);
define('CACHE', TMP . 'cache' . DS);

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'BEdita\Placeholders\Test\TestApp',
    'encoding' => 'UTF-8',
    'paths' => [
        'plugins' => [ROOT . 'Plugin' . DS],
        'templates' => [APP . 'Template' . DS],
    ],
]);

Log::setConfig([
    'debug' => [
        'engine' => ConsoleLog::class,
        'levels' => ['notice', 'info', 'debug'],
    ],
    'error' => [
        'engine' => ConsoleLog::class,
        'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
    ],
]);

Cache::drop('_bedita_object_types_');
Cache::drop('_bedita_core_');
Cache::setConfig([
    '_cake_translations_' => ['engine' => ArrayEngine::class],
    '_cake_model_' => ['engine' => ArrayEngine::class],
    '_bedita_object_types_' => ['className' => NullEngine::class],
    '_bedita_core_' => ['className' => NullEngine::class],
]);

ConnectionManager::drop('test');
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}
ConnectionManager::setConfig('test', ['url' => getenv('db_dsn')]);
ConnectionManager::alias('test', 'default');

Router::reload();

if (!TableRegistry::getTableLocator() instanceof TableLocator) {
    TableRegistry::setTableLocator(new TableLocator());
}

Security::setSalt('3ikcOGwIYlAP6msatcNj76a6iueuyasdNTn');

(new Migrator())->run(['plugin' => 'BEdita/Core']);

FilesystemRegistry::setConfig([
    'default' => ['className' => NullAdapter::class],
    'thumbnails' => ['className' => NullAdapter::class],
]);

$app = new Application(dirname(__DIR__) . '/config');
$app->bootstrap();
$app->pluginBootstrap();

// clear all before running tests
TableRegistry::getTableLocator()->clear();
Cache::clearAll();
