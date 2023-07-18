<?php

use Phinx\Migration\AbstractMigration;

class AddIndexOnSendedUid extends AbstractMigration
{
    /**
     * Modify sended uid and call to be 100 char long, we dont need a 500 char uid and too long a char ss hurting perfs
     * Add index on sended uid to make status update more efficient
     */
    public function change()
    {
        $table = $this->table('sended');
        $table->changeColumn('uid', 'string', ['limit' => 100]);
        $table->addIndex('uid');
        $table->update();


        $table = $this->table('call');
        $table->changeColumn('uid', 'string', ['limit' => 100]);
        $table->addIndex('uid');    
        $table->update();
    }
}
