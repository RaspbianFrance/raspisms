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
         * @return array : ['success' => bool, 'content' => file handler | error message, 'error_code' => $file['error']]
         */
        public static function read_uploaded_file(array $file)
        {
            $result = [
                'success' => false,
                'content' => 'Une erreur inconnue est survenue.',
                'error_code' => $file['error'] ?? 99,
                'mime_type' => false,
                'extension' => false,
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
         * Allow to save an uploaded file from the $_FILE['file'] array
         *
         * @param array $file : The array extracted from $_FILES['file']
         * @param string $dirpath : The directory to save the file in
         * @param bool $override : If true, override the file if another file with this name exists 
         * @param ?string $filename : The name to use for the file, if null use a highly random name
         * @param ?string $extension : The extension to use for the file, if null try to determine it using original file extension, then mime_type
         * @param bool $use_mimetype : If true, ignore original file extension to determine final file extension and use file real mimetype instead
         *
         * @return array : ['success' => bool, 'content' => new file name | error message, 'error_code' => $file['error']]
         */
        public static function save_uploaded_file(array $file, string $dirpath, bool $override = false, ?string $filename = null, ?string $extension = null, bool $use_mimetype = false)
        {
            $result = [
                'success' => false,
                'content' => 'Une erreur inconnue est survenue.',
                'error_code' => $file['error'] ?? 99,
            ];

            $upload_info = self::read_uploaded_file($file);
            if (!$upload_info['success'])
            {
                $result['content'] = $upload_info['content'];
                return $result;
            }
            
            if ($extension === null)
            {
                $extension = $upload_info['extension'];
                if ($extension === '' || $use_mimetype)
                {
                    $mimey = new \Mimey\MimeTypes;
                    $extension = $mimey->getExtension($upload_info['mime_type']);
                }
            }

            if ($filename === null)
            {
                $filename = self::random_uuid();
            }

            $filename = $filename . '.' . $extension;
            $filepath = $dirpath . '/' . $filename;

            if (file_exists($filepath) && !$override)
            {
                $result['content'] = 'Le fichier ' . $filepath . ' existe déjà.';

                return $result;
            }

            $success = move_uploaded_file($upload_info['tmp_name'], $filepath);
            if (!$success)
            {
                $result['content'] = 'Impossible de délplacer le fichier vers ' . $filepath;

                return $result;
            }

            $result['success'] = true;
            $result['content'] = $filename;

            return $result;
        }
        
        
        /**
         * Generate a highly random uuid based on timestamp and strong cryptographic random
         *
         * @return string
         */
        public static function random_uuid()
        {
            $bytes = random_bytes(16);
            return time() . '-' . bin2hex($bytes);
        }
    }
