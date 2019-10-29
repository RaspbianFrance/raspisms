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

    class DataBase extends \descartes\Model
    {
        //
        // PARTIE DES REQUETES SENDEDS
        //

        /**
         * Récupère les SMS envoyés depuis une date.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25)
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function getNbSendedsSinceGroupDay($date)
        {
            $query = "
				SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
				FROM sended
				WHERE at > STR_TO_DATE(:date, '%Y-%m-%d')
				GROUP BY at_ymd
			";

            $params = [
                'date' => $date,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Récupère SMS envoyé à partir de son id.
         *
         * @param int $id = L'id du SMS
         *
         * @return array : Retourne le SMS
         */
        public function getSendedFromId($id)
        {
            $query = '
				SELECT *
				FROM sended
				WHERE id = :id';

            $params = [
                'id' => $id,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Supprime tous les sendeds dont l'id fait partie du tableau fourni.
         *
         * @param $sendeds_ids : Tableau des id des sendeds à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteSendedsIn($sendeds_ids)
        {
            $query = '
				DELETE FROM sended
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($sendeds_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES RECEIVEDS
        //

        /**
         * Insert un nouveau SMS reçu.
         *
         * @param string $date       : La date d'envoie du message
         * @param string $send_by    : Numéro auquel depuis lequel le message a ete envoye
         * @param string $content    : Texte du message
         * @param string $is_command : Commande reconnue
         *
         * @return int : le nombre de SMS créés
         */
        public function insertReceived($date, $send_by, $content, $is_command)
        {
            $query = '
				INSERT INTO received(at, send_by, content, is_command)
				VALUES (:date, :send_by, :content, :is_command)
			';

            $params = [
                'date' => $date,
                'send_by' => $send_by,
                'content' => $content,
                'is_command' => $is_command,
            ];

            return $this->_run_query($query, $params, self::ROWCOUNT); //On retourne le nombre de lignes ajoutés
        }

        /**
         * Récupère les SMS reçus depuis une date.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25)
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function getNbReceivedsSinceGroupDay($date)
        {
            $query = "
				SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
				FROM received
				WHERE at > STR_TO_DATE(:date, '%Y-%m-%d')
				GROUP BY at_ymd
			";

            $params = [
                'date' => $date,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Récupère les SMS reçus depuis une date.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function getReceivedsSince($date)
        {
            $query = "
				SELECT *
				FROM received
				WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
			";

            $params = [
                'date' => $date,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Récupère les SMS reçus depuis une date pour un numero.
         *
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @param $number : Le numéro
         *
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function getReceivedsSinceForNumberOrderByDate($date, $number)
        {
            $query = "
				SELECT *
				FROM received
				WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
				AND send_by = :number
				ORDER BY at ASC
			";

            $params = [
                'date' => $date,
                'number' => $number,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Récupère les receiveds dont l'id fait partie de la liste fournie.
         *
         * @param array $receiveds_ids = Tableau des id des receiveds voulus
         *
         * @return array : Retourne un tableau avec les receiveds adaptés
         */
        public function getReceivedsIn($receiveds_ids)
        {
            $query = '
				SELECT *
				FROM received
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($receiveds_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Supprime tous les receivedss dont l'id fait partie du tableau fourni.
         *
         * @param $receiveds_ids : Tableau des id des receiveds à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteReceivedsIn($receiveds_ids)
        {
            $query = '
				DELETE FROM received
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($receiveds_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES CONTACTS
        //

        /**
         * Supprime tous les contacts dont l'id fait partie du tableau fourni.
         *
         * @param $contacts_ids : Tableau des id des contacts à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteContactsIn($contacts_ids)
        {
            $query = '
				DELETE FROM contact
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($contacts_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Récupère les contacts dont l'id fait partie de la liste fournie.
         *
         * @param array $contacts_ids = Tableau des id des contacts voulus
         *
         * @return array : Retourne un tableau avec les contacts adaptés
         */
        public function getContactsIn($contacts_ids)
        {
            $query = '
				SELECT *
				FROM contact
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($contacts_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        //
        // PARTIE DES REQUETES GROUPS
        //

        /**
         * Insert un group.
         *
         * @param string $nom  : Le nom du nouveau group
         * @param mixed  $name
         *
         * @return int : le nombre de lignes crées
         */
        public function insertGroup($name)
        {
            $query = '
				INSERT INTO group(name)
				VALUES (:name)
			';

            $params = [
                'name' => $name,
            ];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Récupère les groups dont l'id fait partie de la liste fournie.
         *
         * @param array $groups_ids = Tableau des id des groups voulus
         *
         * @return array : Retourne un tableau avec les groups adaptés
         */
        public function getGroupsIn($groups_ids)
        {
            $query = '
				SELECT *
				FROM group
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($groups_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Supprime tous les groups dont l'id fait partie du tableau fourni.
         *
         * @param $contacts_ids : Tableau des id des groups à supprimer
         * @param mixed $groups_ids
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteGroupsIn($groups_ids)
        {
            $query = '
				DELETE FROM group
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($groups_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES GROUPS_CONTACTS
        //

        /**
         * Retourne tous les contacts pour un group donnée.
         *
         * @param int $id_group : L'id du group
         *
         * @return array : Tous les contacts compris dans le group
         */
        public function getContactsForGroup($id_group)
        {
            $query = '
				SELECT con.id as id, con.name as name, con.number as number
				FROM group_contact as g_c
				JOIN contact as con
				ON (g_c.id_contact = con.id)
				WHERE(g_c.id_group = :id_group)
			';

            $params = [
                'id_group' => $id_group,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Récupère tout les groups, avec le nombre de contact dans chacun.
         *
         * @param mixed $order_by
         * @param mixed $desc
         * @param mixed $limit
         * @param mixed $offset
         *
         * @return array : Tableau avec tous les groups et le nombre de contacts liés
         */
        public function getGroupsWithContactsNb($order_by = '', $desc = false, $limit = false, $offset = false)
        {
            $query = '
				SELECT gro.id as id, gro.name as name, COUNT(g_c.id) as nb_contacts
				FROM group as gro
				LEFT JOIN group_contact as g_c
				ON (g_c.id_group = gro.id)
				GROUP BY id
			';

            if ($order_by)
            {
                $orders = [
                    'id',
                    'name',
                    'number',
                ];

                if (\in_array($order_by, $orders, true))
                {
                    $query .= ' ORDER BY '.$order_by;
                    if ($desc)
                    {
                        $query .= ' DESC';
                    }
                }
            }

            if (false !== $limit)
            {
                $query .= ' LIMIT :limit';
                if (false !== $offset)
                {
                    $query .= ' OFFSET :offset';
                }
            }

            $req = $this->pdo->prepare($query);

            if (false !== $limit)
            {
                $req->bindParam(':limit', $limit, \PDO::PARAM_INT);
                if (false !== $offset)
                {
                    $req->bindParam(':offset', $offset, \PDO::PARAM_INT);
                }
            }

            $req->execute();

            return $req->fetchAll();
        }

        //
        // PARTIE DES REQUETES SCHEDULEDS
        //

        /**
         * Récupère tout les sms programmés non en cours, et dont la date d'envoie inférieure à celle renseignée.
         *
         * @param string $date : \Date avant laquelle on veux les sms
         *
         * @return array : Tableau avec les sms programmés demandés
         */
        public function getScheduledsNotInProgressBefore($date)
        {
            $query = '
				SELECT *
				FROM scheduled
				WHERE progress = 0
				AND at <= :date
			';

            $params = [
                'date' => $date,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Insert un sms.
         *
         * @param string $date    : La date d'envoi du SMS
         * @param string $content : Le contenu du SMS
         *
         * @return int : le nombre de lignes crées
         */
        public function insertScheduleds($date, $content)
        {
            $query = '
				INSERT INTO scheduled(at, content, progress)
				VALUES (:date, :content, :progress)
			';

            $params = [
                'date' => $date,
                'content' => $content,
                'progress' => false,
            ];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Supprime tous les sms programmés dont l'id fait partie du tableau fourni.
         *
         * @param $contacts_ids : Tableau des id des sms à supprimer
         * @param mixed $scheduleds_ids
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteScheduledsIn($scheduleds_ids)
        {
            $query = '
				DELETE FROM scheduled
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($scheduleds_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Cette fonction retourne les sms programmés pour un numéro donné et avant une date.
         *
         * @param string $date   : La date avant laquel on veux les  numéros (format yyyy-mm-dd hh:mm:ss)
         * @param string $number : Le numéro cible
         *
         * @return array : Les scheduleds correspondants
         */
        public function getScheduledsBeforeDateForNumber($date, $number)
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

        //
        // PARTIE DES REQUETES COMMANDS
        //

        /**
         * Récupère les commands dont l'id fait partie de la liste fournie.
         *
         * @param array $commands_ids = Tableau des id des commands voulus
         *
         * @return array : Retourne un tableau avec les commands adaptés
         */
        public function getCommandsIn($commands_ids)
        {
            $query = '
				SELECT *
				FROM command
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($commands_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params);
        }

        /**
         * Supprime tous les commands dont l'id fait partie du tableau fourni.
         *
         * @param $commands_ids : Tableau des id des commands à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteCommandsIn($commands_ids)
        {
            $query = '
				DELETE FROM command
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($commands_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES SCHEDULEDS_CONTACTS
        //

        /**
         * Retourne tous les contacts pour un sms programmé donnée.
         *
         * @param int   $id_sms       : L'id du sms
         * @param mixed $id_scheduled
         *
         * @return array : Tous les contacts compris dans le schedulede
         */
        public function getContactsForScheduled($id_scheduled)
        {
            $query = '
				SELECT con.id as id, con.name as name, con.number as number
				FROM scheduled_contact as s_c
				JOIN contact as con
				ON (s_c.id_contact = con.id)
				WHERE(s_c.id_scheduled = :id_scheduled)
			';

            $params = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_run_query($query, $params);
        }

        /**
         * Supprime tous les scheduleds_contacts pour un sms donné.
         *
         * @param int $id_scheduled : L'id du sms pour lequel on doit supprimer les scheduleds_contacts
         *
         * @return int Le nombre de lignes supprimées
         */
        public function deleteScheduleds_contactsForScheduled($id_scheduled)
        {
            $query = '
				DELETE FROM scheduled_contact
				WHERE id_scheduled = :id_scheduled
			';

            $params = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Change le statut des scheduleds dont l'id est fourni dans $scheduleds_id.
         *
         * @param array $scheduleds_ids = Tableau des id des sms voulus
         * @param mixed $progress
         *
         * @return int : Retourne le nombre de lignes mises à jour
         */
        public function updateProgressScheduledsIn($scheduleds_ids, $progress)
        {
            $query = '
				UPDATE scheduled
				SET progress = :progress
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($scheduleds_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];
            $params['progress'] = (bool) $progress;

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES SCHEDULEDS_NUMBERS
        //

        /**
         * Supprime tous les scheduleds_numbers pour un sms donné.
         *
         * @param int $id_scheduled : L'id du sms pour lequel on doit supprimer les scheduleds_numbers
         *
         * @return int Le nombre de lignes supprimées
         */
        public function deleteScheduleds_numbersForScheduled($id_scheduled)
        {
            $query = '
				DELETE FROM scheduled_number
				WHERE id_scheduled = :id_scheduled
			';

            $params = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Retourne tous les numéros pour un scheduled donné.
         *
         * @param int $id_scheduled : L'id du scheduled
         *
         * @return array : Tous les numéro compris dans le scheduled
         */
        public function getNumbersForScheduled($id_scheduled)
        {
            $query = '
				SELECT *
				FROM scheduled_number
				WHERE id_scheduled = :id_scheduled
			';

            $params = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_run_query($query, $params);
        }

        //
        // PARTIE DES REQUETES SCHEDULEDS_GROUPS
        //

        /**
         * Supprime tous les scheduleds_groups pour un sms donné.
         *
         * @param int $id_scheduled : L'id du sms pour lequel on doit supprimer les scheduleds_groups
         *
         * @return int Le nombre de lignes supprimées
         */
        public function deleteScheduleds_groupsForScheduled($id_scheduled)
        {
            $query = '
				DELETE FROM scheduled_group
				WHERE id_scheduled = :id_scheduled
			';

            $params = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Retourne tous les groups pour un scheduled donnée.
         *
         * @param int $id_scheduled : L'id du schedulede
         *
         * @return array : Tous les groups compris dans le scheduled
         */
        public function getGroupsForScheduled($id_scheduled)
        {
            $query = '
				SELECT gro.id as id, gro.name as name
				FROM scheduled_group as s_g
				JOIN group as gro
				ON (s_g.id_group = gro.id)
				WHERE(s_g.id_scheduled = :id_scheduled)
			';

            $params = [
                'id_scheduled' => $id_scheduled,
            ];

            return $this->_run_query($query, $params);
        }

        //
        // PARTIE DES REQUETES USERS
        //

        /**
         * Récupère un utilisateur à partir de son email.
         *
         * @param string $email = L'email de l'utilisateur
         *
         * @return array : Retourne l'utilisateur
         */
        public function getUserFromEmail($email)
        {
            $query = '
				SELECT *
				FROM user
				WHERE email = :email';

            $params = [
                'email' => $email,
            ];

            return $this->_run_query($query, $params, self::FETCH);
        }

        /**
         * Supprime tous les users dont l'id fait partie du tableau fourni.
         *
         * @param $users_ids : Tableau des id des users à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteUsersIn($users_ids)
        {
            $query = '
				DELETE FROM user
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($users_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES TRANSFERS
        //

        /**
         * Change le statut des tranfers dont l'id est fourni dans $transfers_id.
         *
         * @param array $transfers_ids = Tableau des id des transfers voulus
         * @param mixed $progress
         *
         * @return int : Retourne le nombre de lignes mises à jour
         */
        public function updateProgressTransfersIn($transfers_ids, $progress)
        {
            $query = '
				UPDATE transfer
				SET progress = :progress
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($transfers_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];
            $params['progress'] = (bool) $progress;

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Supprime tous les transfers dont l'id fait partie du tableau fourni.
         *
         * @param $transfers_ids : Tableau des id des transfers à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteTransfersIn($transfers_ids)
        {
            $query = '
				DELETE FROM transfer
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($transfers_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES EVENTS
        //

        /**
         * Supprime tous les events dont l'id fait partie du tableau fourni.
         *
         * @param $events_ids : Tableau des id des events à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteEventsIn($events_ids)
        {
            $query = '
				DELETE FROM event
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($events_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        //
        // PARTIE DES REQUETES SMS STOP
        //

        /**
         * Supprime tous les sms_stops dont l'id fait partie du tableau fourni.
         *
         * @param $sms_stops_ids : Tableau des id des sms_stops à supprimer
         *
         * @return int : Nombre de lignes supprimées
         */
        public function deleteSmsStopsIn($sms_stops_ids)
        {
            $query = '
				DELETE FROM sms_stop
				WHERE id ';

            //On génère la clause IN et les paramètres adaptés depuis le tableau des id
            $generted_in = $this->_generate_in_from_array($sms_stops_ids);
            $query .= $generted_in['QUERY'];
            $params = $generted_in['PARAMS'];

            return $this->_run_query($query, $params, self::ROWCOUNT);
        }
    }
