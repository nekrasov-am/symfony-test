<?php
require __DIR__.'/vendor/autoload.php';

use App\Commands\ParseCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$application = new Application();
$command = new ParseCommand();
$application->add($command);
$application->setDefaultCommand('parse', true);
$application->run();