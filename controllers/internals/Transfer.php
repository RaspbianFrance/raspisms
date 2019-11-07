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
     * Classe des transfers.
     */
    class Transfer extends \descartes\InternalController
    {
        private $model_transfer;
        private $internal_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_transfer = new \models\Transfer($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }

        /**
         * Return the list of transfers as an array.
         *
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des transfers
         */
        public function list($nb_entry = false, $page = false)
        {
            return $this->model_transfer->list($nb_entry, $nb_entry * $page);
        }

        /**
         * Get all transfers.
         *
         * @return array
         */
        public function get_all()
        {
            //Recupération des transfers
            return $this->model_transfer->get_all();
        }

        /**
         * Get transfers not in progress.
         *
         * @return array
         */
        public function get_not_in_progress()
        {
            return $this->model_transfer->get_not_in_progress();
        }

        /**
         * Cette fonction retourne une liste des transfers sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des transfers
         */
        public function gets($ids)
        {
            //Recupération des transfers
            return $this->model_transfer->gets($ids);
        }

        /**
         * Cette fonction permet de compter le nombre de scheduleds.
         *
         * @return int : Le nombre d'entrées dans la table
         */
        public function count()
        {
            return $this->model_transfer->count();
        }

        /**
         * Cette fonction va supprimer un transfer.
         *
         * @param array $id : L'id de la transfer à supprimer
         *
         * @return int : Le nombre de transfers supprimées;
         */
        public function delete($id)
        {
            return $this->model_transfer->delete($id);
        }

        /**
         * This function insert a new transfer.
         *
         * @param int  $id_received : Id of the received message to transfer
         * @param bool $progress    : If we must mark it as in progress
         *
         * @return int id of the new inserted transfer
         */
        public function create($id_received, $progress = false)
        {
            $transfer = [
                'id_received' => $id_received,
                'progress' => $progress,
            ];

            $result = $this->model_transfer->insert($transfer);

            if (!$result)
            {
                return false;
            }

            return $result;
        }

        /**
         * Cette fonction met à jour un transfer.
         *
         * @param int  $id
         * @param int  $id_received
         * @param bool $progress
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $id_received, $progress)
        {
            $transfer = [
                'id_received' => $id_received,
                'progress' => $progress,
            ];

            return $this->model_transfer->update($id, $transfer);
        }
    }
