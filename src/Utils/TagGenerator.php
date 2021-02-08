<?php
declare(strict_types = 1);

namespace App\Utils;

use App\Utils\Config;
use App\Utils\Tag;
use Generator;
use InvalidArgumentException;

final class TagGenerator {
  private const REGEX = '/^(?<ext>[a-z0-9_-]+|\*):(?<ver>pecl|dev|\*)@(?<php>[0-9]+\.[0-9]+\.[0-9]+(\-zts)?|\*)-(?<os>[a-z]+|\*)$/';
  public const ANY = '*';

  private string $extName = self::ANY;
  private string $version = self::ANY;
  private string $phpVersion = self::ANY;
  private string $osName = self::ANY;

  private Config $config;

  public static function fromString(Config $config, string $tag): self {
    // accept "*" as a tag (translates to "*:*@*-*")
    if ($tag === self::ANY) {
      return new self($config, self::ANY, self::ANY, self::ANY, self::ANY);
    }

    // translate tags beginning with "*@" to "*:*@"
    $tag = preg_replace('/^\*@/', '*:*@', $tag);
    // translate tags ending with "@*" to "@*-*"
    $tag = preg_replace('/@\*$/', '@*-*', $tag);

    if (preg_match(self::REGEX, $tag, $matches) !== 1) {
      throw new InvalidArgumentException('Tag does not match required format!');
    }

    return new self($config, $matches['ext'], $matches['ver'], $matches['php'], $matches['os']);
  }

  public function __construct(
    Config $config,
    string $extName,
    string $version,
    string $phpVersion,
    string $osName
  ) {
    $this->config = $config;

    $this->setExtName($extName);
    $this->setVersion($version);
    $this->setPhpVersion($phpVersion);
    $this->setOsName($osName);
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

  public function generate(): Generator {
    $extList = [$this->extName];
    if ($this->extName === self::ANY) {
      $extList = $this->config->getExtensionList();
    }

    $versionList = [$this->version];
    if ($this->version === self::ANY) {
      $versionList = $this->config->getVersionList();
    }

    $phpList = [$this->phpVersion];
    if ($this->phpVersion === self::ANY) {
      $phpList = $this->config->getPHPList();
    }

    $osList = [$this->osName];
    if ($this->osName === self::ANY) {
      $osList = $this->config->getOSList();
    }

    foreach ($extList as $extName) {
      foreach ($versionList as $version) {
        foreach ($phpList as $phpVersion) {
          foreach ($osList as $osName) {
            yield Tag::fromString(
              sprintf(
                '%s:%s@%s-%s',
                $extName,
                $version,
                $phpVersion,
                $osName
              )
            );
          }
        }
      }
    }
  }
}
