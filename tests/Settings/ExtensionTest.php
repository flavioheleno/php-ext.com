<?php
declare(strict_types = 1);

namespace App\Test\Settings;

use App\Settings\Extension;
use PHPUnit\Framework\TestCase;

final class ExtensionTest extends TestCase {
  public function testProperties(): void {
    $ext = Extension::fromArray(
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
          'min' => '5.6.0',
          'max' => '7.4.99'
        ],
        'summary' => 'Communicate with any AMQP compliant server'
      ]
    );

    $this->assertSame('amqp', $ext->getName());
    $this->assertFalse($ext->isDisabled());
    $this->assertTrue($ext->isPeclAvailable());
    $this->assertSame(
      'Communicate with any AMQP compliant server',
      $ext->getSummary()
    );
    $this->assertSame('5.6.0', $ext->getMinRequiredPHP());
    $this->assertSame('7.4.99', $ext->getMaxRequiredPHP());
    $this->assertFalse($ext->isZtsRequired());
    $this->assertSame(
      [
        'rabbitmq-c-dev'
      ],
      $ext->getBuildDependencies('alpine')
    );
    $this->assertSame(
      [
        'librabbitmq-dev'
      ],
      $ext->getBuildDependencies('buster')
    );
    $this->assertEmpty($ext->getBuildDependencies('unknown'));
    $this->assertSame('--with-amqp', $ext->getBuildFlag());
    $this->assertSame('git', $ext->getSourceType());
    $this->assertSame(
      'https://github.com/php-amqp/php-amqp',
      $ext->getSourceUrl()
    );
    $this->assertSame('', $ext->getBuildPath());
  }

  public function testDisabled(): void {
    $ext = Extension::fromArray(
      'amqp',
      [
        'disabled' => true
      ]
    );

    $this->assertTrue($ext->isDisabled());
  }

  public function testPeclUnavailable(): void {
    $ext = Extension::fromArray(
      'amqp',
      [
        'pecl' => false
      ]
    );

    $this->assertFalse($ext->isPeclAvailable());
  }

  public function testZtsRequired(): void {
    $ext = Extension::fromArray(
      'amqp',
      [
        'require' => [
          'zts' => true
        ]
      ]
    );

    $this->assertTrue($ext->isZtsRequired());
  }

  public function testInvalidBuildPath(): void {
    $ext = Extension::fromArray(
      'amqp',
      [
        'build' => [
          'path' => ".\0./.\0./etc/passwd"
        ]
      ]
    );

    $this->assertSame('etc/passwd', $ext->getBuildPath());
  }
}
