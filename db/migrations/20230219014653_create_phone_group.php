<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreatePhoneGroup extends AbstractMigration
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
        $this->table('phone_group')
             ->addColumn('id_user', 'integer', ['null' => false])
             ->addColumn('name', 'string', ['null' => false, 'limit' => 100])
             ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
             ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->create();

        $this->table('phone_group_phone')
             ->addColumn('id_phone_group', 'integer', ['null' => false])
             ->addColumn('id_phone', 'integer', ['null' => false])
             ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
             ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
             ->addForeignKey('id_phone_group', 'phone_group', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->addForeignKey('id_phone', 'phone', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->create();
    }
}
