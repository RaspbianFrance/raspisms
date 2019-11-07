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

namespace controllers\internals;

class Console extends \descartes\InternalController
{
    private $model_command;
    private $model_sended;
    private $model_smsstop;
    private $model_received;
    private $model_scheduled;
    private $model_user;
    private $internal_contact;
    private $internal_command;
    private $internal_database;
    private $internal_sended;
    private $internal_sms_stop;
    private $internal_received;
    private $internal_scheduled;
    private $internal_user;
    private $internal_transfer;

    private $internal_event;

    public function __construct()
    {
        $bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

        $this->model_command = new \models\Command($bdd);
        $this->model_sended = new \models\Sended($bdd);
        $this->model_smsstop = new \models\SmsStop($bdd);
        $this->model_received = new \models\Received($bdd);
        $this->model_user = new \models\User($bdd);

        $this->internal_event = new \controllers\internals\Event($bdd);
    }

    /**
     * Cette fonction envoie tous les Sms programmés qui doivent l'être.
     */
    public function sendScheduled()
    {
        //On créé l'objet de base de données
        for ($i = 0; $i < 30; ++$i)
        {
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');

            echo "Début de l'envoi des Sms programmés\n";

            $scheduleds = $this->model_scheduled->get_scheduleds_not_in_progress_before($now);

            $ids_scheduleds = [];

            //On passe en cours de progression tous les Sms
            foreach ($scheduleds as $scheduled)
            {
                $ids_scheduleds[] = $scheduled['id'];
            }

            if (!count($ids_scheduleds))
            {
                continue;
            }

            echo \count($ids_scheduleds)." Sms à envoyer ont été trouvés et ajoutés à la liste des Sms en cours d'envoi.\n";

            foreach ($ids_scheduleds as $ids_scheduled)
            {
                $this->internal_scheduled->update_progress($id_scheduled, true);
            }

            //Pour chaque Sms à envoyer
            foreach ($scheduleds as $scheduled)
            {
                $id_scheduled = $scheduled['id'];
                $text_sms = escapeshellarg($scheduled['content']);
                $flash = $scheduled['flash'];

                //On initialise les numéros auxquelles envoyer le Sms
                $numbers = [];

                //On récupère les numéros pour le Sms et on les ajoute
                $target_numbers = $this->internal_scheduled->get_numbers($id_scheduled);
                foreach ($target_numbers as $target_number)
                {
                    $numbers[] = $target_number['number'];
                }

                //On récupère les contacts, et on ajoute les numéros
                $contacts = $this->internal_scheduled->get_contacts($id_scheduled);
                foreach ($contacts as $contact)
                {
                    $numbers[] = $contact['number'];
                }

                //On récupère les groups
                $groups = $this->internal_scheduled->get_groups($id_scheduled);
                foreach ($groups as $group)
                {
                    //On récupère les contacts du group et on les ajoute aux numéros
                    $contacts = $this->internal_contact->get_by_group($group['id']);
                    foreach ($contacts as $contact)
                    {
                        $numbers[] = $contact['number'];
                    }
                }

                $smsStops = $this->internal_sms_stop->get_all();

                foreach ($numbers as $number)
                {
                    //Si les Sms STOP sont activés, on passe au numéro suivant si le numéro actuelle fait parti des Sms STOP
                    if (RASPISMS_SETTINGS_SMSSTOPS)
                    {
                        foreach ($smsStops as $smsStop)
                        {
                            if (!($number === $smsStop['number']))
                            {
                                continue;
                            }

                            echo 'Un Sms destiné au '.$number." a été bloqué par Sms STOP\n";

                            continue 2; //On passe au numéro suivant !
                        }
                    }

                    echo "	Envoi d'un Sms au ".$number."\n";
                    //On ajoute le Sms aux Sms envoyés
                    //Pour plus de précision, on remet la date à jour en réinstanciant l'objet \DateTime (et on reformatte la date, bien entendu)
                    $now = new \DateTime();
                    $now = $now->format('Y-m-d H:i:s');

                    //On peut maintenant ajouter le Sms
                    if (!$id_sended = $this->model_sended->insert(['at' => $now, 'target' => $number, 'content' => $scheduled['content'], 'before_delivered' => ceil(mb_strlen($scheduled['content']) / 160)]))
                    {
                        echo 'Impossible d\'inserer le sms pour le numero '.$number."\n";
                    }

                    //Commande qui envoie le Sms
                    $commande_send_sms = 'gammu-smsd-inject TEXT '.escapeshellarg($number).' -report -len '.mb_strlen($text_sms).' -text '.$text_sms;

                    if (RASPISMS_SETTINGS_SMS_FLASH && $flash)
                    {
                        $commande_send_sms .= ' -flash';
                    }

                    //Commande qui s'assure de passer le Sms dans ceux envoyés, et de lui donner le bon statut

                    //On va liée les deux commandes pour envoyer le Sms puis le passer en echec
                    $commande = '('.$commande_send_sms.') >/dev/null 2>/dev/null &';
                    exec($commande); //On execute la commande d'envoie d'un Sms
                }
            }

            echo "Tous les Sms sont en cours d'envoi.\n";
            //Tous les Sms ont été envoyés.
            foreach ($ids_scheduleds as $id_scheduled)
            {
                $this->model_scheduled->delete($id_scheduled);
            }

            //On dors 2 secondes
            sleep(2);
        }
    }

