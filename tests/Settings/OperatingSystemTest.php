<?php
declare(strict_types = 1);

namespace App\Test\Settings;

use App\Settings\OperatingSystem;
use PHPUnit\Framework\TestCase;

final class OperatingSystemTest extends TestCase {
  public function testProperties(): void {
    $os = OperatingSystem::fromArray(
      'buster',
      [
        'deps' => [
          'cmd' => 'apt install -y --no-install-recommends',
          'list' => [
            'git',
            'autoconf',
            'build-essential'
          ]
        ],
        'pre' => [
          'apt update',
          'apt full-upgrade -y'
        ],
        'post' => [
          'rm -rf /var/lib/apt/lists/*'
        ]
      ]
    );

    $this->assertSame('buster', $os->getName());
    $this->assertFalse($os->isDisabled());
    $this->assertSame(
      [
        'apt update',
        'apt full-upgrade -y',
      ],
      $os->getPreBuild()
    );
    $this->assertSame(
      [
        'rm -rf /var/lib/apt/lists/*'
      ],
      $os->getPostDepInstall()
    );
    $this->assertSame(
      'apt install -y --no-install-recommends',
      $os->getDependencyInstallCommand()
    );
    $this->assertSame(
      [
        'git',
        'autoconf',
        'build-essential'
      ],
      $os->getDependencies()
    );
  }

  public function testDisabled(): void {
    $os = OperatingSystem::fromArray(
      'buster',
      [
        'disabled' => true
      ]
    );

    $this->assertTrue($os->isDisabled());
  }
}
