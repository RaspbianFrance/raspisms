<?php

use Phinx\Migration\AbstractMigration;

class AddIdUserSendedAndReceived extends AbstractMigration
{
    /**
     * Add id_user field in sended and received table
     */
    public function change()
    {
        $this->table('received')
             ->addColumn('id_user', 'integer', ['null' => false])
             ->addForeignKey('id_user', 'user', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
             ->save();
        
        $this->table('sended')
             ->addColumn('id_user', 'integer', ['null' => false])
             ->addForeignKey('id_user', 'user', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
             ->save();
    }
}
