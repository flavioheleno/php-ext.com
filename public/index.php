<?php
declare(strict_types = 1);

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'en_US.UTF8');

define('__ROOT__', realpath(__DIR__ . '/..'));

require_once __ROOT__ . '/vendor/autoload.php';

$config = new App\Utils\Config(__ROOT__ . '/config');

$mustache = new Mustache_Engine(
  [
    'loader' => new Mustache_Loader_FilesystemLoader(__ROOT__ . '/src/Views'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__ROOT__ . '/src/Views'),
    'logger' => new Mustache_Logger_StreamLogger('php://stderr')
  ]
);

$index = [
  'php-versions' => $config->getPhpList(),
  'os-versions'  => [],
  'os-count'     => count($config->getOsList()),
  'extension'    => []
];

foreach ($config->getPhpList() as $php) {
  $index['os-versions'] = array_merge(
    $index['os-versions'],
    $config->getOsList()
  );
}

foreach ($config->getExtensionList() as $extension) {
  $data = [
    'name' => $extension,
    'url'  => '',
    'pecl' => [],
    'dev'  => []
  ];
  foreach ($config->getPhpList() as $php) {
    foreach ($config->getOsList() as $os) {
      $data['pecl'][] = sprintf(
        '%s:pecl@%s-%s',
        $extension,
        $php,
        $os
      );

      $data['dev'][] = sprintf(
        '%s:dev@%s-%s',
        $extension,
        $php,
        $os
      );
    }
  }

  $index['extension'][] = $data;
}

$content = [
  'page' => [
    'title' => 'php-ext.com',
    'description' => 'A PHP Extension Compatibility Monitoring Portal',
    'styles' => implode(
      "\n",
      [
        '#tbl-header-php > th {',
        '  position: sticky;',
        '  top: 0;',
        '}',
        '#tbl-header-os > th {',
        '  position: sticky;',
        '  top: 33px;',
        '}'
      ]
    )
  ],
  'content' => $mustache->render('index', $index)
];

echo $mustache->render('template', $content);
