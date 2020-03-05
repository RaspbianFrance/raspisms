<?php

use Phinx\Migration\AbstractMigration;

class AddConstraints extends AbstractMigration
{
    /**
     * Add constraints on all necessary tables
     */
    public function change()
    {
        $this->table('command')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();
        
        $this->table('conditional_group')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();
        
        $this->table('contact')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('event')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('group')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('group_contact')
             ->addForeignKey('id_group', 'group', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->addForeignKey('id_contact', 'contact', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('media')
             ->addForeignKey('id_scheduled', 'scheduled', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('phone')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('scheduled')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('scheduled_conditional_group')
             ->addForeignKey('id_scheduled', 'scheduled', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->addForeignKey('id_conditional_group', 'conditional_group', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('scheduled_contact')
             ->addForeignKey('id_scheduled', 'scheduled', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->addForeignKey('id_contact', 'contact', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('scheduled_group')
             ->addForeignKey('id_scheduled', 'scheduled', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->addForeignKey('id_group', 'group', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();

        $this->table('scheduled_number')
             ->addForeignKey('id_scheduled', 'scheduled', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();
        
        $this->table('setting')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
                 ->save();

        $this->table('smsstop')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();
        
        $this->table('webhook')
             ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
             ->save();
    }
}
