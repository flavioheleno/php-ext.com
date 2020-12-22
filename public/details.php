<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

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

  $config = new Config(__DIR__ . '/../config');
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
} catch (InvalidArgumentException $exception) {
  http_response_code(400);

  exit;
} catch (Exception $exception) {
  http_response_code(500);

  exit;
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Compatibility Report for <?php echo $tag->getExtName(); ?> (PHP <?php echo $tag->getPhpVersion(); ?>)">
    <meta name="robots" content="index, follow">
    <title>php-ext.com / <?php echo $tag->getExtName(); ?> (PHP <?php echo $tag->getPhpVersion(); ?>)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/icons@1.0.1/css/brand.min.css" integrity="sha256-rhKRwO3dmDMXxlfkd1nmCUpdrJlmptpWINKNe8+sTx4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/icons@1.0.1/css/free.min.css" integrity="sha256-QmAUWghG3rIhqMHI8F7vC+93NOR4N8b8MJtSjZpZwko=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <style>
      body {
        font-family: 'Roboto'
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <h1>php-ext.com / <strong><?php echo $tag->getExtName(); ?></strong> <small class="text-muted">(PHP <?php echo $tag->getPhpVersion(); ?>)</small></h1>
      <!-- <div class="p-1 bg-dark text-white">
        <strong>Details</strong>
      </div>
      <dl class="row">
        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9"><?php echo $status->label; ?></dd>
        <dt class="col-sm-3">Source</dt>
        <dd class="col-sm-9"><?php echo $tag->getVersion(); ?></dd>
        <dt class="col-sm-3">Build Time</dt>
        <dd class="col-sm-9"><?php echo $status->build_time; ?></dd>
      </dl> -->
      <div class="p-1 bg-dark text-white">
        <strong>Dockerfile</strong>
      </div>
      <div class="">
        <pre class="pre-scrollable">
          <code>
<?php echo htmlentities($dockerFile); ?>
          </code>
        </pre>
      </div>
      <div class="p-1 bg-dark text-white">
        <strong>Build output</strong>
      </div>
      <div class="">
        <pre class="pre-scrollable">
          <code>
<?php echo htmlentities($buildLog); ?>
          </code>
        </pre>
      </div>
    </div>
  </body>
</html>
