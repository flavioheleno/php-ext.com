<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Job;
use App\Utils\Config;
use App\Utils\Tag;
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
  $tag = Tag::fromString($build);
  $job = Job::create(
    [
      'function' => 'build',
      'payload'  => [
        'tag' => $build,
        'ext' => $tag->getExtName(),
        'ver' => $tag->getVersion(),
        'php' => $tag->getPhpVersion(),
        'os'  => $tag->getOsName()
      ]
    ]
  );
}
