<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP

$log_dir           = __DIR__ . '/../log';
$temp_dir          = __DIR__ . '/../temp';
$temp_sessions_dir = __DIR__ . '/../temp/sessions';

if (!file_exists($log_dir) && !is_dir($log_dir)) {
    mkdir($log_dir);
}

$configurator->enableTracy($log_dir);
$configurator->setTimeZone('Europe/Prague');

if (!file_exists($temp_dir) && !is_dir($temp_dir)) {
    mkdir($temp_dir);
}

if (!file_exists($temp_sessions_dir) && !is_dir($temp_sessions_dir)) {
    mkdir($temp_sessions_dir);
}

$configurator->setTempDirectory($temp_dir);

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');

if (!Tracy\Debugger::$productionMode) {
    $configurator->addConfig(__DIR__ . '/config/config.local.neon');
}

$container = $configurator->createContainer();

return $container;
