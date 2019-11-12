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
 * Classe des receivedes.
 */
class Received extends \descartes\InternalController
{
    private $model_received;
    private $internal_phone;

    public function __construct(\PDO $bdd)
    {
        $this->model_received = new \models\Received($bdd);
        $this->internal_phone = new \controllers\internals\Phone($bdd);
    }

    /**
     * Cette fonction retourne une liste des receivedes sous forme d'un tableau.
     * @param int $id_user : user id
     * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
     * @param mixed(int|bool) $page     : Le numéro de page en cours
     *
     * @return array : La liste des receivedes
     */
    public function list($id_user, $nb_entry = null, $page = null)
    {
        //Recupération des receivedes
        $allowed_destinations = $this->internal_phone->gets_for_user($id_user);

        foreach ($allowed_destinations as &$allowed_destination)
        {
            $allowed_destination = $allowed_destination['number'];
        }
        
        return $this->model_received->list_for_destinations($allowed_destinations, $nb_entry, $nb_entry * $page);
    }


    /**
     * Return a received sms
     * @param $id : received id
     * @return array
     */
    public function get($id)
    {
        return $this->model_received->get($id);
    }


    /**
     * Cette fonction retourne une liste des receivedes sous forme d'un tableau.
     *
     * @param array int $ids : Les ids des entrées à retourner
     *
     * @return array : La liste des receivedes
     */
    public function gets($ids)
    {
        //Recupération des receivedes
        return $this->model_received->gets($ids);
    }

    /**
     * Cette fonction retourne les X dernières entrées triées par date for a user.
     * @param int $id_user
     * @param int $nb_entry : Nombre d'entrée à retourner ou faux pour tout
     * @return array : Les dernières entrées
     */
    public function get_lasts_for_user_by_date($id_user, $nb_entry)
    {
        return $this->model_received->get_lasts_for_user_by_date($id_user, $nb_entry);
    }

    /**
     * Cette fonction retourne une liste des receiveds sous forme d'un tableau.
     *
     * @param string $origin : Le numéro depuis lequel est envoyé le message
     *
     * @return array : La liste des receiveds
     */
    public function get_by_origin($origin)
    {
        return $this->model_received->get_by_origin($origin);
    }

    /**
     * Récupère les Sms reçus depuis une date.
     * @param $date : La date depuis laquelle on veux les Sms (au format 2014-10-25 20:10:05)
     * @param int $id_user : User id
     * @return array : Tableau avec tous les Sms depuis la date
     */
    public function get_since_by_date_for_user($date, $id_user)
    {
        return $this->model_received->get_since_by_date_for_user($date, $id_user);
    }

    /**
     * Récupère les Sms reçus depuis une date pour un numero.
     *
     * @param $date : La date depuis laquelle on veux les Sms (au format 2014-10-25 20:10:05)
     * @param $number : Le numéro
     *
     * @return array : Tableau avec tous les Sms depuis la date
     */
    public function get_since_for_number_by_date($date, $number)
    {
        return $this->model_received->get_since_for_number_by_date($date, $number);
    }

    /**
     * Cette fonction va supprimer une liste de receiveds.
     *
     * @param array $ids : Les id des receivedes à supprimer
     * @param mixed $id
     *
     * @return int : Le nombre de receivedes supprimées;
     */
    public function delete($id)
    {
        return $this->model_received->delete($id);
    }

    /**
     * Cette fonction insert une nouvelle receivede.
     *
     * @param array $received : Un tableau représentant la receivede à insérer
     * @param mixed $at
     * @param mixed $origin
     * @param mixed $content
     * @param mixed $command
     *
     * @return mixed bool|int : false si echec, sinon l'id de la nouvelle receivede insérée
     */
    public function create($at, $origin, $content, $command)
    {
        $received = [
            'at' => $at,
            'origin' => $origin,
            'content' => $content,
            'command' => $command,
        ];

        return $this->model_received->create($received);
    }

    /**
     * Cette fonction met à jour une série de receivedes.
     *
     * @param mixed $id
     * @param mixed $at
     * @param mixed $origin
     * @param mixed $content
     * @param mixed $command
     *
     * @return int : le nombre de ligne modifiées
     */
    public function update($id, $at, $origin, $content, $command)
    {
        $received = [
            'at' => $at,
            'origin' => $origin,
            'content' => $content,
            'command' => $command,
        ];

        return $this->model_received->update($id, $received);
    }

    /**
     * Count number of received sms for user
     * @param int $id_user : user id
     * @return int : Number of received SMS for user
     */
    public function count($id_user)
    {
        return $this->model_received->count($id_user);
    }

    /**
     * Cette fonction compte le nombre de receiveds par jour depuis une date.
     *
     * @param int $id_user : user id
     * @param mixed $date
     *
     * @return array : un tableau avec en clef la date et en valeure le nombre de sms envoyés
     */
    public function count_for_user_by_day_since($id_user, $date)
    {
        $counts_by_day = $this->model_received->count_for_user_by_day_since($id_user, $date);
        $return = [];

        foreach ($counts_by_day as $count_by_day)
        {
            $return[$count_by_day['at_ymd']] = $count_by_day['nb'];
        }

        return $return;
    }

    /**
     * Cette fonction retourne les discussions avec un numéro.
     *
     * @return array : Un tableau avec la date de l'échange et le numéro de la personne
     */
    public function get_discussions()
    {
        return $this->model_received->get_discussions();
    }
}
