<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Job;
use App\Utils\Config;
use Illuminate\Database\Capsule\Manager;

// ensure correct absolute path
chdir(dirname($argv[0]));

$manager = new Manager();
// Same as database configuration file of Laravel.
$manager->addConnection(
  [
    'driver'   => 'sqlite',
    'database' => '../data/php-ext.sqlite3',
    'prefix'   => '',
  ],
  'default'
);

$manager->bootEloquent();
$manager->setAsGlobal();

$config = new Config(__DIR__ . '/../config');

foreach ($config->getBuildMatrix() as $build) {
  $matches = [];
  if (preg_match('/^(?<ext>[a-z0-9_]+):(?<ver>pecl|dev)@(?<php>[0-9]+\.[0-9]+\.[0-9]+(\-zts)?)-(?<os>[a-z]+)$/', $build, $matches)) {
    $job = Job::create(
      [
        'function' => 'build',
        'payload'  => [
          'tag' => $build,
          'ext' => $matches['ext'],
          'ver' => $matches['ver'],
          'php' => $matches['php'],
          'os'  => $matches['os']
        ]
      ]
    );
  }
}
