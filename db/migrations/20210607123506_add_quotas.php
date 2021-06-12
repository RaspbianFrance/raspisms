<?php

use Phinx\Migration\AbstractMigration;

class AddQuotas extends AbstractMigration
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
        $table = $this->table('quota');
        $table->addColumn('id_user', 'integer', ['null' => false])
              ->addColumn('consumed', 'integer', ['null' => false, 'default' => 0])
              ->addColumn('credit', 'integer', ['null' => false])
              ->addColumn('additional', 'integer', ['null' => false, 'default' => 0])
              ->addColumn('report_unused', 'boolean', ['null' => false])
              ->addColumn('report_unused_additional', 'boolean', ['null' => false])
              ->addColumn('auto_renew', 'boolean', ['null' => false, 'default' => false])
              ->addColumn('renew_interval', 'string', ['null' => false, 'default' => NULL])
              ->addColumn('start_date', 'datetime', ['null' => false])
              ->addColumn('expiration_date', 'datetime', ['null' => false])
              ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addIndex(['id_user'], ['unique' => true])
              ->create();

    }
}
