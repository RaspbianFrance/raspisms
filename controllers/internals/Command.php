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

    class Command extends StandardController
    {
        protected $model;

        /**
         * Create a new command.
         *
         * @param int    $id_user : User id
         * @param string $name    : Command name
         * @param string $script  : Script file
         * @param bool   $admin   : Is command admin only
         *
         * @return mixed bool|int : False if cannot create command, id of the new command else
         */
        public function create(int $id_user, string $name, string $script, bool $admin)
        {
            $command = [
                'id_user' => $id_user,
                'name' => $name,
                'script' => $script,
                'admin' => $admin,
            ];

            $result = $this->get_model()->insert($command);
            if (!$result)
            {
                return false;
            }

            $internal_event = new Event($this->bdd);
            $internal_event->create($id_user, 'COMMAND_ADD', 'Ajout commande : ' . $name . ' => ' . $script);

            return $result;
        }

        /**
         * Update a command.
         *
         * @param int    $id_user : User id
         * @param int    $id      : Command id
         * @param string $name    : Command name
         * @param string $script  : Script file
         * @param bool   $admin   : Is command admin only
         *
         * @return mixed bool|int : False if cannot create command, id of the new command else
         */
        public function update_for_user(int $id_user, int $id, string $name, string $script, bool $admin)
        {
            $data = [
                'name' => $name,
                'script' => $script,
                'admin' => $admin,
            ];

            return $this->get_model()->update_for_user($id_user, $id, $data);
        }

        /**
         * Analyse a message to check if it's a command so execute it.
         *
         * @param int    $id_user : User id to search a command for
         * @param string $message : Message to analyse
         *
         * @return mixed bool|string : false if not a valid command, anonymized message if valid command
         */
        public function analyze_and_process(int $id_user, string $message)
        {
            if (!ENABLE_COMMAND)
            {
                return false;
            }

            $extracted_command = [];

            $decode_message = json_decode(trim($message), true);
            if (null === $decode_message)
            {
                return false;
            }

            if (!isset($decode_message['login'], $decode_message['password'], $decode_message['command']))
            {
                return false;
            }

            //Check for user
            $internal_user = new \controllers\internals\User($this->bdd);
            $user = $internal_user->check_credentials($decode_message['login'], $decode_message['password']);
            if (!$user || $user['id'] !== $id_user)
            {
                return false;
            }

            //Find command
            $commands = $this->gets_for_user($user['id']);
            $find_command = false;
            foreach ($commands as $command)
            {
                if ($decode_message['command'] === $command['name'])
                {
                    $find_command = $command;

                    break;
                }
            }

            if (false === $find_command)
            {
                return false;
            }

            //Check for admin rights
            if ($find_command['admin'] && !$user['admin'])
            {
                return false;
            }

            //Forge command and return
            $decode_message['password'] = '******';
            $updated_text = json_encode($decode_message);

            $script = $find_command['script'];
            while (str_replace('..', '', $script) !== $script)
            {
                $script = str_replace('..', '', $script);
            }

            $generated_command = PWD_SCRIPTS . '/' . escapeshellarg($script);
            $args = $decode_message['args'] ?? '';
            $generated_command .= ' ' . escapeshellcmd($args);

            exec($generated_command);

            return $updated_text;
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Command($this->bdd);

            return $this->model;
        }
    }
