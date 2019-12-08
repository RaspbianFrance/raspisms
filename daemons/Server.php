<?php
namespace daemons;

/**
 * Main daemon class
 */
class Server extends AbstractDaemon
{
    public function __construct()
    {
        //Construct the server and add SIGUSR1 and SIGUSR2
        parent::__construct("server", [SIGUSR1, SIGUSR2]);

        //Start the daemon
        parent::start ();
    }


    public function run()
    {
        // Le code qui s'exécute infiniment
        echo "On tourne !\n";
        sleep ( 5 );
    }


    public function on_start()
    {
        echo "Démarrage du processus avec le pid " . getmypid () . "\n";
    }


    public function on_stop() 
    {
        echo "Arrêt du processus avec le pid " . getmypid () . "\n";
    }


    public function handle_other_signals($signal)
    {
        echo "Signal non géré par la classe Daemon : " . $signal . "\n";
    }
}
