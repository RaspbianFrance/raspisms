<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

    /**
     * Classe des scheduledes.
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
         * Cette fonction retourne une liste des scheduledes sous forme d'un tableau.
         *
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des scheduledes
         */
        public function get_list($nb_entry = false, $page = false)
        {
            //Recupération des scheduledes
            return $this->model_scheduled->get_list($nb_entry, $nb_entry * $page);
        }

        /**
         * Cette fonction retourne une liste des scheduledes sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des scheduledes
         */
        public function get_by_ids($ids)
        {
            //Recupération des scheduledes
            return $this->model_scheduled->get_by_ids($ids);
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
         *
         * @return int : Le nombre d'entrées dans la table
         */
        public function count()
        {
            return $this->model_scheduled->count();
        }

        /**
         * Cette fonction va supprimer un scheduled.
         *
         * @param int $id : L'id du scheduled à supprimer
         *
         * @return int : Le nombre de scheduledes supprimées;
         */
        public function delete($id)
        {
            return $this->model_scheduled->delete_by_id($id);
        }

        /**
         * Cette fonction insert un nouveau scheduled.
         *
         * @param array $scheduled    : Le scheduled à créer avec at, content, flash, progress
         * @param array $numbers      : Les numéros auxquels envoyer le scheduled
         * @param array $contacts_ids : Les ids des contact auquels envoyer le scheduled
         * @param array $groups_ids   : Les ids des group auxquels envoyer le scheduled
         *
         * @return mixed bool|int : false si echec, sinon l'id du nouveau scheduled inséré
         */
        public function create($scheduled, $numbers = [], $contacts_ids = [], $groups_ids = [])
        {
            if (!$id_scheduled = $this->model_scheduled->insert($scheduled))
            {
                $date = date('Y-m-d H:i:s');
                $this->internal_event->create('SCHEDULED_ADD', 'Ajout d\'un Sms pour le '.$date.'.');

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
         * Cette fonction met à jour une série de scheduledes.
         *
         * @param array $scheduleds   : Un tableau de scheduled à modifier avec at, content, flash, progress + pour chaque scheduled number, contact_ids, group_ids
         * @param array $numbers      : Les numéros auxquels envoyer le scheduled
         * @param array $contacts_ids : Les ids des contact auquels envoyer le scheduled
         * @param array $groups_ids   : Les ids des group auxquels envoyer le scheduled
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($scheduleds)
        {
            $nb_update = 0;
            foreach ($scheduleds as $scheduled)
            {
                $result = $this->model_scheduled->update($scheduled['scheduled']['id'], $scheduled['scheduled']);

                $this->model_scheduled->delete_scheduled_number($scheduled['scheduled']['id']);
                $this->model_scheduled->delete_scheduled_contact($scheduled['scheduled']['id']);
                $this->model_scheduled->delete_scheduled_group($scheduled['scheduled']['id']);

                foreach ($scheduled['number'] as $number)
                {
                    $this->model_scheduled->insert_scheduled_number($scheduled['scheduled']['id'], $number);
                }

                foreach ($scheduled['contact_ids'] as $contact_id)
                {
                    $this->model_scheduled->insert_scheduled_contact($scheduled['scheduled']['id'], $contact_id);
                }

                foreach ($scheduled['group_ids'] as $group_id)
                {
                    $this->model_scheduled->insert_scheduled_group($scheduled['scheduled']['id'], $group_id);
                }

                ++$nb_update;
            }

            return $nb_update;
        }

        /**
         * Cette fonction retourne une liste de numéro pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : La liste des scheduledes
         */
        public function get_number($id_scheduled)
        {
            //Recupération des scheduledes
            return $this->model_scheduled->get_number($id_scheduled);
        }

        /**
         * Cette fonction retourne une liste de contact pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : La liste des contact
         */
        public function get_contact($id_scheduled)
        {
            //Recupération des scheduledes
            return $this->model_scheduled->get_contact($id_scheduled);
        }

        /**
         * Cette fonction retourne une liste de group pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : La liste des group
         */
        public function get_group($id_scheduled)
        {
            //Recupération des scheduledes
            return $this->model_scheduled->get_group($id_scheduled);
        }
    }
