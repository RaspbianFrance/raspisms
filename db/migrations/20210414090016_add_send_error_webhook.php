<?php


use Phinx\Migration\AbstractMigration;

class AddSendErrorWebhook extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('webhook');
        $users->changeColumn('type', 'enum', ['values' => ['send_sms', 'receive_sms', 'send_sms_error']])
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $users = $this->table('webhook');
        $users->changeColumn('type', 'enum', ['values' => ['send_sms', 'receive_sms']])
              ->save();
    }
}