    /**
     * Cette fonction reçoit un Sms, et l'enregistre, en essayant dde trouver une commande au passage.
     */
    public function parseReceivedSms()
    {
        //On créer l'objet de base de données
        for ($i = 0; $i < 30; ++$i)
        {
            foreach (scandir(PWD_RECEIVEDS) as $dir)
            {
                //Si le fichier est un fichier système, on passe à l'itération suivante
                if (mb_substr($dir, 0, 1) == '.')
                {
                    continue;
                }

                echo 'Analyse du Sms ' . $dir . "\n";

                //On récupère la date du Sms à la seconde près grâce au nom du fichier (Cf. parseSms.sh)
                //Il faut mettre la date au format Y-m-d H:i:s
                $date = mb_substr($dir, 0, 4).'-'.mb_substr($dir, 4, 2).'-'.mb_substr($dir, 6, 2).' '.mb_substr($dir, 8, 2).':'.mb_substr($dir, 10, 2).':'.mb_substr($dir, 12, 2);

                //On récupère le fichier, et on récupère la chaine jusqu'au premier ':' pour le numéro de téléphone source, et la fin pour le message
                $content_file = file_get_contents(PWD_RECEIVEDS.$dir);

                //Si on peux pas ouvrir le fichier, on quitte en logant une erreur
                if (false === $content_file)
                {
                    error_log('Unable to read file "'.$dir);
                    die(4);
                }

                //On supprime le fichier. Si on n'y arrive pas, alors on log
                if (!unlink(PWD_RECEIVEDS.$dir))
                {
                    error_log('Unable to delete file "'.$dir);
                    die(8);
                }

                $content_file = explode(':', $content_file, 2);

                //Si on a pas passé de numéro ou de message, alors on lève une erreur
                if (!isset($content_file[0], $content_file[1]))
                {
                    error_log('Missing params in file "'.$dir);
                    die(5);
                }

                $number = $content_file[0];
                $number = \controllers\internals\Tool::parse_phone($number);
                $text = $content_file[1];

                //On gère les Sms STOP
                if ('STOP' === trim($text))
                {
                    echo 'STOP Sms detected '.$number."\n";
                    error_log('STOP Sms detected '.$number);
                    $this->model_smsstop->insert(['number' => $number]);

                    continue;
                }

                //On gère les accusés de reception
                if ('Delivered' === trim($text) || 'Failed' === trim($text))
                {
                    echo 'Delivered or Failed Sms for '.$number."\n";
                    error_log('Delivered or Failed Sms for '.$number);

                    //On récupère les Sms pas encore validé, uniquement sur les dernières 12h
                    $now = new \DateTime();
                    $interval = new \DateInterval('PT12H');
                    $sinceDate = $now->sub($interval)->format('Y-m-d H:i:s');

                    if (!$sendeds = $this->model_sended->_select('sendeds', ['target' => $number, 'delivered' => false, 'failed' => false, '>at' => $sinceDate], 'at', false, 1))
                    {
                        continue;
                    }

                    $sended = $sendeds[0];

                    //On gère les echecs
                    if ('Failed' === trim($text))
                    {
                        $this->model_sended->update($sended['id'], ['before_delivered' => 0, 'failed' => true]);
                        echo 'Sended Sms id '.$sended['id']." pass to failed status\n";

                        continue;
                    }

                    //On gère le cas des messages de plus de 160 caractères, lesquels impliquent plusieurs accusés
                    if ($sended['before_delivered'] > 1)
                    {
                        $this->internal_sended->decrement_before_delivered($sended['id']);
                        echo 'Sended Sms id '.$sended['id']." before_delivered decrement\n";

                        continue;
                    }

                    //Si tout est bon, que nous avons assez d'accusés, nous validons !
                    $this->internal_sended->set_delivered($sended['id']);
                    echo 'Sended Sms id '.$sended['id']." to delivered status\n";

                    continue;
                }

                if (!$number)
                {
                    error_log('Invalid phone number in file "'.$dir);
                    die(6);
                }

                //On va vérifier si on a reçu une commande, et des identifiants
                $flags = \controllers\internals\Tool::parse_for_flag($text);

                //On créer le tableau qui permettra de stocker les commandes trouvées
                $found_commands = [];

                //Si on reçu des identifiants
                if (\array_key_exists('LOGIN', $flags) && \array_key_exists('PASSWORD', $flags))
                {
                    $user = $this->internal_user->check_credentials($flags['LOGIN'], $flags['PASSWORD']);

                    error_log('We found '.\count($user).' users');
                    if ($user)
                    {
                        error_log('Password is valid');

                        //On va passer en revue toutes les commandes, pour voir si on en trouve dans ce message
                        $commands = $this->internal_command->get_all();

                        error_log('We found '.\count($commands).' commands');
                        foreach ($commands as $command)
                        {
                            $command_name = mb_strtoupper($command['name']);
                            if (\array_key_exists($command_name, $flags))
                            {
                                error_log('We found command '.$command_name);

                                //Si la commande ne nécessite pas d'être admin, ou si on est admin
                                if (!$command['admin'] || $user['admin'])
                                {
                                    error_log('And the count is ok');
                                    $found_commands[$command_name] = PWD_SCRIPTS.$command['script'].escapeshellcmd($flags[$command_name]);
                                }
                            }
                        }
                    }
                }

                //On va supprimer le mot de passe du Sms pour pouvoir l'enregistrer sans danger
                if (isset($flags['PASSWORD']))
                {
                    $text = str_replace($flags['PASSWORD'], '*****', $text);
                }

                //On map les données et on créer le Sms reçu
                $send_by = $number;
                $content = $text;
                $is_command = \count($found_commands);
                if ($id_received = $this->internal_received->create($date, $send_by, $content, $is_command))
                {
                    echo "Erreur lors de l'enregistrement du Sms\n";
                    error_log('Unable to process the Sms in file "'.$dir);
                    die(7);
                }

                //On insert le Sms dans le tableau des sms à envoyer par mail
                $this->internal_transfer->create($id_received);

                //Chaque commande sera executée.
                foreach ($found_commands as $command_name => $command)
                {
                    echo 'Execution de la commande : '.$command_name.' :: '.$command."\n";
                    exec($command);
                }
            }

            //On attend 2 secondes
            sleep(2);
        }
    }

    /**
     * Cette fonction permet d'envoyer par mail les sms à transférer.
     */
    public function sendTransfers()
    {
        if (!RASPISMS_SETTINGS_TRANSFER)
        {
            echo "Le transfer de Sms est désactivé ! \n";

            return false;
        }

        $users = $this->internal_user->gets_by_transfer(true);
        $transfers = $this->internal_transfer->get_not_in_progress();

        foreach ($transfers as $transfer)
        {
            $this->internal_transfer->update($transfer['id'], $transfer['id_received'], true);

            $received = $this->internal_received->get($transfer['id_received']);
            if (!$received)
            {
                continue;
            }

            foreach ($users as $user)
            {
                echo "Transfer d'un Sms du ".$received['send_by']." à l'email ".$user['email'];

                $to = $user['email'];
                $subject = '[RaspiSms] - Transfert d\'un Sms du '.$received['send_by'];
                $message = 'Le numéro '.$received['send_by']." vous a envoyé un Sms : \n".$received['content'];

                $success = mail($to, $subject, $message);
                echo ' ... '.($success ? 'ok' : 'ko')."\n";
            }

            $this->internal_transfer->delete($transfer['id']);
        }
    }
}
