<?php

use Zend\Console\Console;
use ZF\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$application = new Application(
    "FlashAirSync",
    "0.1",
    include __DIR__ . '/../config/routes.php',
    Console::getInstance()
);

$exit = $application->run();
exit($exit);
