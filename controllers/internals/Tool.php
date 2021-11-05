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

                case 'CONDITIONAL_GROUP_ADD':
                    $logo = 'fa-bullseye';

                    break;

                case 'SCHEDULED_ADD':
                    $logo = 'fa-calendar';

                    break;

                case 'COMMAND_ADD':
                    $logo = 'fa-terminal';

                    break;

                case 'QUOTA_LIMIT_CLOSE':
                    $logo = 'fa-exclamation';

                    break;

                case 'QUOTA_LIMIT_REACHED':
                    $logo = 'fa-exclamation-triangle';

                    break;

                case 'QUOTA_RENEWAL':
                    $logo = 'fa-retweet';

                    break;

                case 'QUOTA_CONSUME':
                    $logo = 'fa-euro';

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
         * Check if a sting represent a valid PHP period for creating an interval.
         *
         * @param string $period : Period string to check
         *
         * @return bool : True if valid period, false else
         */
        public static function validate_period($period)
        {
            try
            {
                $interval = new \DateInterval($period);
            }
            catch (\Throwable $e)
            {
                return false;
            }

            return true;
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

                exit();
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
         * @return array : ['success' => bool, 'content' => file handler | error message, 'error_code' => $file['error'], 'mime_type' => server side calculated mimetype, 'extension' => original extension, 'tmp_name' => name of the tmp_file]
         */
        public static function read_uploaded_file(array $file)
        {
            $result = [
                'success' => false,
                'content' => 'Une erreur inconnue est survenue.',
                'error_code' => $file['error'] ?? 99,
                'mime_type' => null,
                'extension' => null,
                'tmp_name' => null,
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

            $result['tmp_name'] = $tmp_filename;
            $result['extension'] = pathinfo($file['name'], PATHINFO_EXTENSION);
            $result['mime_type'] = mime_content_type($tmp_filename);

            $file_handler = fopen($tmp_filename, 'r');
            $result['success'] = true;
            $result['content'] = $file_handler;

            return $result;
        }

        /**
         * Generate a highly random uuid based on timestamp and strong cryptographic random.
         *
         * @return string
         */
        public static function random_uuid()
        {
            $bytes = random_bytes(16);

            return time() . '-' . bin2hex($bytes);
        }

        /**
         * Create a user data public path.
         *
         * @param int $id_user : The user id
         *
         * @return string : The created path
         *
         * @exception Raise exception on error
         */
        public static function create_user_public_path(int $id_user)
        {
            $new_dir = PWD_DATA_PUBLIC . '/' . $id_user;
            if (file_exists($new_dir))
            {
                return $new_dir;
            }

            clearstatcache();
            if (!mkdir($new_dir))
            {
                throw new \Exception('Cannot create dir ' . $new_dir);
            }

            //We do chmod in two times because else umask fuck mkdir permissions
            if (!chmod($new_dir, fileperms(PWD_DATA_PUBLIC) & 0777))
            { //Fileperms return garbage in addition to perms. Perms are only in weak bytes. We must use an octet notation with 0
                throw new \Exception('Cannot give dir ' . $new_dir . ' rights : ' . decoct(fileperms(PWD_DATA_PUBLIC) & 0777)); //Show error in dec
            }

            if (0 === posix_getuid() && !chown($new_dir, fileowner(PWD_DATA_PUBLIC)))
            { //If we are root, try to give the file to a proper user
                throw new \Exception('Cannot give dir ' . $new_dir . ' to user : ' . fileowner(PWD_DATA_PUBLIC));
            }

            if (0 === posix_getuid() && !chgrp($new_dir, filegroup(PWD_DATA_PUBLIC)))
            { //If we are root, try to give the file to a proper group
                throw new \Exception('Cannot give dir ' . $new_dir . ' to group : ' . filegroup(PWD_DATA_PUBLIC));
            }

            return $new_dir;
        }

        /**
         * Forge back an url parsed with PHP parse_url function
         * 
         * @param array $parsed_url : Parsed url returned by parse_url function
         * @return string : The url as a string
         */
        public static function unparse_url(array $parsed_url)
        {
            $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
            $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
            $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
            $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
            $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
            $pass     = ($user || $pass) ? "$pass@" : '';
            $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
            $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
            return "$scheme$user$pass$host$port$path$query$fragment";
        } 
    }
