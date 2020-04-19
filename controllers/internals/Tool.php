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
     * Some tools frequently used.
     * Not a standard controller as it's not linked to a model in any way.
     */
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
            try
            {
                $phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
                $phone_number_o = $phone_number_util->parse($number, null);

                $valid = $phone_number_util->isValidNumber($phone_number_o);

                if (!$valid)
                {
                    return false;
                }

                return $phone_number_util->format($phone_number_o, \libphonenumber\PhoneNumberFormat::E164);
            }
            catch (\Exception $e)
            {
                return false;
            }
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
            try
            {
                $phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
                $phone_number_o = $phone_number_util->parse($number, null);

                return $phone_number_util->format($phone_number_o, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
            }
            catch (\Exception $e)
            {
                return false;
            }
        }

        /**
         * Format a number and make a link to a discussion with this number.
         *
         * @param string $number : Number to format and make a link for
         *
         * @return string : Link to the number
         */
        public static function phone_link($number)
        {
            $number_format = self::phone_format($number);
            $url = \descartes\Router::url('Discussion', 'show', ['number' => $number]);

            return '<a href="' . self::s($url, false, true, false) . '">' . self::s($number_format, false, true, false) . '</a>';
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
         * Check if the user connected.
         *
         * @return bool : True if connected, False else
         */
        public static function is_connected()
        {
            return (bool) ($_SESSION['connect'] ?? false);
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
         * Allow to read an uploaded file.
         *
         * @param array $file : The array extracted from $_FILES['file']
         *
         * @return array : ['success' => bool, 'content' => file handler | error message, 'error_code' => $file['error']]
         */
        public static function read_uploaded_file(array $file)
        {
            $result = [
                'success' => false,
                'content' => 'Une erreur inconnue est survenue.',
                'error_code' => $file['error'] ?? 99,
                'mime_type' => false,
            ];

            if (UPLOAD_ERR_OK !== $file['error'])
            {
                switch ($file['error'])
                {
                    case UPLOAD_ERR_INI_SIZE:
                        $result['content'] = 'Impossible de télécharger le fichier car il dépasse les ' . ini_get('upload_max_filesize') / (1000 * 1000) . ' Mégaoctets.';

                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $result['content'] = 'Le fichier dépasse la limite de taille.';

                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $result['content'] = 'L\'envoi du fichier a été interrompu.';

                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $result['content'] = 'Aucun fichier n\'a été envoyé.';

                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $result['content'] = 'Le serveur ne dispose pas de fichier temporaire permettant l\'envoi de fichiers.';

                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $result['content'] = 'Impossible d\'envoyer le fichier car il n\'y a plus de place sur le serveur.';

                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $result['content'] = 'Le serveur a interrompu l\'envoi du fichier.';

                        break;
                }

                return $result;
            }

            $tmp_filename = $file['tmp_name'] ?? false;
            if (!$tmp_filename || !is_readable($tmp_filename))
            {
                return $result;
            }

            $result['mime_type'] = 'text/plain' === mime_content_type($tmp_filename) ? $file['type'] : mime_content_type($tmp_filename);

            $file_handler = fopen($tmp_filename, 'r');
            $result['success'] = true;
            $result['content'] = $file_handler;

            return $result;
        }

        /**
         * Allow to upload file.
         *
         * @param array $file : The array extracted from $_FILES['file']
         *
         * @return array : ['success' => bool, 'content' => file path | error message, 'error_code' => $file['error']]
         */
        public static function upload_file(array $file)
        {
            $result = [
                'success' => false,
                'content' => 'Une erreur inconnue est survenue.',
                'error_code' => $file['error'] ?? 99,
            ];

            if (UPLOAD_ERR_OK !== $file['error'])
            {
                switch ($file['error'])
                {
                    case UPLOAD_ERR_INI_SIZE:
                        $result['content'] = 'Impossible de télécharger le fichier car il dépasse les ' . ini_get('upload_max_filesize') / (1000 * 1000) . ' Mégaoctets.';

                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $result['content'] = 'Le fichier dépasse la limite de taille.';

                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $result['content'] = 'L\'envoi du fichier a été interrompu.';

                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $result['content'] = 'Aucun fichier n\'a été envoyé.';

                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $result['content'] = 'Le serveur ne dispose pas de fichier temporaire permettant l\'envoi de fichiers.';

                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $result['content'] = 'Impossible d\'envoyer le fichier car il n\'y a plus de place sur le serveur.';

                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $result['content'] = 'Le serveur a interrompu l\'envoi du fichier.';

                        break;
                }

                return $result;
            }

            $tmp_filename = $file['tmp_name'] ?? false;
            if (!$tmp_filename || !is_readable($tmp_filename))
            {
                return $result;
            }

            $md5_filename = md5_file($tmp_filename);
            if (!$md5_filename)
            {
                return $result;
            }

            $new_file_path = PWD_DATAS . '/' . $md5_filename;

            if (file_exists($new_file_path))
            {
                $result['success'] = true;
                $result['content'] = $new_file_path;

                return $result;
            }

            $success = move_uploaded_file($tmp_filename, $new_file_path);
            if (!$success)
            {
                $result['content'] = 'Impossible d\'écrire le fichier sur le serveur.';

                return $result;
            }

            $result['success'] = true;
            $result['content'] = $new_file_path;

            return $result;
        }
    }
