<?php

use Phinx\Migration\AbstractMigration;

class AddIdPhoneScheduled extends AbstractMigration
{
    /**
     * Add column id_phone in scheduled and remove origin
     */
    public function change()
    {
        $table = $this->table('scheduled');
        $table->removeColumn('origin')
              ->addColumn('id_phone', 'integer', ['null' => True])
              ->addForeignKey('id_phone', 'phone', 'id', ['delete'=> 'SET_NULL', 'update'=> 'CASCADE'])
              ->save();
    }
}
