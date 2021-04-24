<?php

use Phinx\Migration\AbstractMigration;

class AddCreatedAtAndUpdatedAt extends AbstractMigration
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
        $database_name = $this->getAdapter()->getOption('name');
        $query = 'SELECT table_name FROM information_schema.tables WHERE table_schema = \'' . $database_name . '\'';
        $tables = $this->query($query)->fetchAll();
        foreach ($tables as $table)
        {
            //Do not modify phinxlog
            if ($table['table_name'] == 'phinxlog')
            {
                continue;
            }

            //Foreach table add timestamps, created_at and updated_at whith default values
            $table = $this->table($table['table_name']);
            $table->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP']);
            $table->update();
        }

    }
}
