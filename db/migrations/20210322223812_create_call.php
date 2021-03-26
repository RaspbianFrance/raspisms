<?php

use Phinx\Migration\AbstractMigration;

class CreateCall extends AbstractMigration
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
        $table = $this->table('call');
        $table->addColumn('id_user', 'integer')
              ->addColumn('id_phone', 'integer', ['null' => true])
              ->addColumn('uid', 'string', ['limit' => 500])
              ->addColumn('start', 'datetime')
              ->addColumn('end', 'datetime', ['null' => true])
              ->addColumn('direction', 'enum', ['values' => ['inbound', 'outbound']])
              ->addColumn('origin', 'string', ['limit' => 20, 'null' => true])
              ->addColumn('destination', 'string', ['limit' => 20, 'null' => true])
              ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('id_phone', 'phone', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
              ->create();
    }
}
