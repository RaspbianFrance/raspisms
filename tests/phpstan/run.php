#!/usr/bin/php
<?php
    function help ()
    {
        echo 'Usage : ' . __FILE__ . ' <arg>' . "\n" .
             'Args :' . "\n" .
             '    - help : Show help  message.' . "\n" .
             '    - analyse : Analyse code with phpstan.' . "\n";

        exit(100);
    }

    $analyse_commands = [
        'php ' . __DIR__ . '/phpstan.phar analyse --autoload-file=' . __DIR__ . '/../../descartes/load.php ' . __DIR__ . '/../../controllers/',
        'php ' . __DIR__ . '/phpstan.phar analyse --autoload-file=' . __DIR__ . '/../../descartes/load.php ' . __DIR__ . '/../../models/',
    ];


    if (count($argv) < 2 || $argv[1] === 'help')
    {
        help();
    }

    if ($argv[1] === 'analyse')
    {
        echo "######################" . "\n";
        echo "# SHOW ERRORS TO FIX #" . "\n";
        echo "######################" . "\n";
        echo "\n";

        foreach ($analyse_commands as $analyse_command)
        {
            echo "Run : " . $analyse_command . " \n";
            $return = shell_exec($analyse_command);
            echo $return;
            echo "\n\n";
        }
        
        exit(0);
    }

    echo "Invalid arg : " . $argv[1] . "\n";
    help();
