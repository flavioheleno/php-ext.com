<?php
declare(strict_types = 1);

namespace App\Test\Utils;

use App\Settings\Extension;
use App\Settings\OperatingSystem;
use App\Utils\Dockerfile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DockerfileTest extends TestCase {
  private $os;
  private $gitExt;
  private $svnExt;

  public function setUp(): void {
    $this->os = OperatingSystem::fromArray(
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

    $this->gitExt = Extension::fromArray(
      'amqp',
      [
        'build' => [
          'deps' => [
            'alpine' => [
              'rabbitmq-c-dev'
            ],
            'buster' => [
              'librabbitmq-dev'
            ]
          ],
          'flag' => '--with-amqp',
          'type' => 'git',
          'url' => 'https://github.com/php-amqp/php-amqp'
        ],
        'require' => [
          'min' => '5.6.0'
        ],
        'summary' => 'Communicate with any AMQP compliant server'
      ]
    );

    $this->svnExt = Extension::fromArray(
      'svn',
      [
        'build' => [
          'deps' => [
            'alpine' => [
              'subversion-dev --repository=http://dl-cdn.alpinelinux.org/alpine/edge/main/',
              'git-svn --repository=http://dl-cdn.alpinelinux.org/alpine/edge/main/'
            ],
            'buster' => [
              'libsvn-dev',
              'git-svn'
            ]
          ],
          'flag' => '--with-svn',
          'type' => 'svn',
          'url' => 'http://svn.php.net/repository/pecl/svn'
        ],
        'require' => [
          'min' => '7.0.0'
        ],
        'summary' => 'PHP Bindings for the Subversion Revision control system'
      ]
    );
  }

  public function testBuildFromPecl(): void {
    $df = new Dockerfile($this->os);

    $this->assertSame(
      [
        [
          'apt update',
          'apt full-upgrade -y',
          'apt install -y --no-install-recommends git autoconf build-essential',
          'apt install -y --no-install-recommends librabbitmq-dev',
          'rm -rf /var/lib/apt/lists/*'
        ],
        [
          'pecl install --force amqp',
          'pecl run-tests amqp'
        ]
      ],
      $df->buildFromPecl($this->gitExt)
    );
  }

  public function testBuildFromSourceGit(): void {
    $df = new Dockerfile($this->os);

    $this->assertSame(
      [
        [
          'apt update',
          'apt full-upgrade -y',
          'apt install -y --no-install-recommends git autoconf build-essential',
          'apt install -y --no-install-recommends librabbitmq-dev',
          'rm -rf /var/lib/apt/lists/*'
        ],
        [
          'git clone --recursive --depth=1 https://github.com/php-amqp/php-amqp /tmp/ext-src',
          'cd /tmp/ext-src',
          'phpize',
          './configure --with-amqp',
          'make',
          'make test'
        ]
      ],
      $df->buildFromSource($this->gitExt)
    );
  }

  public function testBuildFromSourceGitWithCustomBuildPath(): void {
    $df = new Dockerfile($this->os);

    $ext = clone $this->gitExt;
    $ext->setBuildPath('my/custom/path');

    $this->assertSame(
      [
        [
          'apt update',
          'apt full-upgrade -y',
          'apt install -y --no-install-recommends git autoconf build-essential',
          'apt install -y --no-install-recommends librabbitmq-dev',
          'rm -rf /var/lib/apt/lists/*'
        ],
        [
          'git clone --recursive --depth=1 https://github.com/php-amqp/php-amqp /tmp/ext-src',
          'cd /tmp/ext-src/my/custom/path',
          'phpize',
          './configure --with-amqp',
          'make',
          'make test'
        ]
      ],
      $df->buildFromSource($ext)
    );
  }

  public function testBuildFromSourceSvn(): void {
    $df = new Dockerfile($this->os);

    $this->assertSame(
      [
        [
          'apt update',
          'apt full-upgrade -y',
          'apt install -y --no-install-recommends git autoconf build-essential',
          'apt install -y --no-install-recommends libsvn-dev git-svn',
          'rm -rf /var/lib/apt/lists/*'
        ],
        [
          'svn checkout --revision HEAD http://svn.php.net/repository/pecl/svn /tmp/ext-src',
          'cd /tmp/ext-src',
          'phpize',
          './configure --with-svn',
          'make',
          'make test'
        ]
      ],
      $df->buildFromSource($this->svnExt)
    );
  }

  public function testBuildFromSourceSvnWithCustomBuildPath(): void {
    $df = new Dockerfile($this->os);

    $ext = clone $this->svnExt;
    $ext->setBuildPath('my/custom/path');

    $this->assertSame(
      [
        [
          'apt update',
          'apt full-upgrade -y',
          'apt install -y --no-install-recommends git autoconf build-essential',
          'apt install -y --no-install-recommends libsvn-dev git-svn',
          'rm -rf /var/lib/apt/lists/*'
        ],
        [
          'svn checkout --revision HEAD http://svn.php.net/repository/pecl/svn /tmp/ext-src',
          'cd /tmp/ext-src/my/custom/path',
          'phpize',
          './configure --with-svn',
          'make',
          'make test'
        ]
      ],
      $df->buildFromSource($ext)
    );
  }

  public function testBuildFromSourceUnsupportedVcs(): void {
    $df = new Dockerfile($this->os);

    $ext = clone $this->gitExt;
    $ext->setSourceType('cvs');

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Cannot handle "cvs" source type.');
    $df->buildFromSource($ext);
  }
}
