<?php

use Phinx\Migration\AbstractMigration;

class AddMediaLinksToSms extends AbstractMigration
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
        //Remove useless column from media
        $table = $this->table('media');

        if ($table->hasColumn('id_scheduled'))
        {
            if ($table->hasForeignKey('id_scheduled'))
            {
                $table->dropForeignKey('id_scheduled');
            }

            $table->removeColumn('id_scheduled');
            $table->update();
        }
        
        if ($table->hasColumn('id_user'))
        {
            if ($table->hasForeignKey('id_user'))
            {
                $table->dropForeignKey('id_user');
            }

            $table->removeColumn('id_user');
            $table->update();
        }

        $table->addColumn('id_user', 'integer')
              ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->update();

        //Add table to join scheduled and media
        $table = $this->table('media_scheduled');
        $table->addColumn('id_media', 'integer')
              ->addColumn('id_scheduled', 'integer')
              ->addForeignKey('id_media', 'media', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('id_scheduled', 'scheduled', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();
        
        //Add table to join sended and media
        $table = $this->table('media_sended');
        $table->addColumn('id_media', 'integer')
              ->addColumn('id_sended', 'integer')
              ->addForeignKey('id_media', 'media', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('id_sended', 'sended', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();

        //Add table to join received and media
        $table = $this->table('media_received');
        $table->addColumn('id_media', 'integer')
              ->addColumn('id_received', 'integer')
              ->addForeignKey('id_media', 'media', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->addForeignKey('id_received', 'received', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
              ->create();
    }
}
