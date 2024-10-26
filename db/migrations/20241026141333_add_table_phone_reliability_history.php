<?php

use Phinx\Migration\AbstractMigration;

class AddTablePhoneReliabilityHistory extends AbstractMigration
{
    public function change()
    {
        // Create the phone_reliability_history table
        // This table store history of reliability alert for phones, so we can use last alert as min date
        // for surveillance periode, preventing triggering same alert in a loop
        $this->table('phone_reliability_history')
            ->addColumn('id_user', 'integer', ['null' => false])
            ->addColumn('id_phone', 'integer', ['null' => false])
            ->addColumn('type', 'string', ['null' => false, 'limit' => 100])
            ->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_user', 'user', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('id_phone', 'phone', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
