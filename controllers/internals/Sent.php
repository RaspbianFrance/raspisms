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
     * Classe des sendedes.
     */
    class Sent extends \descartes\InternalController
    {
        private $model_sended;

        public function __construct(\PDO $bdd)
        {
            $this->model_sended = new \models\Sent($bdd);
        }

        /**
         * Cette fonction retourne une liste des sendedes sous forme d'un tableau.
         *
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des sendedes
         */
        public function list($nb_entry = false, $page = false)
        {
            //Recupération des sendedes
            return $this->model_sended->list($nb_entry, $nb_entry * $page);
        }

        /**
         * Cette fonction retourne une liste des sendedes sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des sendedes
         */
        public function gets($ids)
        {
            //Recupération des sendedes
            return $this->model_sended->gets($ids);
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
            return $this->model_sended->get_lasts_by_date($nb_entry);
        }

        /**
         * Cette fonction retourne une liste des receivedes sous forme d'un tableau.
         *
         * @param string $target : Le numéro auquel est envoyé le message
         *
         * @return array : La liste des sendeds
         */
        public function get_by_target($target)
        {
            //Recupération des sendeds
            return $this->model_sended->get_by_target($target);
        }

        /**
         * Cette fonction va supprimer une liste de sendeds.
         *
         * @param array $ids : Les id des sendedes à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de sendedes supprimées;
         */
        public function delete($id)
        {
            return $this->model_sended->delete($id);
        }

        /**
         * Cette fonction insert une nouvelle sendede.
         *
         * @param array $sended : Un tableau représentant la sendede à insérer
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle sendede insérée
         */
        public function create($sended)
        {
            return $this->model_sended->create($sended);
        }

        /**
         * Cette fonction permet de compter le nombre de sendeds.
         *
         * @return int : Le nombre d'entrées dans la table
         */
        public function count()
        {
            return $this->model_sended->count();
        }

        /**
         * Cette fonction compte le nombre de sendeds par jour depuis une date.
         *
         * @param mixed $date
         *
         * @return array : un tableau avec en clef la date et en valeure le nombre de sms envoyés
         */
        public function count_by_day_since($date)
        {
            $counts_by_day = $this->model_sended->count_by_day_since($date);
            $return = [];

            foreach ($counts_by_day as $count_by_day)
            {
                $return[$count_by_day['at_ymd']] = $count_by_day['nb'];
            }

            return $return;
        }
    }
