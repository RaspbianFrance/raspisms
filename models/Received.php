<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace models;

    /**
     * Cette classe gère les accès bdd pour les receivedes.
     */
    class Received extends \descartes\Model
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
            return $this->_select_one('received', ['id' => $id]);
        }

        /**
         * Return a list of sms where destination in array allowed_destinations
         * @param int $id_user : User id
         * @param int $limit  : Max results to return
         * @param int $offset : Number of results to ignore
         */
        public function list_for_user($id_user, $limit, $offset)
        {
            $limit = (int) $limit;
            $offset = (int) $offset;

            $query = ' 
                SELECT * FROM received
                WHERE destination IN (SELECT number FROM phone WHERE id_user = :id_user)
                LIMIT ' . $limit . ' OFFSET ' . $offset;

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Cette fonction retourne les X dernières entrées triées par date pour un utilisateur.
         *
         * @param int $id_user : User id
         * @param int $nb_entry : Nombre d'entrée à retourner
         *
         * @return array : Les dernières entrées
         */
        public function get_lasts_for_user_by_date($id_user, $nb_entry)
        {
            $nb_entry = (int) $nb_entry;

            $query = '
                SELECT *
                FROM received
                WHERE destination IN (SELECT number FROM phone WHERE id_user = :id_user)
                ORDER BY at ASC
                LIMIT ' . $nb_entry;

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Cette fonction retourne une liste des received sous forme d'un tableau.
         *
         * @param string $origin : Le numéro depuis lequel est envoyé le message
         *
         * @return array : La liste des received
         */
        public function get_by_origin($origin)
        {
            return $this->_select('received', ['origin' => $origin]);
        }

        /**
         * Retourne une liste de receivedes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM received
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Retourne une liste de receivedes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM received
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une receivede.
         *
         * @param array $received : La receivede à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($received)
        {
            $result = $this->_insert('received', $received);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une receivede par son id.
         *
         * @param int   $id       : L'id de la received à modifier
         * @param array $received : Les données à mettre à jour pour la receivede
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $received)
        {
            return $this->_update('received', $received, ['id' => $id]);
        }

        /**
         * Count number of received sms for user
         * @param int $id_user : user id
         * @return int : Number of received SMS for user
         */
        public function count($id_user)
        {
            $query = '
                SELECT COUNT(id) as nb
                FROM received
                WHERE destination IN (SELECT number FROM phone WHERE id_user = :id_user)
            ';

            $params = [
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params)[0]['nb'] ?? 0;
        }

        /**
         * Récupère le nombre de SMS envoyés pour chaque jour depuis une date.
         * @param int $id_user : user id
         * @param \DateTime $date : La date depuis laquelle on veux les SMS
         *
         * @return array : Tableau avec le nombre de SMS depuis la date
         */
        public function count_for_user_by_day_since($id_user, $date)
        {
            $query = " 
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM received
                WHERE at > :date
                AND destination IN (SELECT number FROM phone WHERE id_user = :id_user)
                GROUP BY at_ymd
            ";

            $params = [
                'date' => $date,
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Cette fonction retourne toutes les discussions, càd les numéros pour lesquels ont a a la fois un message et une réponse.
         */
        public function get_discussions()
        {
            $query = ' 
                    SELECT MAX(at) as at, number
                    FROM (SELECT at, destination as number FROM sended UNION (SELECT at, origin as number FROM received)) as discussions
                    GROUP BY number
                    ORDER BY at DESC
            ';

            return $this->_run_query($query);
        }

        /**
         * Get SMS received since a date for a user 
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @param int $id_user : User id
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_by_date_for_user($date, $id_user)
        {
            $query = " 
                SELECT *
                FROM received
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND destination IN (SELECT number FROM phone WHERE id_user = :id_user)
                ORDER BY at ASC";
            
            $params = [
                'date' => $date,
                'id_user' => $id_user,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Récupère les SMS reçus depuis une date pour un numero.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @param $origin : Le numéro
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_for_origin_by_date($date, $origin)
        {
            $query = " 
                SELECT *
                FROM received
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND origin = :origin
                ORDER BY at ASC
            ";

            $params = [
                'date' => $date,
                'origin' => $origin,
            ];

            return $this->_run_query($query, $params);
        }
    }
