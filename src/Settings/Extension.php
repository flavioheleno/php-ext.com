<?php
declare(strict_types = 1);

namespace App\Settings;

final class Extension {
  private string $name;
  private string $summary = '';
  private bool $disabled = false;
  private bool $pecl = true;
  private string $minRequiredPHP = '';
  private string $maxRequiredPHP = '';
  private bool $requireZts = false;
  private array $buildDeps = [];
  private string $buildFlag = '';
  private string $sourceType = '';
  private string $sourceUrl = '';
  private string $buildPath = '';

  public static function fromArray(string $name, array $options = []): self {
    $ext = new Extension($name);
    if (isset($options['disabled']) === true && $options['disabled'] === true) {
      $ext->disable();
    }

    if (isset($options['pecl']) === true && $options['pecl'] === false) {
      $ext->disablePecl();
    }

    if (isset($options['require']['zts']) === true && $options['require']['zts'] === true) {
      $ext->requireZts();
    }

    $ext
      ->setSummary($options['summary'] ?? '')
      ->setMinRequiredPHP($options['require']['min'] ?? '')
      ->setMaxRequiredPHP($options['require']['max'] ?? '')
      ->setBuildFlag($options['build']['flag'] ?? '')
      ->setSourceType($options['build']['type'] ?? '')
      ->setSourceUrl($options['build']['url'] ?? '')
      ->setBuildPath($options['build']['path'] ?? '');

    foreach ($options['build']['deps'] ?? [] as $osName => $depList) {
      foreach ($depList as $dependency) {
        $ext->addBuildDependency($osName, $dependency);
      }
    }

    return $ext;
  }

  private function __construct(string $name) {
    $this->name = $name;
  }

  public function getName(): string {
    return $this->name;
  }

  public function disable(): void {
    $this->disabled = true;
  }

  public function isDisabled(): bool {
    return $this->disabled;
  }

  public function disablePecl(): void {
    $this->pecl = false;
  }

  public function isPeclAvailable(): bool {
    return $this->pecl;
  }

  public function setSummary(string $summary): self {
    $this->summary = $summary;

    return $this;
  }

  public function getSummary(): string {
    return $this->summary;
  }

  public function setMinRequiredPHP(string $version): self {
    $this->minRequiredPHP = $version;

    return $this;
  }

  public function getMinRequiredPHP(): string {
    return $this->minRequiredPHP;
  }

  public function setMaxRequiredPHP(string $version): self {
    $this->maxRequiredPHP = $version;

    return $this;
  }

  public function getMaxRequiredPHP(): string {
    return $this->maxRequiredPHP;
  }

  public function requireZts(): void {
    $this->requireZts = true;
  }

  public function isZtsRequired(): bool {
    return $this->requireZts;
  }

  public function addBuildDependency(string $osName, string $dependency): self {
    $this->buildDeps[$osName][] = $dependency;

    return $this;
  }

  public function getBuildDependencies(string $osName): array {
    return $this->buildDeps[$osName] ?? [];
  }

  public function setBuildFlag(string $flag): self {
    $this->buildFlag = $flag;

    return $this;
  }

  public function getBuildFlag(): string {
    return $this->buildFlag;
  }

  public function setSourceType(string $type): self {
    $this->sourceType = $type;

    return $this;
  }

  public function getSourceType(): string {
    return $this->sourceType;
  }

  public function setSourceUrl(string $url): self {
    $this->sourceUrl = $url;

    return $this;
  }

  public function getSourceUrl(): string {
    return $this->sourceUrl;
  }

  public function setBuildPath(string $path): self {
    $path = preg_replace('/[^a-z0-9_\/\. -]/', '', $path);
    $path = preg_replace('/(\.{1,2}\/)+/', '', $path);
    $this->buildPath = $path;

    return $this;
  }

  public function getBuildPath(): string {
    return $this->buildPath;
  }
}
