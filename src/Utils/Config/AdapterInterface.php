<?php
declare(strict_types = 1);

namespace App\Utils\Config;

interface AdapterInterface {
  public function getExtensionSpecs(string $extName = null): array;
  public function getExtensionList(): array;
  public function getExtensionUrls(): array;
  public function getOsSpecs(string $osName = null): array;
  public function getOsList(): array;
  public function getPhpList(): array;
  public function getVersionList(): array;
  public function getExtensionMatrix(): array;
  public function getPhpMatrix(): array;
  public function getBuildMatrix(): array;
}