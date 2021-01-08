<?php
declare(strict_types = 1);

namespace App\Test\Utils\Config\Adapters;

use App\Utils\Config\Adapters\JsonAdapter;
use PHPUnit\Framework\TestCase;
use BenTools\CartesianProduct\CartesianProduct;

final class JsonAdapterTest extends TestCase {
  private $adapter;
  private $extensions;
  private $operatingSystems;
  private $phpVersions;
  
  public function setUp(): void {
    $configPath = __DIR__ . '/../../../fixtures/config';
    $this->adapter = new JsonAdapter($configPath);

    $jsonConfig = file_get_contents($configPath . DIRECTORY_SEPARATOR . 'extensions.json');
    $this->extensions = json_decode($jsonConfig, true);

    $jsonConfig = file_get_contents($configPath . DIRECTORY_SEPARATOR . 'operating-systems.json');
    $this->operatingSystems = json_decode($jsonConfig, true);

    $jsonConfig = file_get_contents($configPath . DIRECTORY_SEPARATOR . 'php-versions.json');
    $this->phpVersions = json_decode($jsonConfig, true);
  }

  public function testSingleExtensionSpecs(): void {
    $this->assertEquals(
      $this->adapter->getExtensionSpecs('amqp'),
      $this->extensions['amqp']
    );
  }

  public function testAllExtensionSpecs(): void {
    $this->assertEquals(
      $this->adapter->getExtensionSpecs(),
      $this->extensions
    );
  }

  public function testExtensionList(): void {
    $expected = ['ahocorasick', 'amqp'];

    $this->assertEquals(
      $this->adapter->getExtensionList(),
      $expected
    );
  }

  public function testExtensionUrls(): void {
    $expected = [
      'ahocorasick' => 'https://github.com/ph4r05/php_aho_corasick',
      'amqp' => 'https://github.com/php-amqp/php-amqp'
    ];

    $this->assertEquals(
      $this->adapter->getExtensionUrls(),
      $expected
    );
  }

  public function testSingleOSSpecs(): void {
    $this->assertEquals(
      $this->adapter->getOSSpecs('alpine'),
      $this->operatingSystems['alpine']
    );
  }

  public function testOSSpecs(): void {
    $expected = [
      'buster' => $this->operatingSystems['buster']
    ];

    $this->assertEquals(
      $this->adapter->getOSSpecs(),
      $expected
    );
  }

  public function testOSList(): void {
    $expected = ['buster'];

    $this->assertEquals(
      $this->adapter->getOSList(),
      $expected
    );
  }

  public function testPHPList(): void {
    $expected = array_map(
      function (array $items): string {
        return implode('-', array_filter($items));
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->phpVersions,
            ['', 'zts']
          ]
        )
      )
    );
    sort($expected);

    $this->assertEquals(
      $this->adapter->getPHPList(),
      $expected
    );
  }

  public function testVersionList(): void {
    $expected = [
      'pecl',
      'dev'
    ];

    $this->assertEquals(
      $this->adapter->getVersionList(),
      $expected
    );
  }

  public function testExtensionMatrix(): void {
    $expected = array_map(
      function (array $items): string {
        return implode(':', $items);
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->adapter->getExtensionList(),
            $this->adapter->getVersionList()
          ]
        )
      )
    );
    sort($expected);

    $this->assertEquals(
      $this->adapter->getExtensionMatrix(),
      $expected
    );
  }

  public function testPHPMatrix(): void {
    $expected = array_map(
      function (array $items): string {
        return implode('-', $items);
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->adapter->getPHPList(),
            $this->adapter->getOSList()
          ]
        )
      )
    );
    sort($expected);

    $this->assertEquals(
      $this->adapter->getPHPMatrix(),
      $expected
    );
  }

  public function testBuildMatrix(): void {
    $expected = array_map(
      function (array $items): string {
        return implode('@', $items);
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->adapter->getExtensionMatrix(),
            $this->adapter->getPHPMatrix()
          ]
        )
      )
    );
    sort($expected);

    $this->assertEquals(
      $this->adapter->getBuildMatrix(),
      $expected
    );
  }
}
