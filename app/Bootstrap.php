<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

if (($debug = getenv('DEBUG_MODE')) !== false){
    $configurator->setDebugMode($debug == 'true');
}

$configurator->enableDebugger(__DIR__ . '/../log' , 'petko.sinal@gmail.com');

$configurator->enableTracy(dirname(__DIR__ . "/log"));

error_reporting(E_ALL & ~E_DEPRECATED);

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->addDirectory(__DIR__ . '/../vendor/others')
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

if (extension_loaded('newrelic')) {
    newrelic_set_appname('zasadaci_poriadok');
}

return $container;