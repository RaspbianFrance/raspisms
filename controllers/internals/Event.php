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

    class Event extends StandardController
    {
        protected $model;

        /**
         * Disabled methods.
         */
        public function update_for_user()
        {
            return false;
        }

        /**
         * Gets lasts x events for a user order by date.
         *
         * @param int $id_user  : User id
         * @param int $nb_entry : Number of events to return
         *
         * @return array
         */
        public function get_lasts_by_date_for_user(int $id_user, int $nb_entry)
        {
            return $this->get_model()->get_lasts_by_date_for_user($id_user, $nb_entry);
        }

        /**
         * Create a new event.
         *
         * @param int   $id_user : user id
         * @param mixed $type
         * @param mixed $text
         *
         * @return mixed bool : false on fail, new event id else
         */
        public function create($id_user, $type, $text)
        {
            $event = [
                'id_user' => $id_user,
                'type' => $type,
                'text' => $text,
            ];

            return $this->get_model()->insert($event);
        }

        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Event($this->bdd);

            return $this->model;
        }
    }
