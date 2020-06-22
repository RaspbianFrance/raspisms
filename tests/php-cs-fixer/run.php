#!/usr/bin/php
<?php

    function help ()
    {
        echo 'Usage : ' . __FILE__ . ' <arg>' . "\n" .
             'Args :' . "\n" .
             '    - help : Show help  message.' . "\n" .
             '    - lint : Show coding standards to fix.' . "\n" .
             '    - fix : Fix coding standards.' . "\n";

        exit(100);
    }

    $lint_commands = [
        'php ' . __DIR__ . '/php-cs-fixer.phar -v --dry-run --config="' . __DIR__ . '/php_cs.config" fix',
    ];
    
    $fix_commands = [
        'php ' . __DIR__ . '/php-cs-fixer.phar --config="' . __DIR__ . '/php_cs.config" fix',
    ];


    if (count($argv) < 2 || $argv[1] === 'help')
    {
        help();
    }

    if ($argv[1] === 'lint')
    {
        echo "######################" . "\n";
        echo "# SHOW ERRORS TO FIX #" . "\n";
        echo "######################" . "\n";
        echo "\n";

        foreach ($lint_commands as $lint_command)
        {
            echo "Run : " . $lint_command . " \n";
            $return = shell_exec($lint_command);
            echo $return;
            echo "\n\n";
        }
        
        exit(0);
    }

    if ($argv[1] === 'fix')
    {
        echo "##############" . "\n";
        echo "# FIX ERRORS #" . "\n";
        echo "##############" . "\n";
        echo "\n";
        
        foreach ($fix_commands as $fix_command)
        {
            echo "Run : " . $fix_command . " \n";
            $return = shell_exec($fix_command);
            echo $return;
            echo "\n\n";
        }
        
        exit(0);
    }

    echo "Invalid arg : " . $argv[1] . "\n";
    help();
