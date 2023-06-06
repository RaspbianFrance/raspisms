<?php

use Phinx\Migration\AbstractMigration;

class AddIndexToSmsTimestamps extends AbstractMigration
{
    /**
     * Add indexes on most SMS table timestamp (and possibly other fields) to improve perfs on query using date, like stats, sending limits, etc.
     */
    public function change()
    {
        $table = $this->table('sended');
        $table->addIndex('at');
        $table->update();

        $table = $this->table('received');
        $table->addIndex('at');
        $table->update();

        $table = $this->table('scheduled');
        $table->addIndex('at');
        $table->update();
    }
}
