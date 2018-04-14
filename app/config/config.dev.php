<?php

define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH' . dirname(dirname(__DIR__)) . DS);
define('LOG_PATH', APP_PATH . 'logs' . DS);
define('ROOT_PATH', dirname(APP_PATH) . DS);

$base = [
    'modelsDir' => APP_PATH . 'models' . DS,
    'logicsDir' => APP_PATH . 'logic' . DS,

    // log path
    'runningLog' => LOG_PATH . 'app.running.log',
    'systemLog' => LOG_PATH . 'system.log',
];

$services = [];
