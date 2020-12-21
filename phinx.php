<?php

return [
  'paths' => [
    'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
    'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
  ],
  'environments' => [
    'default_migration_table' => 'phinxlog',
    'default_environment' => 'development',
    'production' => [
      'adapter' => 'sqlite',
      'name'    => './data/php-ext'
    ],
    'development' => [
      'adapter' => 'sqlite',
      'name'    => './data/php-ext'
    ],
    'testing' => [
      'adapter' => 'sqlite',
      'memory'  => true
    ]
  ],
  'version_order' => 'creation'
];
