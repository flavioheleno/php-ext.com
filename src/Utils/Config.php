<?php
declare(strict_types = 1);

namespace App\Utils;

use App\Utils\Config\AdapterInterface;

use \ReflectionClass;
use \ReflectionMethod;

final class Config {
  private $adapter;

  public function __construct(AdapterInterface $adapter) {
    $this->adapter = $adapter;
    $this->methods = $this->getAdapterPublicMethods();
  }

  public function __call(string $method, array $arguments): array {
    $argument = $this->getArgument($arguments);

    if (in_array($method, $this->methods)) {
      return $this->adapter->$method($argument);
    }
  }
  
  private function getAdapterPublicMethods(): array {
    $interface = new ReflectionClass('App\Utils\Config\AdapterInterface');
    $reflection = $interface->getMethods(ReflectionMethod::IS_PUBLIC);

    return array_map(function ($item) {
      return $item->name;
    }, $reflection);
  }

  private function getArgument($arguments): ?string {
    if (empty($arguments)) {
      return null;
    }

    return array_shift($arguments);
  }
}
