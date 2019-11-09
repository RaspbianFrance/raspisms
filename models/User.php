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

    class User extends \descartes\Model
    {
        /**
         * Retourne un user par son email.
         *
         * @param string $email : L'email du user
         *
         * @return mixed array | false : false si pas de user pour ce mail, sinon le user associé sous forme de tableau
         */
        public function get_by_email($email)
        {
            return $this->_select_one('user', ['email' => $email]);
        }

        /**
         * Return users by transfer status.
         *
         * @param bool $transfer : transfer status
         */
        public function gets_by_transfer($transfer)
        {
            return $this->_select('transfer', ['transfer' => $transfer]);
        }

        /**
         * Return list of user.
         *
         * @param int $limit  : Number of user to return
         * @param int $offset : Number of user to skip
         */
        public function list($limit, $offset)
        {
            return $this->_select('user', [], null, false, $limit, $offset);
        }

        /**
         * Retourne une liste de useres sous forme d'un tableau.
         *
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @param mixed $id
         *
         * @return int : Le nombre de lignes supprimées
         */
        public function remove($id)
        {
            return $this->_delete('user', ['id' => $id]);
        }

        /**
         * Insert un user.
         *
         * @param array $user : La user à insérer avec les champs name, script, admin & admin
         *
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert($user)
        {
            $result = $this->_insert('user', $user);

            if (!$result)
            {
                return false;
            }

            return $this->_last_id();
        }

        /**
         * Met à jour un user par son id.
         *
         * @param int   $id   : L'id de la user à modifier
         * @param array $user : Les données à mettre à jour pour la user
         *
         * @return int : le nombre de ligne modifiées
         */
        public function update($id, $user)
        {
            return $this->_update('user', $user, ['id' => $id]);
        }

        /**
         * Update a user password by his id.
         *
         * @param int   $id       : User id
         * @param array $password : The new password of the user
         *
         * @return int : Number of modified lines
         */
        public function update_password($id, $password)
        {
            return $this->_update('user', ['password' => $password], ['id' => $id]);
        }

        /**
         * Update a user transfer property value by his id.
         *
         * @param int   $id       : User id
         * @param array $transfer : The new transfer property value
         *
         * @return int : Number of modified lines
         */
        public function update_transfer($id, $transfer)
        {
            return $this->_update('user', ['transfer' => $transfer], ['id' => $id]);
        }

        /**
         * Update a user email by his id.
         *
         * @param int   $id    : User id
         * @param array $email : The new email
         *
         * @return int : Number of modified lines
         */
        public function update_email($id, $email)
        {
            return $this->_update('user', ['email' => $email], ['id' => $id]);
        }
    }
