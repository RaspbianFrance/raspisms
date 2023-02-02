<?php

use Phinx\Migration\AbstractMigration;

class AddPhoneLimits extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('phone_limit');
        $table->addColumn('id_phone', 'integer', ['null' => false])
              ->addColumn('volume', 'integer', ['null' => false])
              ->addColumn('startpoint', 'string', ['null' => false, 'limit' => 254]) # A relative time to use as startpoint for counting volume. See https://www.php.net/manual/en/datetime.formats.relative.php
              ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('id_phone', 'phone', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();
    }
}
