<?php
declare(strict_types = 1);

namespace App\Console\Maintenance;

use App\Models\Job;
use App\Models\Status;
use App\Utils\Tag;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Database\Capsule\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ClearDanglingCommand extends Command {
  protected static $defaultName = 'maintenance:clear-dangling';

  /**
   * Command configuration.
   *
   * @return void
   */
  protected function configure(): void {
    $this
      ->setDescription('Clear dangling (i.e. dead) build tasks');
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

      $lastUpdate = new DateTime();
      $lastUpdate->sub(new DateInterval('PT1H'));

      $jobs = Job::query()
        ->where('assigned', true)
        ->where('finished', false)
        ->where('updated_at', '<', $lastUpdate->format('U'))
        ->orderBy('created_at')
        ->get();

      $io->text(
        sprintf(
          '[%s] Cleaning <options=bold;fg=cyan>%d</> dangling jobs',
          date('H:i:s'),
          $jobs->count()
        )
      );
      $jobs->map(
        function ($job) use ($io) {
          $io->text(
            sprintf(
              '[%s]  -> <options=bold;fg=cyan>%s</> (stalled for %s)',
              date('H:i:s'),
              $job->payload['tag'],
              $job->updated_at->longAbsoluteDiffForHumans()
            )
          );

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
        }
      );
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
