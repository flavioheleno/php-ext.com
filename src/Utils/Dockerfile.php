<?php
declare(strict_types = 1);

namespace App\Utils;

use App\Settings\Extension;
use App\Settings\OperatingSystem;
use RuntimeException;

final class Dockerfile {
  private const BASE_DIR = '/tmp/ext-src';

  private OperatingSystem $os;

  private function preMake(Extension $extension): array {
    // build pre-make commands
    $content = $this->os->getPreBuild();
    if (count($this->os->getDependencies())) {
      $content[] = sprintf(
        '%s %s',
        $this->os->getDependencyInstallCommand(),
        implode(' ', $this->os->getDependencies())
      );
    }

    $osName = $this->os->getName();
    if (count($extension->getBuildDependencies($osName))) {
      $content[] = sprintf(
        '%s %s',
        $this->os->getDependencyInstallCommand(),
        implode(' ', $extension->getBuildDependencies($osName))
      );
    }

    // return $content;
    return array_merge(
      $content,
      $this->os->getPostDepInstall()
    );
  }

  private function cloneSource(Extension $extension): string {
    switch ($extension->getSourceType()) {
      case 'git':
        return sprintf('git clone --recursive --depth=1 %s %s', $extension->getSourceUrl(), self::BASE_DIR);
      case 'svn':
        return sprintf('svn checkout --revision HEAD %s %s', $extension->getSourceUrl(), self::BASE_DIR);
      default:
        throw new RuntimeException(sprintf('Cannot handle "%s" source type.', $extension->getSourceType()));
    }
  }

  public function __construct(OperatingSystem $os) {
    $this->os = $os;
  }

  public function buildFromPecl(Extension $extension): array {
    return [
      $this->preMake($extension),
      [
        sprintf('pecl install --force %s', $extension->getName()),
        sprintf('pecl run-tests %s', $extension->getName())
      ]
    ];
  }

  public function buildFromSource(Extension $extension): array {
    return [
      $this->preMake($extension),
      [
        $this->cloneSource($extension),
        sprintf(
          'cd %s',
          rtrim(
            self::BASE_DIR . '/' . ltrim($extension->getBuildPath(), '/'),
            '/'
          )
        ),
        'phpize',
        sprintf('./configure %s', $extension->getBuildFlag()),
        'make',
        'make test'
      ]
    ];
  }
}
