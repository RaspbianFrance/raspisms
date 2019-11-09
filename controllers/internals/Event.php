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
     * Classe des Event.
     */
    class Event extends \descartes\InternalController
    {
        private $model_event;

        public function __construct(\PDO $bdd)
        {
            $this->model_event = new \models\Event($bdd);
        }

        /**
         * Cette fonction retourne une liste des events sous forme d'un tableau.
         *
         * @param PDO             $bdd      :  instance PDO de la base de donnée
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des events
         */
        public function list($nb_entry = null, $page = null)
        {
            //Recupération des events
            return $this->model_event->list($nb_entry, $nb_entry * $page);
        }

        /**
         * Cette fonction retourne les X dernières entrées triées par date.
         *
         * @param mixed false|int $nb_entry : Nombre d'entrée à retourner ou faux pour tout
         *
         * @return array : Les dernières entrées
         */
        public function get_lasts_by_date($nb_entry = false)
        {
            return $this->model_event->get_lasts_by_date($nb_entry);
        }

        /**
         * Cette fonction va supprimer une liste de contacts.
         *
         * @param array $ids : Les id des contactes à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de contactes supprimées;
         */
        public function delete($id)
        {
            return $this->model_event->delete($id);
        }

        /**
         * Cette fonction insert un nouvel event.
         *
         * @param array $event : Un tableau représentant l'event à insérer
         * @param mixed $type
         * @param mixed $text
         *
         * @return mixed bool|int : false si echec, sinon l'id du nouvel event inséré
         */
        public function create($type, $text)
        {
            $event = [
                'type' => $type,
                'text' => $text,
            ];

            return $this->model_event->insert($event);
        }
    }
