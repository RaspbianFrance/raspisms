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
     * Classe des commandes.
     */
    class Command extends \descartes\InternalController
    {
        private $model_command;
        private $internal_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_command = new \models\Command($bdd);
            $this->internal_event = new \controllers\internals\Event($bdd);
        }

        /**
         * Return the list of commands as an array.
         *
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des commandes
         */
        public function list($nb_entry = null, $page = null)
        {
            return $this->model_command->list($nb_entry, $nb_entry * $page);
        }

        /**
         * Get all commands.
         *
         * @return array
         */
        public function get_all()
        {
            //Recupération des commandes
            return $this->model_command->get_all();
        }

        /**
         * Cette fonction retourne une liste des commandes sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des commandes
         */
        public function gets($ids)
        {
            //Recupération des commandes
            return $this->model_command->gets($ids);
        }

        /**
         * Cette fonction permet de compter le nombre de scheduleds.
         *
         * @return int : Le nombre d'entrées dans la table
         */
        public function count()
        {
            return $this->model_command->count();
        }

        /**
         * Cette fonction va supprimer une commande.
         *
         * @param array $id : L'id de la commande à supprimer
         *
         * @return int : Le nombre de commandes supprimées;
         */
        public function delete($id)
        {
            return $this->model_command->delete($id);
        }

        /**
         * Cette fonction insert une nouvelle commande.
         *
         * @param array $command : La commande à insérer
         * @param mixed $name
         * @param mixed $script
         * @param mixed $admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle commande insérée
         */
        public function create($name, $script, $admin)
        {
            $command = [
                'name' => $name,
                'script' => $script,
                'admin' => $admin,
            ];

            $result = $this->model_command->insert($command);

            if (!$result)
            {
                return false;
            }

            $this->internal_event->create('COMMAND_ADD', 'Ajout commande : '.$name.' => '.$script);

            return $result;
        }

        /**
         * Cette fonction met à jour un commande.
         *
         * @param mixed $id
         * @param mixed $name
         * @param mixed $script
         * @param mixed $admin
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $name, $script, $admin)
        {
            $command = [
                'name' => $name,
                'script' => $script,
                'admin' => $admin,
            ];

            return $this->model_command->update($id, $command);
        }
    }
