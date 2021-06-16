<?php

use Phinx\Migration\AbstractMigration;

class CreateNewSettingsDefaults extends AbstractMigration
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
        $query = 'INSERT INTO setting (id_user, name, value)
                  SELECT id, \'hide_menus\', \'\' FROM user WHERE id NOT IN (SELECT id_user FROM setting WHERE name = \'hide_menus\')';
        $this->execute($query);
        
        
        $query = 'INSERT INTO setting (id_user, name, value)
                  SELECT id, \'alert_quota_limit_reached\', \'1\' FROM user WHERE id NOT IN (SELECT id_user FROM setting WHERE name = \'alert_quota_limit_reached\')';
        $this->execute($query);


        $query = 'INSERT INTO setting (id_user, name, value)
                  SELECT id, \'alert_quota_limit_close\', \'0.9\' FROM user WHERE id NOT IN (SELECT id_user FROM setting WHERE name = \'alert_quota_limit_close\')';
        $this->execute($query);
    }
}
