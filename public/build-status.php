<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Status;
use App\Utils\Config;
use Illuminate\Database\Capsule\Manager;

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

$response = [
  'status'  => false,
  'list'    => [],
  'metrics' => [
    Status::BUILD   => 0,
    Status::SKIP    => 0,
    Status::PASS    => 0,
    Status::FAIL    => 0,
    Status::PENDING => 0
  ]
];
foreach ($config->getBuildMatrix() as $build) {
  $status = Status::find($build);

  $current = Status::PENDING;
  if ($status !== null) {
    $current = $status->label;
  }

  $response['list'][$build] = $current;
  $response['metrics'][$current]++;
}

$response['status'] = in_array(
  Status::PENDING,
  array_unique(array_values($response['list']))
) === false;

header('Content-Type: application/json');
echo json_encode($response);
