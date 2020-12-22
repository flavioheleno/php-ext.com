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

$extName = $argv[1] ?? '';
if ($extName === '') {
  echo 'Usage: php ', $argv[0], ' <ext-name>', PHP_EOL;

  return 1;
}

if (in_array($extName, $config->getExtensionList()) === false) {
  echo date('[H:i:s]'), ' Error! Unsupported extension "', $extName, '"!', PHP_EOL;

  return 1;
}

echo date('[H:i:s]'), ' Creating jobs for ', $extName, '...', PHP_EOL;
foreach ($config->getVersionList() as $version) {
  foreach ($config->getPHPMatrix() as $php) {
    $tag = Tag::fromString("{$extName}:{$version}@{$php}");
    $job = Job::create(
      [
        'function' => 'build',
        'payload'  => [
          'tag' => (string)$tag,
          'ext' => $tag->getExtName(),
          'ver' => $tag->getVersion(),
          'php' => $tag->getPhpVersion(),
          'os'  => $tag->getOsName()
        ]
      ]
    );

    echo date('[H:i:s]'), '  -> ', $tag, ': ', $job->id, PHP_EOL;
  }
}
