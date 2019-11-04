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
     * Cette classe gère les accès bdd pour les scheduledes.
     */
    class Scheduled extends \descartes\Model
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
            $scheduleds = $this->_select('scheduled', ['id' => $id]);

            return isset($scheduleds[0]) ? $scheduleds[0] : false;
        }

        /**
         * Retourne une liste de scheduledes sous forme d'un tableau.
         *
         * @param int $limit  : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function list($limit, $offset)
        {
            return $this->_select('scheduled', [], '', false, $limit, $offset);
        }

        /**
         * Retourne une liste de scheduledes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM scheduled
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
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
            $query = ' 
                SELECT *
                FROM scheduled
                WHERE at <= :date
                AND (
                    id IN (
                        SELECT id_scheduled
                        FROM scheduled_number
                        WHERE number = :number
                    )
                    OR id IN (
                        SELECT id_scheduled
                        FROM scheduled_contact
                        WHERE id_contact IN (
                            SELECT id
                            FROM contact
                            WHERE number = :number
                        )
                    )
                    OR id IN (
                        SELECT id_scheduled
                        FROM scheduled_group
                        WHERE id_group IN (
                            SELECT id_group
                            FROM group_contact
                            WHERE id_contact IN (
                                SELECT id
                                FROM contact
                                WHERE number = :number
                            )
                        )
                    )
                )
            ';

            $params = [
                'date' => $date,
                'number' => $number,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Retourne une liste de scheduledes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM scheduled
                WHERE id = :id';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une schedulede.
         *
         * @param array $scheduled : La schedulede à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($scheduled)
        {
            $result = $this->_insert('scheduled', $scheduled);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour une schedulede par son id.
         *
         * @param int   $id        : L'id de la scheduled à modifier
         * @param array $scheduled : Les données à mettre à jour pour la schedulede
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $scheduled)
        {
            return $this->_update('scheduled', $scheduled, ['id' => $id]);
        }

        /**
         * Compte le nombre d'entrées dans la table.
         *
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('scheduled');
        }

        /**
         * Cette fonction retourne une liste de numéro pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : Les numéros des scheduled
         */
        public function get_number($id_scheduled)
        {
            return $this->_select('scheduled_number', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Cette fonction retourne une liste de contact pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : Les contact du scheduled
         */
        public function get_contact($id_scheduled)
        {
            $query = 'SELECT * FROM contact WHERE id IN (SELECT id_contact FROM scheduled_contact WHERE id_scheduled = :id_scheduled)';

            $params = ['id_scheduled' => $id_scheduled];

            return $this->_run_query($query, $params);
        }

        /**
         * Cette fonction retourne une liste de groups pour un scheduled.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel on veux le numéro
         *
         * @return array : Les groups du scheduled
         */
        public function get_group($id_scheduled)
        {
            $query = 'SELECT * FROM group WHERE id IN (SELECT id_group FROM scheduled_group WHERE id_scheduled = :id_scheduled)';

            $params = ['id_scheduled' => $id_scheduled];

            return $this->_run_query($query, $params);
        }

        /**
         * Insert un liens scheduled/number.
         *
         * @param int    $id_scheduled : L'id du scheduled
         * @param string $number       : Le numéro à lier
         *
         * @return int : le nombre d'entrées
         */
        public function insert_scheduled_number($id_scheduled, $number)
        {
            $result = $this->_insert('scheduled_number', ['id_scheduled' => $id_scheduled, 'number' => $number]);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Insert un liens scheduled/contact.
         *
         * @param int $id_scheduled : L'id du scheduled
         * @param int $id_contact   : L'id du contact
         *
         * @return int : le nombre d'entrées
         */
        public function insert_scheduled_contact($id_scheduled, $id_contact)
        {
            $result = $this->_insert('scheduled_contact', ['id_scheduled' => $id_scheduled, 'id_contact' => $id_contact]);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Insert un liens scheduled/group.
         *
         * @param int $id_scheduled : L'id du scheduled
         * @param int $id_group     : L'id du group
         *
         * @return int : le nombre d'entrées
         */
        public function insert_scheduled_group($id_scheduled, $id_group)
        {
            $result = $this->_insert('scheduled_group', ['id_scheduled' => $id_scheduled, 'id_group' => $id_group]);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Supprime les liens scheduled/number pour un scheduled précis.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel supprimer
         *
         * @return int : Le nmbre d'entrées modifiées
         */
        public function delete_scheduled_number($id_scheduled)
        {
            return $this->_delete('scheduled_number', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Supprime les liens scheduled/contact pour un scheduled précis.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel supprimer
         *
         * @return int : Le nmbre d'entrées modifiées
         */
        public function delete_scheduled_contact($id_scheduled)
        {
            return $this->_delete('scheduled_contact', ['id_scheduled' => $id_scheduled]);
        }

        /**
         * Supprime les liens scheduled/group pour un scheduled précis.
         *
         * @param int $id_scheduled : L'id du scheduled pour lequel supprimer
         *
         * @return int : Le nmbre d'entrées modifiées
         */
        public function delete_scheduled_group($id_scheduled)
        {
            return $this->_delete('scheduled_group', ['id_scheduled' => $id_scheduled]);
        }
    }
