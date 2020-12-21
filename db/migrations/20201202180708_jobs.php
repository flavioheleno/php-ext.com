<?php
declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class Jobs extends AbstractMigration {
  public function change(): void {
    $jobs = $this->table('jobs');
    $jobs
      ->addColumn('function', 'text', ['null' => false])
      ->addColumn('payload', 'text', ['null' => false])
      ->addColumn('assigned', 'boolean', ['null' => false, 'default' => false])
      ->addColumn('finished', 'boolean', ['null' => false, 'default' => false])
      ->addColumn('failed', 'boolean', ['null' => false, 'default' => false])
      ->addTimestampsWithTimezone()
      ->addIndex('function')
      ->addIndex('assigned')
      ->addIndex('finished')
      ->addIndex('failed')
      ->create();
  }
}
