<?php
declare(strict_types = 1);

namespace App\Settings;

final class OperatingSystem {
  private string $name;
  private bool $disabled = false;
  private array $preBuild = [];
  private array $postDepInstall = [];
  private string $depInstallCommand = '';
  private array $depsList = [];

  public static function fromArray(string $name, array $options = []): self {
    $os = new OperatingSystem($name);
    if (isset($options['disabled']) === true && $options['disabled'] === true) {
      $os->disable();
    }

    foreach ($options['pre'] ?? [] as $command) {
      $os->addPreBuildCommand($command);
    }

    foreach ($options['post'] ?? [] as $command) {
      $os->addPostDepInstallCommand($command);
    }

    $os->setDependencyInstallCommand($options['deps']['cmd'] ?? '');
    foreach ($options['deps']['list'] ?? [] as $dependency) {
      $os->addDependency($dependency);
    }

    return $os;
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

  public function addPreBuildCommand(string $command): self {
    $this->preBuild[] = $command;

    return $this;
  }

  public function getPreBuild(): array {
    return $this->preBuild;
  }

  public function addPostDepInstallCommand(string $command): self {
    $this->postDepInstall[] = $command;

    return $this;
  }

  public function getPostDepInstall(): array {
    return $this->postDepInstall;
  }

  public function setDependencyInstallCommand(string $command): self {
    $this->depInstallCommand = $command;

    return $this;
  }

  public function getDependencyInstallCommand(): string {
    return $this->depInstallCommand;
  }

  public function addDependency(string $dependency): self {
    $this->depsList[] = $dependency;

    return $this;
  }

  public function getDependencies(): array {
    return $this->depsList;
  }
}
