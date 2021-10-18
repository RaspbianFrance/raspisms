<?php

use Phinx\Migration\AbstractMigration;

class AddWebhookTypeSendSmsStatusChange extends AbstractMigration
{
    public function up()
    {
        $this->execute('ALTER TABLE `webhook` MODIFY `type` ENUM(\'send_sms\', \'send_sms_status_change\', \'receive_sms\', \'inbound_call\')');
    }

    public function down()
    {
        $this->execute('ALTER TABLE `webhook` MODIFY `type` ENUM(\'send_sms\', \'receive_sms\', \'inbound_call\')');
    }
}
