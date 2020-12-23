#!/usr/bin/env php
<?php
declare(strict_types = 1);

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF8');

// ensure correct absolute path
chdir(dirname($argv[0]));

define('__ROOT__', realpath(__DIR__ . '/../'));

require_once __ROOT__ . '/vendor/autoload.php';

use App\Console\Build;
use App\Console\Maintenance;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

$app = new Application('php-ext.com console');

$commandLoader = new FactoryCommandLoader(
  [
    Build\AddCommand::getDefaultName() => function (): Build\AddCommand {
      return new Build\AddCommand();
    },
    Build\BuilderCommand::getDefaultName() => function (): Build\BuilderCommand {
      return new Build\BuilderCommand();
    },
    Build\StatusCommand::getDefaultName() => function (): Build\StatusCommand {
      return new Build\StatusCommand();
    },
    Maintenance\ClearDanglingCommand::getDefaultName() => function (): Maintenance\ClearDanglingCommand {
      return new Maintenance\ClearDanglingCommand();
    }
  ]
);

$app->setCommandLoader($commandLoader);
$app->run();
