<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Utils\Config;
use Hoa\Math\Combinatorics\Combination\CartesianProduct;

$config = new Config(__DIR__ . '/../config');

header('Content-Type: application/json');
echo json_encode(
  [
    'ext' => $config->getExtensionList(),
    'ver' => $config->getVersionList(),
    'php' => $config->getPHPMatrix(),
    'url' => $config->getExtensionUrls()
  ]
);
