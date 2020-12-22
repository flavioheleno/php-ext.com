<?php
declare(strict_types = 1);

namespace App\Utils;

use InvalidArgumentException;

final class Tag {
  private const REGEX = '/^(?<ext>[a-z0-9_-]+):(?<ver>pecl|dev)@(?<php>[0-9]+\.[0-9]+\.[0-9]+(\-zts)?)-(?<os>[a-z]+)$/';

  private string $extName;
  private string $version;
  private string $phpVersion;
  private string $osName;
  private bool $isZts = false;

  public static function fromString(string $tag): self {
    if (preg_match(self::REGEX, $tag, $matches) !== 1) {
      throw new InvalidArgumentException('Tag does not match required format!');
    }

    return new self($matches['ext'], $matches['ver'], $matches['php'], $matches['os']);
  }

  private function __construct(string $extName, string $version, string $phpVersion, string $osName) {
    $this->setExtName($extName);
    $this->setVersion($version);
    $this->setPhpVersion($phpVersion);
    $this->setOsName($osName);

    if (substr($phpVersion, -4) === '-zts') {
      $this->setZts();
    }
  }

  public function setExtName(string $extName): self {
    $this->extName = $extName;

    return $this;
  }

  public function getExtName(): string {
    return $this->extName;
  }

  public function setVersion(string $version): self {
    $this->version = $version;

    return $this;
  }

  public function getVersion(): string {
    return $this->version;
  }

  public function setPhpVersion(string $phpVersion): self {
    $this->phpVersion = $phpVersion;

    return $this;
  }

  public function getPhpVersion(): string {
    return $this->phpVersion;
  }

  public function setOsName(string $osName): self {
    $this->osName = $osName;

    return $this;
  }

  public function getOsName(): string {
    return $this->osName;
  }

  public function setZts(): self {
    $this->isZts = true;

    return $this;
  }

  public function isZts(): bool {
    return $this->isZts;
  }

  public function __toString(): string {
    return sprintf(
      '%s:%s@%s-%s',
      $this->extName,
      $this->version,
      $this->phpVersion,
      $this->osName
    );
  }
}
