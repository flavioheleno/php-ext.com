<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Job;
use App\Models\Status;
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

$jobs = Job::query()
  ->where('assigned', true)
  ->where('finished', false)
  ->where('updated_at', '<', 'NOW() - \'1 hour\'::interval')
  ->orderBy('created_at')
  ->get();

echo date('[H:i:s]'), ' Cleaning ', $jobs->count(), ' dangling jobs', PHP_EOL;
$jobs->map(function ($job) {
  echo date('[H:i:s]'), '  -> ', $job->payload['tag'], PHP_EOL;
  $status = Status::where('id', $job->payload['tag'])->first();
  if ($status === null) {
    $status = Status::create(
      [
        'id'    => $job->payload['tag'],
        'label' => Status::BUILD
      ]
    );
  }

  $status->label = Status::FAIL;
  $status->log = 'Dangling build process';
  $status->save();

  $job->assigned = false;
  $job->finished = true;
  $job->failed = true;
  $job->save();
});
