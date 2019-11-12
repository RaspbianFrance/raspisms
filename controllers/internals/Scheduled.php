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

    /**
     * Classe des scheduleds.
     */
    class Scheduled extends \descartes\InternalController
    {
        private $model_scheduled;
        private $internal_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_scheduled = new \models\Scheduled($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }

        /**
         * Cette fonction retourne une liste des scheduleds sous forme d'un tableau.
         *
         * @param int $id_user : User id
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des scheduleds
         */
        public function list($id_user, $nb_entry = null, $page = null)
        {
            //Recupération des scheduleds
            return $this->model_scheduled->list($id_user, $nb_entry, $nb_entry * $page);
        }

        /**
         * Cette fonction retourne une liste des scheduleds sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des scheduleds
         */
        public function get ($id)
        {
            //Recupération des scheduleds
            return $this->model_scheduled->get($id);
        }

        /**
         * Cette fonction retourne une liste des scheduleds sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des scheduleds
         */
        public function gets($ids)
        {
            //Recupération des scheduleds
            return $this->model_scheduled->gets($ids);
        }

        /**
         * Cette fonction retourne les messages programmés avant une date et pour un numéro.
         *
         * @param \DateTime $date   : La date avant laquelle on veux le message
         * @param string    $number : Le numéro
         *
         * @return array : Les messages programmés avant la date
         */
        public function get_before_date_for_number($date, $number)
        {
            return $this->model_scheduled->get_before_date_for_number($date, $number);
        }

        /**
         * Cette fonction permet de compter le nombre de scheduled.
         * @param int $id_user : User id
         * @return int : Le nombre d'entrées dans la table
         */
        public function count($id_user)
        {
            return $this->model_scheduled->count($id_user);
        }

        /**
         * Cette fonction va supprimer un scheduled.
         *
         * @param int $id : L'id du scheduled à supprimer
         *
         * @return int : Le nombre de scheduleds supprimées;
         */
        public function delete($id)
        {
            return $this->model_scheduled->delete($id);
        }

        /**
         * Cette fonction insert un nouveau scheduled.
         * @param int $id_user
         * @param mixed $at
         * @param mixed $text
         * @param mixed $origin
         * @param mixed $flash
         * @param mixed $progress
         * @param array $numbers      : Les numéros auxquels envoyer le scheduled
         * @param array $contacts_ids : Les ids des contact auquels envoyer le scheduled
         * @param array $groups_ids   : Les ids des group auxquels envoyer le scheduled
         *
         * @return mixed bool|int : false si echec, sinon l'id du nouveau scheduled inséré
         */
        public function create($id_user, $at, $text, $origin = null, $flash = false, $numbers = [], $contacts_ids = [], $groups_ids = [])
        {
            $scheduled = [
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'origin' => $origin,
                'flash' => $flash,
            ];

            if (!$id_scheduled = $this->model_scheduled->insert($scheduled))
            {
                $date = date('Y-m-d H:i:s');
                $this->internal_event->create($id_user, 'SCHEDULED_ADD', 'Ajout d\'un Sms pour le '.$date.'.');

                return false;
            }

            foreach ($numbers as $number)
            {
                $this->model_scheduled->insert_scheduled_number($id_scheduled, $number);
            }

            foreach ($contacts_ids as $contact_id)
            {
                $this->model_scheduled->insert_scheduled_contact($id_scheduled, $contact_id);
            }

            foreach ($groups_ids as $group_id)
            {
                $this->model_scheduled->insert_scheduled_group($id_scheduled, $group_id);
            }

            return $id_scheduled;
        }

        /**
         * Cette fonction met à jour une série de scheduleds.
         *
         * @param mixed $id
         * @param int $id_user : User id
         * @param mixed $text
         * @param mixed $at
         * @param mixed $origin
         * @param array $numbers      : Les numéros auxquels envoyer le scheduled
         * @param array $contacts_ids : Les ids des contact auquels envoyer le scheduled
         * @param array $groups_ids   : Les ids des group auxquels envoyer le scheduled
         * @param mixed $flash
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $id_user, $at, $text, $origin = null, $flash = false, $numbers = [], $contacts_ids = [], $groups_ids = [])
        {
            $scheduled = [
                'id_user' => $id_user,
                'at' => $at,
                'text' => $text,
                'origin' => $origin,
                'flash' => $flash,
            ];

            $success = $this->model_scheduled->update($id, $scheduled);

            $this->model_scheduled->delete_scheduled_numbers($id);
            $this->model_scheduled->delete_scheduled_contacts($id);
            $this->model_scheduled->delete_scheduled_groups($id);

            foreach ($numbers as $number)
            {
                $this->model_scheduled->insert_scheduled_number($id, $number);
            }

            foreach ($contacts_ids as $contact_id)
            {
                $this->model_scheduled->insert_scheduled_contact($id, $contact_id);
            }

            foreach ($groups_ids as $group_id)
            {
                $this->model_scheduled->insert_scheduled_group($id, $group_id);
            }

            return (bool) $success;
        }

        /**
         * Cette fonction retourne une liste de numéro pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : La liste des scheduleds
         */
        public function get_numbers($id_scheduled)
        {
            //Recupération des scheduleds
            return $this->model_scheduled->get_numbers($id_scheduled);
        }

        /**
         * Cette fonction retourne une liste de contact pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : La liste des contact
         */
        public function get_contacts($id_scheduled)
        {
            //Recupération des scheduleds
            return $this->model_scheduled->get_contacts($id_scheduled);
        }

        /**
         * Cette fonction retourne une liste de group pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : La liste des group
         */
        public function get_groups($id_scheduled)
        {
            //Recupération des scheduleds
            return $this->model_scheduled->get_groups($id_scheduled);
        }

        /**
         * This function update progress status of a scheduled sms.
         *
         * @param bool  $progress     : Progress status
         * @param mixed $id_scheduled
         *
         * @return int : Number of update
         */
        public function update_progress($id_scheduled, $progress)
        {
            return $this->model_scheduled->update($id_scheduled, ['progress' => $progress]);
        }
    }
