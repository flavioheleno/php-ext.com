<?php
declare(strict_types = 1);

namespace App\Console\Build;

use App\Models\Job;
use App\Models\Status;
use App\Settings\Extension;
use App\Settings\OperatingSystem;
use App\Utils\Config;
use App\Utils\Dockerfile;
use Docker\Context\ContextBuilder;
use Docker\Docker;
use Exception;
use Illuminate\Database\Capsule\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class BuilderCommand extends Command {
  protected static $defaultName = 'build:builder';

  /**
   * Command configuration.
   *
   * @return void
   */
  protected function configure(): void {
    $this
      ->setDescription('Execute a build task from the job queue')
      ->addOption(
        'sleep',
        's',
        InputOption::VALUE_REQUIRED,
        'Number of seconds to sleep between checks for new tasks (requires --live)',
        60
      )
      ->addOption(
        'limit',
        'l',
        InputOption::VALUE_REQUIRED,
        'Maximum number of tasks to execute',
        -1
      )
      ->addOption(
        'live',
        null,
        InputOption::VALUE_NONE,
        'Keep monitoring queue for new tasks'
      );
  }

  /**
   * Command execution.
   *
   * @param \Symfony\Component\Console\Input\InputInterface   $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int|null
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    try {
      // i/o styling
      $io = new SymfonyStyle($input, $output);
      $io->text(
        sprintf(
          '[%s] Started with pid <options=bold;fg=cyan>%d</>',
          date('H:i:s'),
          posix_getpid()
        )
      );

      $workLimit = (int)$input->getOption('limit');
      $workLoop  = (bool)$input->getOption('live');
      $sleepSecs = (int)$input->getOption('sleep');

      if ($output->isDebug()) {
        $io->text('<options=bold>Configuration:</>');
        $io->listing(
          [
            sprintf('Number of tasks to execute: <options=bold;fg=cyan>%d</>', $workLimit),
            sprintf('Keep monitoring for new tasks: <options=bold;fg=cyan>%s</>', $workLoop ? 'Yes' : 'No')
          ]
        );
      }

      $manager = new Manager();
      $manager->addConnection(
        [
          'driver'   => 'sqlite',
          'database' => __ROOT__ . '/data/php-ext.sqlite3'
        ],
        'default'
      );

      $manager->bootEloquent();
      $manager->setAsGlobal();

      do {
        try {
          $job = Job::query()
            ->where('function', 'build')
            ->where('assigned', false)
            ->where('finished', false)
            ->orderBy('created_at')
            ->first();

          if ($job === null) {
            $io->text(
              sprintf(
                '[%s] No available jobs',
                date('H:i:s')
              )
            );

            if ($workLoop) {
              sleep($sleepSecs);

              continue;
            }

            return Command::SUCCESS;
          }

          $job->assigned = true;
          $job->save();

          // decrease the task counter
          if ($workLimit > 0) {
            $workLimit--;
          }

          $status = Status::where('id', $job->payload['tag'])->first();
          if ($status === null) {
            $status = Status::create(
              [
                'id'    => $job->payload['tag'],
                'label' => Status::BUILD
              ]
            );
          }

          $config = new Config(__ROOT__ . '/config');

          $extSpecs = $config->getExtensionSpecs();
          if (isset($extSpecs[$job->payload['ext']]) === false) {
            throw new RuntimeException(
              sprintf(
                'Specs not found for extension "%s"',
                $job->payload['ext']
              )
            );
          }

          $extension = Extension::fromArray($job->payload['ext'], $extSpecs[$job->payload['ext']]);

          $osSpecs = $config->getOSSpecs();
          if (isset($osSpecs[$job->payload['os']]) === false) {
            throw new RuntimeException(
              sprintf(
                'Specs not found for Operating System "%s"',
                $job->payload['os']
              )
            );
          }

          $os = OperatingSystem::fromArray($job->payload['os'], $osSpecs[$job->payload['os']]);

          $io->text(
            sprintf(
              '[%s] Building task <options=bold;fg=cyan>%d</> (<options=bold;fg=cyan>%s</>)',
              date('H:i:s'),
              $job->id,
              $job->payload['tag']
            )
          );

          if ($extension->isPeclAvailable() === false && $job->payload['ver'] === 'pecl') {
            $message = 'This extension is not available via PECL.';
            $io->text(
              sprintf(
                '[%s] Skip: %s',
                date('H:i:s'),
                $message
              )
            );

            $status->label = Status::SKIP;
            $status->log = $message;
            $status->save();

            $job->assigned = false;
            $job->finished = true;
            $job->save();

            continue;
          }

          if ($extension->isZtsRequired() && str_ends_with($job->payload['php'], '-zts') === false) {
            $message = 'This extension requires a thread-safe version of PHP.';
            $io->text(
              sprintf(
                '[%s] Skip: %s',
                date('H:i:s'),
                $message
              )
            );

            $status->label = Status::SKIP;
            $status->log = $message;
            $status->save();

            $job->assigned = false;
            $job->finished = true;
            $job->save();

            continue;
          }

          if ($extension->getMinRequiredPHP() !== '' && version_compare(str_replace('-zts', '', $job->payload['php']), $extension->getMinRequiredPHP(), '<')) {
            $message = sprintf('This extension requires PHP %s or later.', $extension->getMinRequiredPHP());
            $io->text(
              sprintf(
                '[%s] Skip: %s',
                date('H:i:s'),
                $message
              )
            );

            $status->label = Status::SKIP;
            $status->log = $message;
            $status->save();

            $job->assigned = false;
            $job->finished = true;
            $job->save();

            continue;
          }

          if (
            $extension->getMaxRequiredPHP() !== '' &&
            version_compare(str_replace('-zts', '', $job->payload['php']), $extension->getMaxRequiredPHP(), '<')
          ) {
            $message = sprintf('This extension requires PHP %s or older.', $extension->getMaxRequiredPHP());
            $io->text(
              sprintf(
                '[%s] Skip: %s',
                date('H:i:s'),
                $message
              )
            );

            $status->label = Status::SKIP;
            $status->log = $message;
            $status->save();

            $job->assigned = false;
            $job->finished = true;
            $job->save();

            continue;
          }

          $docker = Docker::create();
          $versionTag = sprintf('%s-%s', $job->payload['php'], $job->payload['os']);

          $io->text(
            sprintf(
              '[%s]  > Creating context',
              date('H:i:s')
            )
          );
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
          $io->text(
            sprintf(
              '[%s]  > Done',
              date('H:i:s')
            )
          );

          $io->text(
            sprintf(
              '[%s]  > Building image',
              date('H:i:s')
            )
          );
          $buildTime = microtime(true);
          $buildStream = $docker->imageBuild($context->read());
          $buildTime = microtime(true) - $buildTime;
          $io->text(
            sprintf(
              '[%s]  > Done',
              date('H:i:s')
            )
          );

          $buildOutput = [];
          $containerId = null;
          $imageId = null;

          $buildStream->onFrame(
            function ($frame) use (&$buildOutput, &$containerId, &$imageId): void {
              $stream = $frame->getStream();
              if ($stream === null) {
                return;
              }

              $stream = trim($stream);
              if ($stream === '') {
                return;
              }

              $matches = [];
              if (preg_match('/^---> Running in (?<id>[a-f0-9]{12})$/', $stream, $matches)) {
                $containerId = $matches['id'];
              }

              if (preg_match('/^---> (?<id>[a-f0-9]{12})$/', $stream, $matches)) {
                $imageId = $matches['id'];
              }

              if (preg_match('/^Successfully built (?<id>[a-f0-9]{12})$/', $stream, $matches)) {
                $containerId = null;
                $imageId = $matches['id'];
              }

              $buildOutput[] = $stream;
            }
          );
          $io->text(
            sprintf(
              '[%s]  > Waiting for stream to be done',
              date('H:i:s')
            )
          );
          $buildStream->wait();
          $io->text(
            sprintf(
              '[%s]  > Done',
              date('H:i:s')
            )
          );

          if ($containerId !== null) {
            $io->text(
              sprintf(
                '[%s]  > Removing container <options=bold;fg=cyan>%s</>',
                date('H:i:s'),
                $containerId
              )
            );
            try {
              $docker->containerDelete($containerId, ['force' => true]);
              $io->text(
                sprintf(
                  '[%s]  > Done',
                  date('H:i:s')
                )
              );
            } catch (Exception $exception) {
              $io->text(
                sprintf(
                  '[%s]  > Failed',
                  date('H:i:s')
                )
              );
            }
          }

          if ($imageId !== null) {
            $io->text(
              sprintf(
                '[%s]  > Removing image <options=bold;fg=cyan>%s</>',
                date('H:i:s'),
                $imageId
              )
            );
            try {
              $docker->imageDelete($imageId, ['force' => true]);
              $io->text(
                sprintf(
                  '[%s]  > Done',
                  date('H:i:s')
                )
              );
            } catch (Exception $exception) {
              $io->text(
                sprintf(
                  '[%s]  > Failed',
                  date('H:i:s')
                )
              );
            }
          }

          // update status
          $status->file = $context->getDockerfileContent();
          $status->log = implode(PHP_EOL, $buildOutput);
          $status->build_time = (int)ceil($buildTime);

          $lastMessage = $buildOutput[count($buildOutput) - 1];
          if (preg_match('/^Successfully built/', $lastMessage)) {
            $io->text(
              sprintf(
                '[%s] Status: <options=bold;fg=green>PASS</>',
                date('H:i:s')
              )
            );
            $status->label = Status::PASS;

            continue;
          }

          $io->text(
            sprintf(
              '[%s] Status: <options=bold;fg=red>FAIL</>',
              date('H:i:s')
            )
          );

          $status->label = Status::FAIL;
          $job->failed = true;
        } catch (Exception $exception) {
          $io->error(
            sprintf(
              '[%s] Exception caught!',
              date('H:i:s')
            )
          );
          $io->error(
            sprintf(
              '[%s] %s',
              date('H:i:s'),
              $exception->getMessage()
            )
          );

          if (isset($status)) {
            $status->label = Status::FAIL;
            $status->log = $exception->getMessage();
          }

          if (isset($job)) {
            $job->failed = true;
          }
        } finally {
          // save status
          if (isset($status)) {
            $status->save();
          }

          // update and save job
          if (isset($job)) {
            $job->assigned = false;
            $job->finished = true;
            $job->save();
          }
        }
      } while ($workLoop && $workLimit !== 0);
    } catch (Exception $exception) {
      if (isset($io) === true) {
        $io->error(
          sprintf(
            '[%s] %s',
            date('H:i:s'),
            $exception->getMessage()
          )
        );
        if ($output->isDebug()) {
          $io->listing(explode(PHP_EOL, $exception->getTraceAsString()));
        }
      }

      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }
}
