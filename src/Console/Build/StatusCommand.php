<?php
declare(strict_types = 1);

namespace App\Console\Build;

use App\Models\Job;
use Exception;
use Illuminate\Database\Capsule\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class StatusCommand extends Command {
  protected static $defaultName = 'build:status';

  /**
   * Command configuration.
   *
   * @return void
   */
  protected function configure(): void {
    $this
      ->setDescription('Return the build task job queue status');
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

      $jobs = Job::query()
        ->where('assigned', true)
        ->count();
      $io->text(
        sprintf(
          '[%s] Assigned: <options=bold;fg=cyan>%d</> jobs',
          date('H:i:s'),
          $jobs
        )
      );

      $jobs = Job::query()
        ->where('finished', true)
        ->count();
      $io->text(
        sprintf(
          '[%s] Finished: <options=bold;fg=cyan>%d</> jobs',
          date('H:i:s'),
          $jobs
        )
      );

      $jobs = Job::query()
        ->where('failed', true)
        ->count();
      $io->text(
        sprintf(
          '[%s] Failed: <options=bold;fg=cyan>%d</> jobs',
          date('H:i:s'),
          $jobs
        )
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
