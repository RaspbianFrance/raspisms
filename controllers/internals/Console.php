<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

use DateInterval;
use Faker\Factory;

    /**
     * Class to call the console scripts.
     */
    class Console extends \descartes\InternalController
    {
        /**
         * Start launcher daemon.
         */
        public function launcher()
        {
            new \daemons\Launcher();
        }

        /**
         * Start sender daemon.
         */
        public function sender()
        {
            new \daemons\Sender();
        }

        /**
         * Start webhook daemon.
         */
        public function webhook()
        {
            new \daemons\Webhook();
        }

        /**
         * Start mailer daemon.
         */
        public function mailer()
        {
            new \daemons\Mailer();
        }

        /**
         * Start a phone daemon.
         *
         * @param $id_phone : Phone id
         */
        public function phone($id_phone)
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $internal_phone = new \controllers\internals\Phone($bdd);

            $phone = $internal_phone->get($id_phone);
            if (!$phone)
        {
            exit(1);
        }

        new \daemons\Phone($phone);
    }

    /**
     * Check if a user exists based on email.
     *
     * @param string $email : User email
     */
    public function user_exists(string $email)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_user = new \controllers\internals\User($bdd);

        $user = $internal_user->get_by_email($email);

        exit($user ? 0 : 1);
    }

    /**
     * Check if a user exists based on id.
     *
     * @param string $id : User id
     */
    public function user_id_exists(string $id)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_user = new \controllers\internals\User($bdd);

        $user = $internal_user->get($id);

        exit($user ? 0 : 1);
    }

    /**
     * Create a user or update an existing user.
     *
     * @param $email : User email
     * @param $password : User password
     * @param $admin : Is user admin
     * @param $api_key : User API key, if null random api key is generated
     * @param $status : User status, default \models\User::STATUS_ACTIVE
     * @param bool $encrypt_password : Should the password be encrypted, by default true
     *
     * exit code 0 on success | 1 on error
     */
    public function create_update_user(string $email, string $password, bool $admin, ?string $api_key = null, string $status = \models\User::STATUS_ACTIVE, bool $encrypt_password = true)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_user = new \controllers\internals\User($bdd);

        $user = $internal_user->get_by_email($email);
        if ($user)
        {
            $api_key = $api_key ?? $internal_user->generate_random_api_key();
            $update_datas = [
                'email' => $email,
                'password' => $encrypt_password ? password_hash($password, PASSWORD_DEFAULT) : $password,
                'admin' => $admin,
                'api_key' => $api_key,
                'status' => $status,
            ];

            $success = $internal_user->update($user['id'], $update_datas);
            echo json_encode(['id' => $user['id']]);

            exit($success ? 0 : 1);
        }

        $new_user_id = $internal_user->create($email, $password, $admin, $api_key, $status, $encrypt_password);
        echo json_encode(['id' => $new_user_id]);

        exit($new_user_id ? 0 : 1);
    }

    /**
     * Update a user status.
     *
     * @param string $id     : User id
     * @param string $status : User status, default \models\User::STATUS_ACTIVE
     */
    public function update_user_status(string $id, string $status)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_user = new \controllers\internals\User($bdd);

        $user = $internal_user->get($id);
        if (!$user)
        {
            exit(1);
        }

        $success = $internal_user->update_status($user['id'], $status);

        exit($success ? 0 : 1);
    }

    /**
     * Delete a user.
     *
     * @param string $id : User id
     */
    public function delete_user(string $id)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_user = new \controllers\internals\User($bdd);

        $success = $internal_user->delete($id);

        exit($success ? 0 : 1);
    }

    /**
     * Delete medias that are no longer usefull.
     */
    public function clean_unused_medias()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_media = new \controllers\internals\Media($bdd);

        $medias = $internal_media->gets_unused();

        foreach ($medias as $media)
        {
            $success = $internal_media->delete_for_user($media['id_user'], $media['id']);

            echo (false === $success ? '[KO]' : '[OK]') . ' - ' . $media['path'] . "\n";
        }
    }

    /**
     * Do alerting for quota limits.
     */
    public function quota_limit_alerting()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_quota = new \controllers\internals\Quota($bdd);
        $internal_quota->alerting_for_limit_close_and_reached();
    }

    /**
     * Do quota renewal.
     */
    public function renew_quotas()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_quota = new \controllers\internals\Quota($bdd);
        $internal_quota->renew_quotas();
    }

    /**
     * Do phone reliability verifications
     */
    public function phone_reliability()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_user = new \controllers\internals\User($bdd);
        $internal_settings = new \controllers\internals\Setting($bdd);
        $internal_sended = new \controllers\internals\Sended($bdd);
        $internal_phone_reliability = new \controllers\internals\PhoneReliability($bdd);
        $internal_phone = new \controllers\internals\Phone($bdd);
        $internal_webhook = new \controllers\internals\Webhook($bdd);
        $internal_mailer = new \controllers\internals\Mailer();
        
        $users = $internal_user->get_all_active();
        foreach ($users as $user)
        {
            $settings = $internal_settings->gets_for_user($user['id']);
            echo "\nCheck phone reliability for user " . $user['id'] . ":\n";
            if ($settings['phone_reliability_failed'])
            {
                $rate_limit = intval($settings['phone_reliability_failed_rate_limit']) / 100;
                $min_volume = intval($settings['phone_reliability_failed_volume']);
                $period = intval($settings['phone_reliability_failed_period']);
                $grace_period = intval($settings['phone_reliability_failed_grace_period']);

                echo "  Check for failed SMS with rate > " . $rate_limit . " and volume > " . $min_volume . " on period " . $period . "s with grace period of " . $grace_period . "s.\n";

                $unreliable_phones = $internal_phone_reliability->find_unreliable_phones($user['id'], \models\Sended::STATUS_FAILED, $rate_limit, $min_volume, $period, $grace_period);
                foreach ($unreliable_phones as $unreliable_phone)
                {
                    $phone = $internal_phone->get($unreliable_phone['id_phone']);
                    if (!$phone)
                    {
                        echo '  Cannot find phone: ' . $unreliable_phone['id_phone'] . "\n";

                        continue;
                    }

                    echo "\n  Phone " . $phone['id'] . ' - ' . $phone['name'] . " failed rate = " . $unreliable_phone['rate'] . " > " . $rate_limit . " and volume " . $unreliable_phone['total'] . " > " . $min_volume . "\n";
                    
                    $internal_phone_reliability->create($user['id'], $phone['id'], \models\Sended::STATUS_FAILED);

                    if ($settings['phone_reliability_failed_email'])
                    {
                        $success = $internal_mailer->enqueue($user['email'], EMAIL_PHONE_RELIABILITY_FAILED, [
                            'phone' => $phone, 
                            'period' => $period, 
                            'total' => $unreliable_phone['total'], 
                            'unreliable' => $unreliable_phone['unreliable'],
                            'rate' => $unreliable_phone['rate'],
                        ]);

                        if (!$success)
                        {
                            echo '  Cannot enqueue alert for unreliable failed phone: ' . $unreliable_phone['id_phone'] . "\n";

                            continue;
                        }

                        echo "  Alert mail for unreliable failed phone " . $phone['id'] . ' - ' . $phone['name'] . " added\n";
                    }

                    if ($settings['phone_reliability_failed_webhook'])
                    {
                        $webhook = [
                            'reliability_type' => \models\Sended::STATUS_FAILED,
                            'id_phone' => $unreliable_phone['id_phone'],
                            'period' => $period,
                            'total' => $unreliable_phone['total'], 
                            'unreliable' => $unreliable_phone['unreliable'],
                            'rate' => $unreliable_phone['rate'],
                        ];

                        $internal_webhook->trigger($user['id'], \models\Webhook::TYPE_PHONE_RELIABILITY, $webhook);

                        echo "  Webhook for unreliable failed phone " . $phone['id'] . ' - ' . $phone['name'] . " triggered\n";
                    }

                    if ($settings['phone_reliability_failed_auto_disable'])
                    {
                        $internal_phone->update_status($unreliable_phone['id_phone'], \models\Phone::STATUS_DISABLED);
                    }
                }
            }

            if ($settings['phone_reliability_unknown'])
            {
                $rate_limit = intval($settings['phone_reliability_unknown_rate_limit']) / 100;
                $min_volume = intval($settings['phone_reliability_unknown_volume']);
                $period = intval($settings['phone_reliability_unknown_period']);
                $grace_period = intval($settings['phone_reliability_unknown_grace_period']);

                echo "\n  Check for unknown SMS with rate > " . $rate_limit . " and volume > " . $min_volume . " on period " . $period . "s with grace period of " . $grace_period . "s.\n";

                $unreliable_phones = $internal_phone_reliability->find_unreliable_phones($user['id'], \models\Sended::STATUS_UNKNOWN, $rate_limit, $min_volume, $period, $grace_period);
                foreach ($unreliable_phones as $unreliable_phone)
                {
                    $phone = $internal_phone->get($unreliable_phone['id_phone']);
                    if (!$phone)
                    {
                        echo '  Cannot find phone: ' . $unreliable_phone['id_phone'] . "\n";

                        continue;
                    }

                    echo "\n  Phone " . $phone['id'] . ' - ' . $phone['name'] . " unknown rate = " . $unreliable_phone['rate'] . " > " . $rate_limit . "\n";

                    $internal_phone_reliability->create($user['id'], $phone['id'], \models\Sended::STATUS_UNKNOWN);

                    if ($settings['phone_reliability_unknown_email'])
                    {
                        $success = $internal_mailer->enqueue($user['email'], EMAIL_PHONE_RELIABILITY_UNKNOWN, [
                            'phone' => $phone, 
                            'period' => $period, 
                            'total' => $unreliable_phone['total'], 
                            'unreliable' => $unreliable_phone['unreliable'],
                            'rate' => $unreliable_phone['rate'],
                        ]);

                        if (!$success)
                        {
                            echo '  Cannot enqueue alert for unreliable unknown phone: ' . $unreliable_phone['id_phone'] . "\n";

                            continue;
                        }

                        echo "  Alert mail for unreliable unknown phone " . $phone['id'] . ' - ' . $phone['name'] . " added\n";
                    }

                    if ($settings['phone_reliability_unknown_webhook'])
                    {
                        $webhook = [
                            'reliability_type' => \models\Sended::STATUS_UNKNOWN,
                            'id_phone' => $unreliable_phone['id_phone'],
                            'period' => $period,
                            'total' => $unreliable_phone['total'],
                            'unreliable' => $unreliable_phone['unreliable'],
                            'rate' => $unreliable_phone['rate'],
                        ];

                        $internal_webhook->trigger($user['id'], \models\Webhook::TYPE_PHONE_RELIABILITY, $webhook);

                        echo "  Webhook for unreliable unknown phone " . $phone['id'] . ' - ' . $phone['name'] . " triggered\n";
                    }

                    if ($settings['phone_reliability_unknown_auto_disable'])
                    {
                        $internal_phone->update_status($unreliable_phone['id_phone'], \models\Phone::STATUS_DISABLED);
                    }
                }
            }
        }
    }

   /**
     * Function to easily populate the database with fake data for testing.
     * 
     * @param int $id_user : User ID for whom data is to be generated
     * @param int $received_entries : Number of entries to add to the received table
     * @param int $sended_entries : Number of entries to add to the sended table
     * @param int $contact_entries : Number of entries to add to the contact table
     */
    public function seed_database(int $id_user, int $received_entries, int $sended_entries, int $contact_entries)
    {
        $this->seed_received($id_user, $received_entries);
        $this->seed_sended($id_user, $sended_entries);
        $this->seed_contact($id_user, $contact_entries);
    }

    /**
     * Fill table received with fake data
     * 
     * @param int $id_user : User to insert received for
     * @param int $entries : How many received to insert
     */
    public function seed_received(int $id_user, int $entries)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_received = new \controllers\internals\Received($bdd);
        $internal_phone = new \controllers\internals\Phone($bdd);
        $faker = Factory::create();

        $phones = $internal_phone->gets_for_user($id_user);

        for ($i = 0; $i < $entries; $i++)
        {
            $id_phone = $faker->randomElement($phones)['id'];
            $at = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');
            $text = $faker->sentence(rand(5,10), true);
            $origin = $faker->e164PhoneNumber;
            $status = $faker->randomElement(['read', 'unread']);
            $command = false;
            $mms = false;
            $media_ids = [];
        
        
            $internal_received->create($id_user, $id_phone, $at, $text, $origin, $status, $command, $mms, $media_ids);
        }
    }

    /**
     * Fill table sended with fake data
     * 
     * @param int $id_user : User to insert sended entries for
     * @param int $entries : Number of entries to insert
     */
    public function seed_sended(int $id_user, int $entries)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_sended = new \controllers\internals\Sended($bdd);
        $internal_phone = new \controllers\internals\Phone($bdd);
        $faker = Factory::create();

        $phones = $internal_phone->gets_for_user($id_user);

        for ($i = 0; $i < $entries; $i++)
        {        
            echo $i."\n";
            $phone = $faker->randomElement($phones);
            $id_phone = $phone['id'];
            $at = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');
            $text = $faker->sentence(rand(5, 10), true);
            $destination = $faker->e164PhoneNumber;
            $uid = $faker->uuid;
            $adapter = $phone['adapter'];
            $flash = $faker->boolean;
            $mms = $faker->boolean;
            $tag = $faker->optional()->word;
            $medias = []; // Add logic for media IDs if needed
            $originating_scheduled = $faker->numberBetween(1, 100);
            $status = $faker->randomElement([\models\Sended::STATUS_UNKNOWN, \models\Sended::STATUS_DELIVERED, \models\Sended::STATUS_FAILED]);
        
            $internal_sended->create($id_user, $id_phone, $at, $text, $destination, $uid, $adapter, $flash, $mms, $tag, $medias, $originating_scheduled, $status);
        }
    }

    /**
     * Fill table contact with fake data
     * 
     * @param int $id_user : User to insert contacts for
     * @param int $entries : Number of contacts to insert
     */
    public function seed_contact(int $id_user, int $entries)
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
        $internal_contact = new \controllers\internals\Contact($bdd);
        $faker = Factory::create();

        for ($i = 0; $i < $entries; $i++)
        {
            $name = $faker->name;
            $number = $faker->e164PhoneNumber;
            $data = '[]';

            $internal_contact->create($id_user, $number, $name, $data);
        }
    }
}
