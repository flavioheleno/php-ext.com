<?php
declare(strict_types = 1);

namespace App\Console\Build;

use App\Models\Job;
use App\Utils\Config;
use App\Utils\TagGenerator;
use Exception;
use Illuminate\Database\Capsule\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AddCommand extends Command {
  protected static $defaultName = 'build:add';

  /**
   * Command configuration.
   *
   * @return void
   */
  protected function configure(): void {
    $this
      ->setDescription('Add a new build task to the job queue')
      ->addArgument(
        'tag',
        InputArgument::REQUIRED,
        'A build tag (e.g. amqp:dev@7.3.25-buster)'
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

      $config = new Config(__ROOT__ . '/config');

      $generator = TagGenerator::fromString($config, $input->getArgument('tag'));
      foreach ($generator->generate() as $tag) {
        if (in_array($tag->getExtName(), $config->getExtensionList()) === false) {
          $io->text(
            sprintf(
              '[%s]  Error: Unsupported extension <options=bold;fg=cyan>%s</>!',
              date('H:i:s'),
              $tag->getExtName()
            )
          );

          return Command::FAILURE;
        }

        $io->text(
          sprintf(
            '[%s] Creating build task',
            date('H:i:s')
          )
        );
        $io->text(
          sprintf(
            '[%s]  > Extension: <options=bold;fg=cyan>%s</>',
            date('H:i:s'),
            $tag->getExtName()
          )
        );
        $io->text(
          sprintf(
            '[%s]  > Version: <options=bold;fg=cyan>%s</>',
            date('H:i:s'),
            $tag->getVersion()
          )
        );
        $io->text(
          sprintf(
            '[%s]  > PHP Version: <options=bold;fg=cyan>%s</>',
            date('H:i:s'),
            $tag->getPhpVersion()
          )
        );
        $io->text(
          sprintf(
            '[%s]  > Operating System: <options=bold;fg=cyan>%s</>',
            date('H:i:s'),
            $tag->getOsName()
          )
        );
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

        $io->text(
          sprintf(
            '[%s] Task created with id <options=bold;fg=cyan>%d</>',
            date('H:i:s'),
            $job->id
          )
        );
      }
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
