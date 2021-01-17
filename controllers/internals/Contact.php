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

    class Contact extends StandardController
    {
        protected $model;

        /**
         * Return a contact for a user by a number.
         *
         * @param int    $id_user : user id
         * @param string $number  : Contact number
         *
         * @return array
         */
        public function get_by_number_and_user(int $id_user, string $number)
        {
            return $this->get_model()->get_by_number_and_user($id_user, $number);
        }

        /**
         * Return a contact by his name for a user.
         *
         * @param int    $id_user : User id
         * @param string $name    : Contact name
         *
         * @return array
         */
        public function get_by_name_and_user(int $id_user, string $name)
        {
            return $this->get_model()->get_by_name_and_user($id_user, $name);
        }

        /**
         * Return all contacts of a user.
         *
         * @param int $id_user : user id
         *
         * @return array
         */
        public function gets_for_user(int $id_user)
        {
            return $this->get_model()->gets_for_user($id_user);
        }

        /**
         * Create a new contact.
         *
         * @param int    $id_user : User id
         * @param string $number  : Contact number
         * @param string $name    : Contact name
         * @param string $data   : Contact data
         *
         * @return mixed bool|int : False if cannot create contact, id of the new contact else
         */
        public function create($id_user, $number, $name, $data)
        {
            $contact = [
                'id_user' => $id_user,
                'number' => $number,
                'name' => $name,
                'data' => $data,
            ];

            $result = $this->get_model()->insert($contact);
            if (!$result)
            {
                return $result;
            }

            $internal_event = new Event($this->bdd);
            $internal_event->create($id_user, 'CONTACT_ADD', 'Ajout contact : ' . $name . ' (' . \controllers\internals\Tool::phone_format($number) . ')');

            return $result;
        }

        /**
         * Update a contact.
         *
         * @param int     $id_user : User id
         * @param int     $id      : Contact id
         * @param string  $number  : Contact number
         * @param string  $name    : Contact name
         * @param ?string $data   : Contact data
         *
         * @return int : number of modified rows
         */
        public function update_for_user(int $id_user, int $id, string $number, string $name, string $data)
        {
            $contact = [
                'number' => $number,
                'name' => $name,
                'data' => $data,
            ];

            return $this->get_model()->update_for_user($id_user, $id, $contact);
        }

        /**
         * Import a list of contacts as csv.
         *
         * @param resource $file_handler : File handler to import contacts from
         * @param int      $id_user      : User id
         *
         * @return mixed : False on error, number of inserted contacts else
         */
        public function import_csv(int $id_user, $file_handler)
        {
            if (!\is_resource($file_handler))
            {
                return false;
            }

            $nb_insert = 0;

            $head = null;
            while ($line = fgetcsv($file_handler))
            {
                if (null === $head)
                {
                    $head = $line;

                    continue;
                }

                $line = array_combine($head, $line);
                if (false === $line)
                {
                    continue;
                }

                if (!isset($line[array_keys($line)[0]], $line[array_keys($line)[1]]))
                {
                    continue;
                }

                $data = [];
                $i = 0;
                foreach ($line as $key => $value)
                {
                    ++$i;
                    if ($i < 3)
                    {
                        continue;
                    }

                    if ('' === $value)
                    {
                        continue;
                    }

                    $key = mb_ereg_replace('[\W]', '', $key);
                    $data[$key] = $value;
                }
                $data = json_encode($data);

                try
                {
                    $success = $this->create($id_user, $line[array_keys($line)[1]], $line[array_keys($line)[0]], $data);
                    if ($success)
                    {
                        ++$nb_insert;
                    }
                }
                catch (\Exception $e)
                {
                    continue;
                }
            }

            return $nb_insert;
        }

        /**
         * Import a list of contacts as json.
         *
         * @param resource $file_handler : File handler to import contacts from
         * @param int      $id_user      : User id
         *
         * @return mixed : False on error, number of inserted contacts else
         */
        public function import_json(int $id_user, $file_handler)
        {
            if (!\is_resource($file_handler))
            {
                return false;
            }

            $file_content = '';
            while ($line = fgets($file_handler))
            {
                $file_content .= $line;
            }

            try
            {
                $contacts = json_decode($file_content, true);

                if (!\is_array($contacts))
                {
                    return false;
                }

                $nb_insert = 0;
                foreach ($contacts as $contact)
                {
                    if (!\is_array($contact))
                    {
                        continue;
                    }

                    if (!isset($contact['name'], $contact['number']))
                    {
                        continue;
                    }

                    $data = $contact['data'] ?? [];
                    $data = json_encode($data);

                    try
                    {
                        $success = $this->create($id_user, $contact['number'], $contact['name'], $data);
                        if ($success)
                        {
                            ++$nb_insert;
                        }
                    }
                    catch (\Exception $e)
                    {
                        continue;
                    }
                }

                return $nb_insert;
            }
            catch (\Exception $e)
            {
                return false;
            }
        }

        /**
         * Export the contacts of a user as csv.
         *
         * @param int $id_user : User id
         *
         * @return array : ['headers' => array of headers to return, 'content' => the generated file]
         */
        public function export_csv(int $id_user): array
        {
            $contacts = $this->get_model()->gets_for_user($id_user);

            $columns = [0, 1];

            foreach ($contacts as $contact)
            {
                $data = json_decode($contact['data'], true);
                foreach ($data as $key => $value)
                {
                    $columns[] = $key;
                }
            }
            $columns = array_unique($columns);

            $lines = [];
            foreach ($contacts as $contact)
            {
                $data = json_decode($contact['data'], true);

                $line = [$contact['name'], $contact['number']];
                foreach ($columns as $column)
                {
                    if (isset($data[$column]))
                    {
                        $line[] = $data[$column];

                        continue;
                    }
                }
                $lines[] = $line;
            }

            //Php only support csv formatting to file. To get it in string we need to create a tmp in memory file, write in it, and then read the file into a var
            // output up to 5MB is kept in memory, if it becomes bigger it will automatically be written to a temporary file
            $csv_tmp_file = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');
            fputcsv($csv_tmp_file, $columns);
            foreach ($lines as $line)
            {
                fputcsv($csv_tmp_file, $line);
            }
            rewind($csv_tmp_file);

            $csv_string = stream_get_contents($csv_tmp_file);

            return [
                'headers' => [
                    'Content-Disposition: attachment; filename=contacts.csv',
                    'Content-Type: text/csv',
                    'Content-Length: ' . mb_strlen($csv_string),
                ],
                'content' => $csv_string,
            ];
        }

        /**
         * Export the contacts of a user as json.
         *
         * @param int $id_user : User id
         *
         * @return array : ['headers' => array of headers to return, 'content' => the generated file]
         */
        public function export_json(int $id_user): array
        {
            $contacts = $this->get_model()->gets_for_user($id_user);

            foreach ($contacts as &$contact)
            {
                unset($contact['id'], $contact['id_user']);

                $contact['data'] = json_decode($contact['data']);
            }
            $content = json_encode($contacts);

            return [
                'headers' => [
                    'Content-Disposition: attachment; filename=contacts.json',
                    'Content-Type: application/json',
                    'Content-Length: ' . mb_strlen($content),
                ],
                'content' => $content,
            ];
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Contact($this->bdd);

            return $this->model;
        }
    }
