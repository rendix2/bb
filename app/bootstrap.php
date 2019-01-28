<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP

$log_dir           = __DIR__ . '/../log';
$temp_dir          = __DIR__ . '/../temp';
$temp_sessions_dir = __DIR__ . '/../temp/sessions';
$config_dir        = __DIR__ . '/../config';

// check if all dirs exists first

if (!file_exists($config_dir) && !is_dir($config_dir)) {
    mkdir($config_dir);
}

if (!file_exists($log_dir) && !is_dir($log_dir)) {
    mkdir($log_dir);
}

if (!file_exists($temp_dir) && !is_dir($temp_dir)) {
    mkdir($temp_dir);
}

if (!file_exists($temp_sessions_dir) && !is_dir($temp_sessions_dir)) {
    mkdir($temp_sessions_dir);
}

$configurator->enableTracy($log_dir);
$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory($temp_dir);

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig($config_dir . '/config.neon');

if (!Tracy\Debugger::$productionMode) {
    $configurator->addConfig($config_dir . '/config.local.neon');
}

$container = $configurator->createContainer();

return $container;
