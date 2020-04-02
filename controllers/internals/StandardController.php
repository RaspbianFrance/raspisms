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

    abstract class StandardController extends \descartes\InternalController
    {
        protected $bdd;

        public function __construct(?\PDO $bdd = null)
        {
            if ($bdd === null)
            {
                $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            }
            $this->bdd = $bdd;
        }

        /**
         * Return all the entries.
         *
         * @return array
         */
        public function get_all()
        {
            return $this->get_model()->get_all();
        }

        /**
         * Return a entry by his id.
         *
         * @param int $id : Entry id
         *
         * @return array
         */
        public function get(int $id)
        {
            return $this->get_model()->get($id);
        }

        /**
         * Return a entry by his id and a user.
         *
         * @param int $id_user : Entry id
         * @param int $id      : Entry id
         *
         * @return array
         */
        public function get_for_user(int $id_user, int $id)
        {
            return $this->get_model()->get_for_user($id_user, $id);
        }

        /**
         * Return all entries for a user.
         *
         * @param int $id_user : Entry id
         *
         * @return array
         */
        public function gets_for_user(int $id_user)
        {
            return $this->get_model()->gets_for_user($id_user);
        }

        /**
         * Return the list of entries for a user.
         *
         * @param int  $id_user  : User id
         * @param ?int $nb_entry : Number of entry to return
         * @param ?int $page     : Pagination, used to calcul offset, $nb_entry * $page
         *
         * @return array : Entrys list
         */
        public function list_for_user(int $id_user, ?int $nb_entry = null, ?int $page = null)
        {
            return $this->get_model()->list_for_user($id_user, $nb_entry, $nb_entry * $page);
        }

        /**
         * Return a list of entries in a group of ids and for a user.
         *
         * @param int   $id_user : user id
         * @param array $ids     : ids of entries to find
         *
         * @return array
         */
        public function gets_in_for_user(int $id_user, array $ids)
        {
            return $this->get_model()->gets_in_for_user($id_user, $ids);
        }

        /**
         * Delete a entry by his id for a user.
         *
         * @param int $id_user : User id
         * @param int $id      : Entry id
         *
         * @return int : Number of removed rows
         */
        public function delete_for_user(int $id_user, int $id)
        {
            return $this->get_model()->delete_for_user($id_user, $id);
        }

        /**
         * Delete a entry by his id.
         *
         * @param int $id : Entry id
         *
         * @return int : Number of removed rows
         */
        public function delete(int $id)
        {
            return $this->get_model()->delete($id);
        }

        /**
         * Count number of entry for a user.
         *
         * @param int $id_user : User id
         *
         * @return int : number of entries
         */
        public function count_for_user(int $id_user)
        {
            return $this->get_model()->count_for_user($id_user);
        }

        /**
         * Get the model for the Controller.
         *
         * @return \descartes\Model
         */
        abstract protected function get_model(): \descartes\Model;
    }
