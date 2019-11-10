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
     * Classe des smsstopes.
     */
    class SmsStop extends \descartes\InternalController
    {
        private $model_sms_stop;

        public function __construct(\PDO $bdd)
        {
            $this->model_sms_stop = new \models\SmsStop($bdd);
        }

        /**
         * Return all smsstops.
         *
         * @return array
         */
        public function get_all()
        {
            return $this->model_sms_stop->get_all();
        }

        /**
         * Cette fonction retourne une liste des smsstopes sous forme d'un tableau.
         *
         * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
         * @param mixed(int|bool) $page     : Le numéro de page en cours
         *
         * @return array : La liste des smsstopes
         */
        public function list($nb_entry = null, $page = null)
        {
            //Recupération des smsstopes
            return $this->model_sms_stop->list($nb_entry, $nb_entry * $page);
        }

        /**
         * Cette fonction retourne une liste des smsstopes sous forme d'un tableau.
         *
         * @param array int $ids : Les ids des entrées à retourner
         *
         * @return array : La liste des smsstopes
         */
        public function gets($ids)
        {
            //Recupération des smsstopes
            return $this->model_sms_stop->gets($ids);
        }

        /**
         * Cette fonction retourne un smsstop par son numéro de tel.
         *
         * @param string $number : Le numéro du smsstop
         *
         * @return array : Le smsstop
         */
        public function get_by_number($number)
        {
            //Recupération des smsstopes
            return $this->model_sms_stop->get_by_number($number);
        }

        /**
         * Cette fonction permet de compter le nombre de smsstops.
         *
         * @return int : Le nombre d'entrées dans la table
         */
        public function count()
        {
            return $this->model_sms_stop->count();
        }

        /**
         * Cette fonction va supprimer une liste de smsstops.
         *
         * @param array $ids : Les id des smsstopes à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de smsstopes supprimées;
         */
        public function delete($id)
        {
            return $this->model_sms_stop->delete($id);
        }

        /**
         * Cette fonction insert une nouvelle smsstope.
         *
         * @param array $smsstop : Un tableau représentant la smsstope à insérer
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle smsstope insérée
         */
        public function create($smsstop)
        {
            return $this->model_sms_stop->insert($smsstop);
        }
    }
