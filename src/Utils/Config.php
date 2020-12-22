<?php
declare(strict_types = 1);

namespace App\Utils;

use BenTools\CartesianProduct\CartesianProduct;

use InvalidArgumentException;

final class Config {
  private $basePath;
  private $loadedContent = [];

  private function loadJson(string $fileName): void {
    if (isset($this->loadedContent[$fileName]) === true) {
      return;
    }

    $filePath = $this->basePath . DIRECTORY_SEPARATOR . $fileName;
    if (! is_file($filePath)) {
      throw new InvalidArgumentException('$fileName must be a valid file');
    }

    $raw = file_get_contents($filePath);
    if ($raw === false) {
      throw new InvalidArgumentException('Could not read from file');
    }

    $json = json_decode($raw, true);
    if ($json === false) {
      throw new InvalidArgumentException('Could not decode json data');
    }

    $this->loadedContent[$fileName] = $json;
  }

  public function __construct(string $basePath) {
    $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    if (! is_dir($basePath)) {
      throw new InvalidArgumentException('$basePath must be a valid directory');
    }

    $this->basePath = $basePath;
  }

  public function getExtensionSpecs(string $extName = null): array {
    $this->loadJson('extensions.json');
    if ($extName !== null) {
      return $this->loadedContent['extensions.json'][$extName] ?? [];
    }

    $extensions = array_filter(
      $this->loadedContent['extensions.json'],
      function (array $specs): bool {
        return (isset($specs['disabled']) === false || $specs['disabled'] === false);
      },
      ARRAY_FILTER_USE_BOTH
    );
    ksort($extensions);

    return $extensions;
  }

  public function getExtensionList(): array {
    return array_keys($this->getExtensionSpecs());
  }

  public function getExtensionUrls(): array {
    return array_map(
      function (array $items): string {
        return $items['build']['url'] ?? '';
      },
      $this->getExtensionSpecs()
    );
  }

  public function getOSSpecs(string $osName = null): array {
    $this->loadJson('operating-systems.json');
    if ($osName !== null) {
      return $this->loadedContent['operating-systems.json'][$osName] ?? [];
    }

    $os = array_filter(
      $this->loadedContent['operating-systems.json'],
      function (array $specs): bool {
        return (isset($specs['disabled']) === false || $specs['disabled'] === false);
      },
      ARRAY_FILTER_USE_BOTH
    );
    ksort($os);

    return $os;
  }

  public function getOSList(): array {
    return array_keys($this->getOSSpecs());
  }

  public function getPHPList(): array {
    $this->loadJson('php-versions.json');
    $php = array_map(
      function (array $items): string {
        return implode('-', array_filter($items));
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->loadedContent['php-versions.json'],
            ['', 'zts']
          ]
        )
      )
    );
    sort($php);

    return $php;
  }

  public function getVersionList(): array {
    return ['pecl', 'dev'];
  }

  public function getExtensionMatrix(): array {
    $ext = array_map(
      function (array $items): string {
        return implode(':', $items);
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->getExtensionList(),
            $this->getVersionList()
          ]
        )
      )
    );
    sort($ext);

    return $ext;
  }

  public function getPHPMatrix(): array {
    $php = array_map(
      function (array $items): string {
        return implode('-', $items);
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->getPHPList(),
            $this->getOSList()
          ]
        )
      )
    );

    sort($php);

    return $php;
  }

  public function getBuildMatrix(): array {
    return array_map(
      function (array $items): string {
        return implode('@', $items);
      },
      iterator_to_array(
        new CartesianProduct(
          [
            $this->getExtensionMatrix(),
            $this->getPHPMatrix()
          ]
        )
      )
    );
  }
}
