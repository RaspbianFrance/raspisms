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
     * Cette classe gère les accès bdd pour les eventes.
     */
    class Event extends \descartes\Model
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
            $events = $this->_select('event', ['id' => $id]);

            return isset($events[0]) ? $events[0] : false;
        }

        /**
         * Retourne une liste de eventes sous forme d'un tableau.
         *
         * @param int $limit  : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
         */
        public function list($limit, $offset)
        {
            return $this->_select('event', [], '', false, $limit, $offset);
        }

        /**
         * Cette fonction retourne les X dernières entrées triées par date.
         *
         * @param mixed $nb_entry
         *
         * @return array : Les dernières entrées
         */
        public function get_lasts_by_date($nb_entry)
        {
            return $this->_select('event', [], 'at', true, $nb_entry);
        }

        /**
         * Retourne une liste de eventes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         *
         * @return array : La liste des entrées
         */
        public function gets($ids)
        {
            $query = ' 
                SELECT * FROM event
                WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generated_in = $this->_generate_in_from_array($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Retourne une liste de eventes sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function delete($id)
        {
            $query = ' 
                DELETE FROM event
                WHERE id = :id';

            $params = ['id' => $id];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une evente.
         *
         * @param array $event : La evente à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($event)
        {
            $result = $this->_insert('event', $event);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Compte le nombre d'entrées dans la table.
         *
         * @return int : Le nombre d'entrées
         */
        public function count()
        {
            return $this->_count('event');
        }
    }
