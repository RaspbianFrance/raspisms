<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace daemons;

/**
 * Class defining the global structur of a Linux Daemon.
 */
abstract class AbstractDaemon
{
    protected $name;
    protected $uniq;
    protected $logger;
    protected $no_parent;
    protected $pid_dir;
    private $is_running = true;
    private $signals = [
        SIGTERM,
        SIGINT,
        SIGCHLD,
        SIGHUP,
    ];

    /**
     * Class used to handle POSIX signals and fork from the current process.
     *
     * @param string $name      : The name of the class
     * @param object $logger    : A PSR3 logger instance
     * @param string $pid_dir   : Directory for the pid files
     * @param bool   $no_parent : Should the daemon be disconnected from his parent process
     * @param array  $signals   :An array containing additional POSIX signals to handle [optionel]
     * @param bool   $uniq      : Must the process be uniq ?
     */
    protected function __construct(string $name, object $logger, string $pid_dir = '/var/run', $no_parent = false, array $signals = [], bool $uniq = false)
    {
        $this->name = $name;
        $this->logger = $logger;
        $this->no_parent = $no_parent;
        $this->pid_dir = $pid_dir;
        $this->signals = array_merge($this->signals, $signals);
        $this->uniq = $uniq;

        //Allow script to run indefinitly
        set_time_limit(0);

        //Register signals
        $this->register_signals();
    }

    /**
     * True if the daemon is running.
     */
    public function is_running()
    {
        return $this->is_running;
    }

    /**
     * Used to handle properly SIGINT, SIGTERM, SIGCHLD and SIGHUP.
     *
     * @param int   $signal
     * @param mixed $signinfo
     */
    protected function handle_signal(int $signal, $signinfo)
    {
        if (SIGTERM === $signal || SIGINT === $signal)
        { //Stop the daemon
            $this->is_running = false;
        }
        elseif (SIGHUP === $signal)
        { //Restart the daemon
            $this->on_stop();
            $this->on_start();
        }
        elseif (SIGCHLD === $signal)
        { //On daemon child stopping
            pcntl_waitpid(-1, $status, WNOHANG);
        }
        else
        { //All the other signals
            $this->handle_other_signals($signal);
        }
    }

    /**
     * Launch the infinite loop executing the "run" abstract method.
     */
    protected function start()
    {
        //If process must be uniq and a process with the same pid file is already running
        if (file_exists($this->pid_dir . '/' . $this->name . '.pid') && $this->uniq)
        {
            $this->logger->info('Another process named ' . $this->name . ' is already running.');

            return false;
        }

        //If we must make the daemon independant from any parent, we do a fork and die operation
        if ($this->no_parent)
        {
            $pid = pcntl_fork(); //Fork current process into a child, so we can kill current process and keep only the child with parent PID = 1

            if (-1 === $pid)
            { //Impossible to run script
                $this->logger->critical('Impossible to create a subprocess.');

                return false;
            }
            if ($pid)
            { //Current script
                return true;
            }

            $this->logger->info("Process {$this->name} started as a child with pid " . getmypid() . '.');

            //Child script
            $sid = posix_setsid(); //Try to make the child process a main process
            if (-1 === $sid)
            { //Error
                $this->logger->critical("Cannot make the child process with pid {$pid} independent.");
                exit(1);
            }

            $this->logger->info('The child process with pid ' . getmypid() . ' is now independent.');
        }

        //Create pid dir if not exists
        if (!file_exists($this->pid_dir))
        {
            $success = mkdir($this->pid_dir, 0777, true);
            if (!$success)
            {
                $this->logger->critical('Cannot create PID directory : ' . $this->pid_dir);
                exit(2);
            }
        }

        //Set process name
        cli_set_process_title($this->name);

        //Write the pid of the process into a file
        file_put_contents($this->pid_dir . '/' . $this->name . '.pid', getmypid());

        //Really start the daemon
        $this->on_start();

        try
        {
            while ($this->is_running)
            {
                pcntl_signal_dispatch(); //Call dispatcher for signals
                $this->run();
            }
        }
        catch (\Throwable $t)
        {
            $this->logger->critical('Exception : ' . $t->getMessage() . ' in ' . $t->getFile() . ' line ' . $t->getLine());
        }

        //Stop the daemon
        $this->on_stop();

        //Delete pid file
        if (file_exists($this->pid_dir . '/' . $this->name . '.pid'))
        {
            unlink($this->pid_dir . '/' . $this->name . '.pid');
        }
    }

    /**
     * Override to implement the code that run infinetly (actually, it run one time but repeat the operation infinetly.
     */
    abstract protected function run();

    /**
     * Override to execute code before the ''run'' method on daemon start.
     */
    abstract protected function on_start();

    /**
     * Override to execute code after the ''run'' method on daemon shutdown.
     */
    abstract protected function on_stop();

    /**
     * Override to handle additional POSIX signals.
     *
     * @param int $signal : Signal sent by interrupt
     */
    abstract protected function handle_other_signals(int $signal);

    /**
     * Used to register POSIX signals.
     */
    private function register_signals()
    {
        //Enable a tick at every 1 instruction, allowing us to run a function frequently, for exemple looking at signal status
        declare(ticks=1);

        foreach ($this->signals as $signal)
        {
            //For each signal define the method handle_signal of the current class as the way to handle it
            @pcntl_signal($signal, [
                'self',
                'handle_signal',
            ]);
        }
    }
}
