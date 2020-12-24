<?php
declare(strict_types = 1);

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF8');

define('__ROOT__', realpath(__DIR__ . '/..'));

require_once __ROOT__ . '/vendor/autoload.php';

use App\Models\Status;
use App\Utils\Config;
use App\Utils\Tag;
use Illuminate\Database\Capsule\Manager;

try {
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

  $id = $_SERVER['QUERY_STRING'] ?? exit;
  $tag = Tag::fromString($id);

  $config = new Config(__ROOT__ . '/config');
  if (in_array($tag->getExtName(), $config->getExtensionList()) === false) {
    http_response_code(400);

    exit;
  }

  $status = Status::where('id', $id)->first();
  if ($status === null) {
    http_response_code(404);

    exit;
  }

  $dockerFile = $status->file ?? '';
  $dockerFile = str_replace(' && ', " && \\\n    ", $dockerFile);
  $dockerFile = trim($dockerFile);

  // https://superuser.com/questions/380772/removing-ansi-color-codes-from-text-stream
  $buildLog = $status->log ?? '';
  $buildLog = preg_replace('/\x1b\[[0-9;]*[a-zA-Z]/', '', $buildLog);
  $buildLog = trim($buildLog);

  $details = [
    'extension' => [
      'name'    => $tag->getExtName(),
      'version' => $tag->getVersion(),
      'php'     => $tag->getPhpVersion(),
      'os'      => $tag->getOsName()
    ],
    'dockerfile' => $dockerFile,
    'build' => [
      'time' => $status->build_time,
      'log'  => $buildLog
    ],
    'status' => $status->label
  ];
} catch (InvalidArgumentException $exception) {
  http_response_code(400);

  exit;
} catch (Exception $exception) {
  http_response_code(500);

  exit;
}


$mustache = new Mustache_Engine(
  [
    'loader' => new Mustache_Loader_FilesystemLoader(__ROOT__ . '/src/Views'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__ROOT__ . '/src/Views'),
    'logger' => new Mustache_Logger_StreamLogger('php://stderr')
  ]
);

$content = [
  'page' => [
    'title' => sprintf(
      'php-ext.com / %s (from %s using PHP %s-%s)',
      $tag->getExtName(),
      $tag->getVersion(),
      $tag->getPhpVersion(),
      $tag->getOsName()
    ),
    'description' => sprintf(
      'Compatibility Report for %s (from %s using PHP %s-%s)',
      $tag->getExtName(),
      $tag->getVersion(),
      $tag->getPhpVersion(),
      $tag->getOsName()
    )
  ],
  'content' => $mustache->render('details', $details)
];

echo $mustache->render('template', $content);
