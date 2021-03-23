<?php

use Phinx\Migration\AbstractMigration;

class UpdateWebhookTypes extends AbstractMigration
{
    public function up()
    {
        $this->execute('ALTER TABLE `webhook` MODIFY `type` ENUM(\'send_sms\', \'receive_sms\', \'inbound_call\')');
    }

    public function down()
    {
        $this->execute('ALTER TABLE `webhook` MODIFY `type` ENUM(\'send_sms\', \'receive_sms\')');
    }
}
