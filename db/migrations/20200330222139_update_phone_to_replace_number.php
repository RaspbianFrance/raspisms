<?php

use Phinx\Migration\AbstractMigration;

class UpdatePhoneToReplaceNumber extends AbstractMigration
{
    public function change()
    {

        $table = $this->table('phone');
        $table->removeColumn('number');
        $table->addColumn('name', 'string', ['null' => false, 'limit' => 150]);
        $table->addIndex('name', ['unique' => true]);
        $table->update();

        $table = $this->table('sended');
        $table->removeColumn('origin');
        $table->addColumn('id_phone', 'integer', ['null' => true]);
        $table->addForeignKey('id_phone', 'phone', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE']);
        $table->update();
        
        
        $table = $this->table('received');
        $table->removeColumn('destination');
        $table->addColumn('id_phone', 'integer', ['null' => true]);
        $table->addForeignKey('id_phone', 'phone', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE']);
        $table->update();
    }
}
