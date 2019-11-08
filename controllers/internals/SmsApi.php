<?php
namespace controllers\publics;

    /**
     * Page des smsapis
     */
    class SmsAPI extends \descartes\Controller
    {
        private $internal_user;
        private $internal_scheduled;
        private $internal_contact;


        //On défini les constantes qui servent pour les retours d'API
        const API_ERROR_NO = 0;
        const API_ERROR_BAD_ID = 1;
        const API_ERROR_CREATION_FAILED = 2;
        const API_ERROR_MISSING_FIELD = 3;
        
        
        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté
         * @return void;
         */
        public function _before()
        {
            global $bdd;
            global $model;
            $this->bdd = $bdd;
            $this->model = $model;

            $this->internal_user = new \controllers\internals\User($this->bdd);
            $this->internal_scheduled = new \controllers\internals\Scheduled($this->bdd);
            $this->internal_contact = new \controllers\internals\Contact($this->bdd);

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Cette fonction permet d'envoyer un Sms, en passant simplement des arguments à l'URL (ou pas $_GET)
         * @param string text = Le contenu du Sms
         * @param mixed numbers = Les numéros auxquels envoyer les Sms. Soit un seul numéro, et il s'agit d'un string. Soit plusieurs numéros, et il s'agit d'un tableau
         * @param mixed contacts = Les noms des contacts auxquels envoyer les Sms. Soit un seul et il s'agit d'un string. Soit plusieurs, et il s'agit d'un tableau
         * @param mixed groupes = Les noms des groupes auxquels envoyer les Sms. Soit un seul et il s'agit d'un string. Soit plusieurs, et il s'agit d'un tableau
         * @param optionnal string date = La date à laquelle doit être envoyé le Sms. Au format 'Y-m-d H:i'. Si non fourni, le Sms sera envoyé dans 2 minutes
         */
        public function api()
        {
            //On récupère l'email et le password
            $email = isset($_GET['email']) ? $_GET['email'] : null;
            $email = isset($_POST['email']) ? $_POST['email'] : $email;
            $password = isset($_GET['password']) ? $_GET['password'] : null;
            $password = isset($_POST['password']) ? $_POST['password'] : $password;

            //Si les identifiants sont incorrect on retourne une erreur
            $user = $internal_user->check_credentials($email, $password);

            if (!$user) {
                echo json_encode(array(
                    'error' => self::API_ERROR_BAD_ID,
                ));
                return true;
            }

            //On map les variables $_GET
            $get_numbers = isset($_GET['numbers']) ? $_GET['numbers'] : array();
            $get_contacts = isset($_GET['contacts']) ? $_GET['contacts'] : array();
            $get_groupes = isset($_GET['groupes']) ? $_GET['groupes'] : array();
            
            //On map les variables POST
            $post_numbers = isset($_POST['numbers']) ? $_POST['numbers'] : array();
            $post_contacts = isset($_POST['contacts']) ? $_POST['contacts'] : array();
            $post_groupes = isset($_POST['groupes']) ? $_POST['groupes'] : array();

            //On map le texte et la date à part car c'est les seuls arguments qui ne sera jamais un tableau
            $text = isset($_GET['text']) ? $_GET['text'] : null;
            $text = isset($_POST['text']) ? $_POST['text'] : $text;
            $date = isset($_GET['date']) ? $_GET['date'] : null;
            $date = isset($_POST['date']) ? $_POST['date'] : $date;

            //On passe tous les paramètres GET en tableau
            $get_numbers = is_array($get_numbers) ? $get_numbers : ($get_numbers ? array($get_numbers) : array());
            $get_contacts = is_array($get_contacts) ? $get_contacts : array($get_contacts);
            $get_groupes = is_array($get_groupes) ? $get_groupes : array($get_groupes);

            //On passe tous les paramètres POST en tableau
            $post_numbers = is_array($post_numbers) ? $post_numbers : array($post_numbers);
            $post_contacts = is_array($post_contacts) ? $post_contacts : array($post_contacts);
            $post_groupes = is_array($post_groupes) ? $post_groupes : array($post_groupes);

            //On merge les données reçus en GET, et celles en POST
            $numbers = array_merge($get_numbers, $post_numbers);
            $contacts = array_merge($get_contacts, $post_contacts);
            $groupes = array_merge($get_groupes, $post_groupes);

            //Pour chaque contact, on récupère l'id du contact
            foreach ($contacts as $key => $contact) {
                if (!$contact = $internal_contact->get_by_name($contact)) {
                    unset($contacts[$key]);
                    continue;
                }

                $contacts[$key] = $contact['id'];
            }

            //Pour chaque groupe, on récupère l'id du groupe
            foreach ($groupes as $key => $name) {
                if (!$groupe = $internal_contact->get_by_name($groupe)) {
                    unset($groupes[$key]);
                    continue;
                }

                $groupes[$key] = $groupe['id'];
            }

            //Si la date n'est pas définie, on la met à la date du jour
            if (!$date) {
                $now = new \DateTime();
                $date = $now->format('Y-m-d H:i');
            }

            //Si il manque des champs essentiels, on leve une erreur
            if (!$text || (!$numbers && !$contacts && !$groupes)) {
                echo json_encode(array(
                    'error' => self::API_ERROR_MISSING_FIELD,
                ));
                return false;
            }

            //On assigne les variable POST (après avoir vidé $_POST) en prévision de la création du Sms
            if (!$this->internal_scheduled->create(['at' => $date, 'content' => $text], $numbers, $contacts, $groupes)) {
                echo json_encode(array(
                    'error' => self::API_ERROR_CREATION_FAILED,
                ));
                return false;
            }
            
            echo json_encode(array(
                'error' => self::API_ERROR_NO,
            ));
            return true;
        }
    }
