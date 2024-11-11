<?php

require 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Life\Commands\RunGameCommand;
use Life\Handlers\XmlFileHandler;

// Create instance file handler
$fileHandler = new XmlFileHandler();

// Create instance command with provide dependency
$command = new RunGameCommand($fileHandler);

// Create instance console application Symfony
$application = new Application();
$application->add($command);

// Run application
$application->run();