<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Job;
use App\Models\Status;
use App\Settings\Extension;
use App\Settings\OperatingSystem;
use App\Utils\Config;
use App\Utils\Dockerfile;
use Docker\Context\ContextBuilder;
use Docker\Docker;
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

do {
  try {
    $job = Job::query()
      ->where('assigned', false)
      ->where('finished', false)
      ->orderBy('created_at')
      ->first();

    if ($job === null) {
      echo date('[H:i:s]'), ' No available jobs', PHP_EOL;
      exit;
    }

    $job->assigned = true;
    $job->save();

    $status = Status::where('id', $job->payload['tag'])->first();
    if ($status === null) {
      $status = Status::create(
        [
          'id'    => $job->payload['tag'],
          'label' => Status::BUILD
        ]
      );
    }

    $config = new Config(__DIR__ . '/../config');

    $extSpecs = $config->getExtensionSpecs();
    if (isset($extSpecs[$job->payload['ext']]) === false) {
      throw new RuntimeException("Specs not found for extension '{$job->payload['ext']}'");
    }

    $extension = Extension::fromArray($job->payload['ext'], $extSpecs[$job->payload['ext']]);

    $osSpecs = $config->getOSSpecs();
    if (isset($osSpecs[$job->payload['os']]) === false) {
      throw new RuntimeException("Specs not found for Operating System '{$job->payload['os']}'");
    }

    $os = OperatingSystem::fromArray($job->payload['os'], $osSpecs[$job->payload['os']]);

    echo date('[H:i:s]'), ' Building ', $job->payload['tag'], '...', PHP_EOL;

    if ($extension->isPeclAvailable() === false && $job->payload['ver'] === 'pecl') {
      echo date('[H:i:s]'), '  -> Skip!', PHP_EOL;

      $status->label = Status::SKIP;
      $status->log = 'This extension is not available via PECL';
      $status->save();

      $job->assigned = false;
      $job->finished = true;
      $job->save();

      continue;
    }

    if ($extension->isZtsRequired() && str_ends_with($job->payload['php'], '-zts') === false) {
      echo date('[H:i:s]'), '  -> Skip!', PHP_EOL;

      $status->label = Status::SKIP;
      $status->log = 'This extension requires a ZTS version of PHP';
      $status->save();

      $job->assigned = false;
      $job->finished = true;
      $job->save();

      continue;
    }

    if ($extension->getMinRequiredPHP() !== '' && version_compare(str_replace('-zts', '', $job->payload['php']), $extension->getMinRequiredPHP(), '<')) {
      echo date('[H:i:s]'), '  -> Skip!', PHP_EOL;

      $status->label = Status::SKIP;
      $status->log = sprintf('This extension requires PHP %s or later', $extension->getMinRequiredPHP());
      $status->save();

      $job->assigned = false;
      $job->finished = true;
      $job->save();

      continue;
    }

    if ($extension->getMaxRequiredPHP() !== '' && version_compare(str_replace('-zts', '', $job->payload['php']), $extension->getMaxRequiredPHP(), '<')) {
      echo date('[H:i:s]'), '  -> Skip!', PHP_EOL;

      $status->label = Status::SKIP;
      $status->log = sprintf('This extension requires PHP %s or older', $extension->getMaxRequiredPHP());
      $status->save();

      $job->assigned = false;
      $job->finished = true;
      $job->save();

      continue;
    }

    $docker = Docker::create();
    $versionTag = sprintf('%s-%s', $job->payload['php'], $job->payload['os']);

    echo date('[H:i:s]'), '  -> Creating context', PHP_EOL;
    $commandList = [];
    $dockerfile = new Dockerfile($os);
    switch ($job->payload['ver']) {
      case 'pecl':
        $commandList = $dockerfile->buildFromPecl($extension);
        break;
      case 'dev':
        $commandList = $dockerfile->buildFromSource($extension);
        break;
    }

    $contextBuilder = new ContextBuilder();
    $contextBuilder->from(sprintf('php:%s', $versionTag));
    $contextBuilder->workdir('/tmp');
    foreach ($commandList as $runBlock) {
      $contextBuilder->run(implode(' && ', $runBlock));
    }

    $context = $contextBuilder->getContext();
    echo date('[H:i:s]'), '  -> Done', PHP_EOL;

    $imageTag = sprintf(
      '%s:%s-%s',
      $job->payload['ext'],
      $job->payload['ver'],
      str_replace(':', '-', $versionTag)
    );

    echo date('[H:i:s]'), '  -> Building image', PHP_EOL;
    $buildTime = microtime(true);
    $buildStream = $docker->imageBuild(
      $context->read(),
      [
        't' => $imageTag
      ]
    );
    $buildTime = microtime(true) - $buildTime;
    echo date('[H:i:s]'), '  -> Done', PHP_EOL;

    // echo date('[H:i:s]'), '  -> Cleanup after building', PHP_EOL;
    // $docker->imageDelete($imageTag);
    // echo date('[H:i:s]'), '  -> Done', PHP_EOL;

    $buildOutput = [];

    $buildStream->onFrame(function ($frame) use (&$buildOutput): void {
      $stream = $frame->getStream();
      if ($stream === null) {
        return;
      }

      $stream = trim($stream);
      if ($stream === '') {
        return;
      }

      $buildOutput[] = $stream;
    });
    echo date('[H:i:s]'), '  -> Waiting for stream to be done', PHP_EOL;
    $buildStream->wait();
    echo date('[H:i:s]'), '  -> Done', PHP_EOL;

    // update status
    $status->file = $context->getDockerfileContent();
    $status->log = implode(PHP_EOL, $buildOutput);
    $status->build_time = (int)ceil($buildTime);

    $lastMessage = $buildOutput[count($buildOutput) - 1];
    if (preg_match('/^Successfully/', $lastMessage)) {
      echo date('[H:i:s]'), '  -> Pass!', PHP_EOL;
      $status->label = Status::PASS;

      continue;
    }

    echo date('[H:i:s]'), '  -> Fail :-(', PHP_EOL;

    $status->label = Status::FAIL;
    $job->failed = true;
  } catch (Exception $exception) {
    echo 'Exception caught!', PHP_EOL;
    echo $exception->getMessage(), PHP_EOL;

    if ($status !== null) {
      $status->label = Status::FAIL;
      $status->log = $exception->getMessage();
    }

    if ($job !== null) {
      $job->failed = true;
    }
  } finally {
    // save status
    if ($status !== null) {
      $status->save();
    }

    // update and save job
    if ($job !== null) {
      $job->assigned = false;
      $job->finished = true;
      $job->save();
    }
  }
} while (1);
