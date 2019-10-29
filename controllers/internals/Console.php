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
    /**
     * Cette fonction envoie tous les Sms programmés qui doivent l'être.
     */
    public function sendScheduled()
    {
        //On créé l'objet de base de données
        global $db;

        for ($i = 0; $i < 30; ++$i)
        {
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');

            echo "Début de l'envoi des Sms programmés\n";

            $scheduleds = $db->getScheduledNotInProgressBefore($now);

            $ids_scheduleds = [];

            //On passe en cours de progression tous les Sms
            foreach ($scheduleds as $scheduled)
            {
                $ids_scheduleds[] = $scheduled['id'];
            }

            echo \count($ids_scheduleds)." Sms à envoyer ont été trouvés et ajoutés à la liste des Sms en cours d'envoi.\n";

            $db->updateProgressScheduledIn($ids_scheduleds, true);

            //Pour chaque Sms à envoyer
            foreach ($scheduleds as $scheduled)
            {
                $id_scheduled = $scheduled['id'];
                $text_sms = escapeshellarg($scheduled['content']);
                $flash = $scheduled['flash'];

                //On initialise les numéros auxquelles envoyer le Sms
                $numbers = [];

                //On récupère les numéros pour le Sms et on les ajoute
                $target_numbers = $db->getNumbersForScheduled($id_scheduled);
                foreach ($target_numbers as $target_number)
                {
                    $numbers[] = $target_number['number'];
                }

                //On récupère les contacts, et on ajoute les numéros
                $contacts = $db->getContactForScheduled($id_scheduled);
                foreach ($contacts as $contact)
                {
                    $numbers[] = $contact['number'];
                }

                //On récupère les groups
                $groups = $db->getGroupForScheduled($id_scheduled);
                foreach ($groups as $group)
                {
                    //On récupère les contacts du group et on les ajoute aux numéros
                    $contacts = $db->getContactForGroup($group['id']);
                    foreach ($contacts as $contact)
                    {
                        $numbers[] = $contact['number'];
                    }
                }

                $smsStops = $db->getFromTableWhere('smsstop');

                foreach ($numbers as $number)
                {
                    //Si les Sms STOP sont activés, on passe au numéro suivant si le numéro actuelle fait parti des Sms STOP
                    if (RASPISms_SETTINGS_SmsSTOPS)
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
                    if (!$db->insertIntoTable('sendeds', ['at' => $now, 'target' => $number, 'content' => $scheduled['content'], 'before_delivered' => ceil(mb_strlen($scheduled['content']) / 160)]))
                    {
                        echo 'Impossible d\'inserer le sms pour le numero '.$number."\n";
                    }

                    $id_sended = $db->lastId();

                    //Commande qui envoie le Sms
                    $commande_send_sms = 'gammu-smsd-inject TEXT '.escapeshellarg($number).' -report -len '.mb_strlen($text_sms).' -text '.$text_sms;

                    if (RASPISms_SETTINGS_Sms_FLASH && $flash)
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
            $db->deleteScheduledIn($ids_scheduleds);

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
        global $db;

        for ($i = 0; $i < 30; ++$i)
        {
            foreach (scandir(PWD_RECEIVEDS) as $dir)
            {
                //Si le fichier est un fichier système, on passe à l'itération suivante
                if ('.' === $dir || '..' === $dir || '.tokeep' === $dir)
                {
                    continue;
                }

                echo 'Analyse du Sms '.$dir."\n";

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
                    $db->insertIntoTable('smsstop', ['number' => $number]);

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

                    if (!$sendeds = $db->getFromTableWhere('sendeds', ['target' => $number, 'delivered' => false, 'failed' => false, '>at' => $sinceDate], 'at', false, 1))
                    {
                        continue;
                    }

                    $sended = $sendeds[0];

                    //On gère les echecs
                    if ('Failed' === trim($text))
                    {
                        $db->updateTableWhere('sendeds', ['before_delivered' => 0, 'failed' => true], ['id' => $sended['id']]);
                        echo 'Sended Sms id '.$sended['id']." pass to failed status\n";

                        continue;
                    }

                    //On gère le cas des messages de plus de 160 caractères, lesquels impliquent plusieurs accusés
                    if ($sended['before_delivered'] > 1)
                    {
                        $db->updateTableWhere('sendeds', ['before_delivered' => $sended['before_delivered'] - 1], ['id' => $sended['id']]);
                        echo 'Sended Sms id '.$sended['id']." before_delivered decrement\n";

                        continue;
                    }

                    //Si tout est bon, que nous avons assez d'accusés, nous validons !
                    $db->updateTableWhere('sendeds', ['before_delivered' => 0, 'delivered' => true], ['id' => $sended['id']]);
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
                    //Si on a bien un utilisateur avec les identifiants reçus
                    $user = $db->getUserFromEmail($flags['LOGIN']);
                    error_log('We found '.\count($user).' users');
                    if ($user && $user['password'] === sha1($flags['PASSWORD']))
                    {
                        error_log('Password is valid');
                        //On va passer en revue toutes les commandes, pour voir si on en trouve dans ce message
                        $commands = $db->getFromTableWhere('commands');

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
                if (!$db->insertIntoTable('receiveds', ['at' => $date, 'send_by' => $send_by, 'content' => $content, 'is_command' => $is_command]))
                {
                    echo "Erreur lors de l'enregistrement du Sms\n";
                    error_log('Unable to process the Sms in file "'.$dir);
                    die(7);
                }

                //On insert le Sms dans le tableau des sms à envoyer par mail
                $db->insertIntoTable('transfers', ['id_received' => $db->lastId(), 'progress' => false]);

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
        if (!RASPISms_SETTINGS_TRANSFER)
        {
            echo "Le transfer de Sms est désactivé ! \n";

            return false;
        }

        global $db;
        $transfers = $db->getFromTableWhere('transfers', ['progress' => false]);

        $ids_transfers = [];
        $ids_receiveds = [];
        foreach ($transfers as $transfer)
        {
            $ids_transfers[] = $transfer['id'];
            $ids_receiveds[] = $transfer['id_received'];
        }

        $db->updateProgressTransfersIn($ids_transfers, true);

        $receiveds = $db->getReceivedIn($ids_receiveds);

        $users = $db->getFromTableWhere('users', ['transfer' => true]);

        foreach ($users as $user)
        {
            foreach ($receiveds as $received)
            {
                echo "Transfer d'un Sms du ".$received['send_by']." à l'email ".$user['email'];
                $to = $user['email'];
                $subject = '[RaspiSms] - Transfert d\'un Sms du '.$received['send_by'];
                $message = 'Le numéro '.$received['send_by']." vous a envoyé un Sms : \n".$received['content'];

                $ok = mail($to, $subject, $message);

                echo ' ... '.($ok ? 'OK' : 'KO')."\n";
            }
        }

        $db->deleteTransfersIn($ids_transfers);
    }
}
