<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\publics;

    class Templating extends \descartes\Controller
    {
        private $internal_contact;
        private $internal_templating;

        /**
         * Cette fonction est appelée avant toute les autres :
         * Elle vérifie que l'utilisateur est bien connecté.
         *
         * @return void;
         */
        public function __construct()
        {
            $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);
            $this->internal_contact = new \controllers\internals\Contact($bdd);
            $this->internal_templating = new \controllers\internals\Templating();

            \controllers\internals\Tool::verifyconnect();
        }

        /**
         * Try to render a template as a message for preview.
         *
         * @param string $_POST['template']   : Template string
         * @param int    $_POST['id_contact'] : Id of the contact to render the template for
         *
         * @return mixed : False or json string ['success' => bool, 'result' => message]
         */
        public function render_preview()
        {
            $return = [
                'success' => false,
                'result' => 'Une erreur inconnue est survenue.',
            ];

            $template = $_POST['template'] ?? false;
            $id_contact = $_POST['id_contact'] ?? false;

            if (!$template || !$id_contact)
            {
                $return['result'] = 'Veuillez remplir un message.';
                echo json_encode($return);

                return false;
            }

            $contact = $this->internal_contact->get_for_user($_SESSION['user']['id'], $id_contact);
            if (!$contact)
            {
                $return['result'] = 'Ce contact n\'existe pas.';
                echo json_encode($return);

                return false;
            }

            $contact['datas'] = json_decode($contact['datas'], true);

            $datas = [
                'contact' => $contact['datas'],
            ];

            $result = $this->internal_templating->render($template, $datas);
            $return = $result;
            if (!trim($result['result']))
            {
                $return['result'] = 'Message vide, il ne sera pas envoyé.';
            }

            echo json_encode($return);

            return true;
        }
    }
