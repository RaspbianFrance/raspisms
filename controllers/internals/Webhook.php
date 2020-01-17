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

    class Webhook extends StandardController
    {
        protected $bdd;
        protected $model;

        /**
         * Create a new webhook.
         *
         * @param int    $id_user : User id
         * @param string $url     : Webhook url
         * @param string $type    : Webhook type
         *
         * @return mixed bool|int : False if cannot create webhook, id of the new webhook else
         */
        public function create(int $id_user, string $url, string $type)
        {
            $webhook = [
                'id_user' => $id_user,
                'url' => $url,
                'type' => $type,
            ];

            $result = $this->get_model()->insert($webhook);
            if (!$result)
            {
                return false;
            }

            return $result;
        }

        /**
         * Update a webhook.
         *
         * @param int    $id_user : User id
         * @param int    $id      : Webhook id
         * @param string $url     : Webhook url
         * @param string $type    : Webhook type
         *
         * @return mixed bool|int : False if cannot create webhook, id of the new webhook else
         */
        public function update_for_user(int $id_user, int $id, string $url, string $type)
        {
            $datas = [
                'url' => $url,
                'type' => $type,
            ];

            return $this->get_model()->update_for_user($id_user, $id, $datas);
        }

        /**
         * Find all webhooks for a user and for a type of webhook.
         *
         * @param int    $id_user : User id
         * @param string $type    : Webhook type
         *
         * @return array
         */
        public function gets_for_type_and_user(int $id_user, string $type)
        {
            return $this->get_model()->gets_for_type_and_user($id_user, $type);
        }

        /**
         * Get the model for the Controller.
         *
         * @return \descartes\Model
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Webhook($this->bdd);

            return $this->model;
        }
    }
