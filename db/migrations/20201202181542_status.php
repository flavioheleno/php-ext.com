<?php
declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class Status extends AbstractMigration {
  public function change(): void {
    $statuses = $this->table('statuses', ['id' => false, 'primary_key' => 'id']);
    $statuses
      ->addColumn('id', 'text', ['null' => false])
      ->addColumn('label', 'text', ['null' => false])
      ->addColumn('file', 'text', ['null' => true])
      ->addColumn('log', 'text', ['null' => true])
      ->addColumn('build_time', 'integer', ['null' => false, 'default' => 0])
      ->addColumn('changed', 'boolean', ['null' => false, 'default' => false])
      ->addTimestampsWithTimezone()
      ->create();
  }
}
