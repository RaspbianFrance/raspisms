<?php
namespace controllers\internals;
/**
 * Classe des receivedes
 */
class Received extends \InternalController
{

    /**
     * Cette fonction retourne une liste des receivedes sous forme d'un tableau
     * @param mixed(int|bool) $nb_entry : Le nombre d'entrées à retourner par page
     * @param mixed(int|bool) $page : Le numéro de page en cours
     * @return array : La liste des receivedes
     */	
    public function get_list ($nb_entry = false, $page = false)
    {
        //Recupération des receivedes
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->get_list($nb_entry, $nb_entry * $page);
    }
    

    /**
     * Cette fonction retourne une liste des receivedes sous forme d'un tableau
     * @param array int $ids : Les ids des entrées à retourner
     * @return array : La liste des receivedes
     */	
    public function get_by_ids ($ids)
    {
        //Recupération des receivedes
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->get_by_ids($ids);
    }
    
    /**
     * Cette fonction retourne les X dernières entrées triées par date
     * @param mixed false|int $nb_entry : Nombre d'entrée à retourner ou faux pour tout
     * @return array : Les dernières entrées
     */
    public function get_lasts_by_date ($nb_entry = false)
    {
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->get_lasts_by_date($nb_entry);
    }
    
    /**
     * Cette fonction retourne une liste des receiveds sous forme d'un tableau
     * @param string $origin : Le numéro depuis lequel est envoyé le message
     * @return array : La liste des receiveds
     */	
    public function get_by_origin ($origin)
    {
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->get_by_origin($origin);
    }

    /**
     * Récupère les SMS reçus depuis une date
     * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
     * @return array : Tableau avec tous les SMS depuis la date
     */
    public function get_since_by_date ($date)
    {
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->get_since_by_date($date, $number);
    }

    /**
     * Récupère les SMS reçus depuis une date pour un numero
     * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
     * @param $number : Le numéro
     * @return array : Tableau avec tous les SMS depuis la date
     */
    public function get_since_for_number_by_date ($date, $number)
    {
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->get_since_for_number_by_date($date, $number);
    }

    /**
     * Cette fonction va supprimer une liste de receiveds
     * @param array $ids : Les id des receivedes à supprimer
     * @return int : Le nombre de receivedes supprimées;
     */
    public function delete ($id)
    {
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->delete_by_id($id);
    }

    /**
     * Cette fonction insert une nouvelle receivede
     * @param array $received : Un tableau représentant la receivede à insérer
     * @return mixed bool|int : false si echec, sinon l'id de la nouvelle receivede insérée
     */
    public function create ($at, $origin, $content, $is_command)
    {
        $received = [
            'at' => $at,
            'origin' => $origin,
            'content' => $content,
            'is_command' => $is_command,
        ];

        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->create($received);
    }

    /**
     * Cette fonction met à jour une série de receivedes
     * @return int : le nombre de ligne modifiées
     */
    public function update ($id, $at, $origin, $content, $is_command)
    {
        $modelReceived = new \models\Received($this->bdd);
        
        $received = [
            'at' => $at,
            'origin' => $origin,
            'content' => $content,
            'is_command' => $is_command,
        ];

        return $modelReceived->update($id, $received);
    }

    /**
     * Cette fonction permet de compter le nombre de receiveds
     * @return int : Le nombre d'entrées dans la table
     */
    public function count ()
    {
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->count();
    }

    /**
     * Cette fonction compte le nombre de receiveds par jour depuis une date
     * @return array : un tableau avec en clef la date et en valeure le nombre de sms envoyés
     */
    public function count_by_day_since ($date)
    {
        $modelReceived = new \models\Received($this->bdd);

        $counts_by_day = $modelReceived->count_by_day_since($date);
        $return = [];
        
        foreach ($counts_by_day as $count_by_day)
        {
            $return[$count_by_day['at_ymd']] = $count_by_day['nb'];
        }

        return $return;
    }
    
    /**
     * Cette fonction retourne les discussions avec un numéro
     * @return array : Un tableau avec la date de l'échange et le numéro de la personne
     */
    public function get_discussions ()
    {
        $modelReceived = new \models\Received($this->bdd);
        return $modelReceived->get_discussions();
    }
}
