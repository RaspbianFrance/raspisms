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

    class Tool extends \descartes\InternalController
    {
        /**
         * Cette fonction parse un numéro pour le retourner sans espaces, etc.
         *
         * @param string $number : Le numéro de téléphone à parser
         *
         * @return mixed : Si le numéro est bien un numéro de téléphone, on retourne le numéro parsé. Sinon, on retourne faux
         */
        public static function parse_phone($number)
        {
            $number = preg_replace('#[^-0-9+]#', '', $number);
            if (preg_match('#^(0|\+[1-9]{1,3}|\+1\-[0-9]{3})[1-9][0-9]{8,10}$#', $number))
            {
                return $number;
            }

            return false;
        }

        /**
         * Cette fonction parse un numéro pour le retourner avec des espaces, etc.
         *
         * @param string $number : Le numéro de téléphone à parser
         *
         * @return mixed : Si le numéro est bien un numéro de téléphone, on retourne le numéro parsé. Sinon, on retourne faux
         */
        public static function phone_format($number)
        {
            $phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
            $phone_number_o = $phone_number_util->parse($number, null);

            return $phone_number_util->format($phone_number_o, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
        }

        /**
         * Cette fonction fait la correspondance entre un type d'evenement et une icone font awesome.
         *
         * @param string $type : Le type de l'évenement à analyser
         *
         * @return string : Le nom de l'icone à afficher (ex : fa-user)
         */
        public static function event_type_to_icon($type)
        {
            switch ($type) {
                case 'USER_ADD':
                    $logo = 'fa-user';

                    break;
                case 'CONTACT_ADD':
                    $logo = 'fa-user';

                    break;
                case 'GROUP_ADD':
                    $logo = 'fa-group';

                    break;
                case 'SCHEDULED_ADD':
                    $logo = 'fa-calendar';

                    break;
                case 'COMMAND_ADD':
                    $logo = 'fa-terminal';

                    break;
                default:
                    $logo = 'fa-question';
            }

            return $logo;
        }

        /**
         * Cette fonction vérifie une date.
         *
         * @param string $date   : La date a valider
         * @param string $format : Le format de la date
         *
         * @return bool : Vrai si la date et valide, faux sinon
         */
        public static function validate_date($date, $format)
        {
            $objectDate = \DateTime::createFromFormat($format, $date);

            return $objectDate && $objectDate->format($format) === $date;
        }

        /**
         * Cette fonction parse un texte, pour en extraire des données contenu dans des drapeaux au format [FLAG:contenu du drapeau].
         *
         * @param string $texte : Le texte à parser
         *
         * @return mixed : Tableau de la forme 'FLAG' => 'contenu du drapeau'. si on trouve une forme correcte (Le contenu de FLAG sera mis en majuscule automatiquement). Sinon le tableau vide.
         */
        public static function parse_for_flag($texte)
        {
            $returns = [];
            $results = [];
            while (preg_match('#\[(.*)(?<!\\\):(.*)(?<!\\\)\]#Uui', $texte, $results))
            { //Tant qu'on recuèpre un flag
                $returns[mb_strtoupper($results[1])] = $results[2];
                $texte = str_replace($results[0], '', $texte);
            }

            return $returns;
        }

        /**
         * Cette fonction retourne un mot de passe généré aléatoirement.
         *
         * @param int $length : Taille du mot de passe à générer
         *
         * @return string : Le mot de passe aléatoire
         */
        public static function generate_password($length)
        {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-@()?.:!%*$&/';
            $password = '';
            $chars_length = mb_strlen($chars) - 1;
            $i = 0;
            while ($i < $length)
            {
                ++$i;
                $password .= $chars[rand(0, $chars_length)];
            }

            return $password;
        }

        /**
         * Cette fonction vérifie si un utilisateur et connecté, et si il ne l'est pas, redirige sur la page de connexion.
         */
        public static function verifyconnect()
        {
            if (!isset($_SESSION['connect']) || !$_SESSION['connect'])
            {
                header('Location: /');
                die();
            }
        }

        /**
         * Check if the user is admin.
         *
         * @return bool : True if admin, False else
         */
        public static function is_admin()
        {
            return (bool) ($_SESSION['user']['admin'] ?? false);
        }

        /**
         * Cette fonction s'occupe d'envoyer les emails.
         *
         * @param string $to       : L'adresse mail à laquelle envoyer le mail
         * @param array  $settings : Les settings du mail, type, sujet, template
         * @param array  $datas    : Les données à fournir au template du mail
         */
        public static function send_email($to, $settings, $datas = [])
        {
            $controller = new \descartes\Controller();

            ob_start();
            $controller->render($settings['template'], $datas);
            $content = ob_get_clean();

            return mail($to, $settings['subject'], $content);
        }
    }
