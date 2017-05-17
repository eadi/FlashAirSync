<?php

use Zend\Console\Console;
use ZF\Console\Application;

chdir(dirname(__DIR__));

require_once 'vendor/autoload.php';

$application = new Application(
    "FlashAirSync",
    "0.1",
    include 'config/routes.php',
    Console::getInstance()
);

$exit = $application->run();
exit($exit);
