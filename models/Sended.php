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

namespace models;

    /**
     * Cette classe gère les accès bdd pour les sendedes.
     */
    class Sended extends \descartes\Model
    {
        /**
         * Retourne une entrée par son id.
         *
         * @param int $id : L'id de l'entrée
         *
         * @return array : L'entrée
         */
        public function get($id)
        {
            $sendeds = $this->_select('sended', ['id' => $id]);

            return isset($sendeds[0]) ? $sendeds[0] : false;
        }

        /**
         * Retourne une liste de sendedes sous forme d'un tableau.
         *
         * @param int $limit  : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function list($limit, $offset)
        {
            return $this->_select('sended', [], null, false, $limit, $offset);
        }

        /**
         * Retourne une liste de sendedes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM sended
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Cette fonction retourne les X dernières entrées triées par date.
         *
         * @param int $nb_entry : Nombre d'entrée à retourner
         *
         * @return array : Les dernières entrées
         */
        public function get_lasts_by_date($nb_entry)
        {
            return $this->_select('sended', [], 'at', true, $nb_entry);
        }

        /**
         * Cette fonction retourne une liste des sended sous forme d'un tableau.
         *
         * @param string $target : Le numéro auquel est envoyé le message
         *
         * @return array : La liste des sended
         */
        public function get_by_target($target)
        {
            return $this->_select('sended', ['target' => $target]);
        }

        /**
         * Retourne une liste de sendedes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM sended
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une sendede.
         *
         * @param array $sended : La sendede à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($sended)
        {
            $result = $this->_insert('sended', $sended);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une sendede par son id.
         *
         * @param int   $id     : L'id de la sended à modifier
         * @param array $sended : Les données à mettre à jour pour la sendede
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $sended)
        {
            return $this->_update('sended', $sended, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table.
         *
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('sended');
        }

        /**
         * Récupère le nombre de SMS envoyés pour chaque jour depuis une date.
         *
         * @param \DateTime $date : La date depuis laquelle on veux les SMS
         *
         * @return array : Tableau avec le nombre de SMS depuis la date
         */
        public function count_by_day_since($date)
        {
            $query = " 
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM sended
                WHERE at > :date
                GROUP BY at_ymd
            ";

            $params = [
                'date' => $date,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Decrement before_delivered field.
         *
         * @param int $id_sended : id of the sended sms to decrement
         */
        public function decrement_before_delivered($id_sended)
        {
            $query = ' 
                UPDATE sended
                SET before_delivered = before_delivered - 1
                WHERE id = :id_sended
                ';

            $params = ['id_sended' => $id_sended];

            return $this->_run_query($query, $params);
        }
    }
