<?php

use Life\RunGameCommand;
use Symfony\Component\Console\Application;

require './vendor/autoload.php';

$app = new Application();
$app->add(new RunGameCommand());
$app->run();
