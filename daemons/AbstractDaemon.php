<?php

/**
 * Class defining the global structur of a Linux Daemon
 */
abstract class AbstractDaemon
{
	protected $name;
	private $is_running = true;
	private $signals = array (
        SIGTERM,
        SIGINT,
        SIGCHLD,
        SIGHUP 
    );

	/**
	 * Class used to handle POSIX signals and fork from the current process
	 *
	 * @param string $name : The name of the class
	 * @param array $signals :An array containing additional POSIX signals to handle [optionel]
	 */
    protected function __construct (string $name, array $signals = [])
    {
		$this->name = $name;
        $this->signals = array_merge($this->signals, $signals);

        //Allow script to run indefinitly
        set_time_limit(0);

        //Register signals
		$this->register_signals();
    }


	/**
	 * Used to register POSIX signals
	 */
    private function register_signals()
    {
        //Enable a tick at every 1 instruction, allowing us to run a function frequently, for exemple looking at signal status
        declare(ticks = 1);

        foreach ($this->signals as $signal)
        {
            //For each signal define the method handle_signal of the current class as the way to handle it
            @pcntl_signal($signal, [
                    'self',
					'handle_signal' 
            ]);
		}
    }

	/**
	 * Used to handle properly SIGINT, SIGTERM, SIGCHLD and SIGHUP
	 *
     * @param int $signal
     * @param mixed $signinfo
	 */
    protected function handle_signal(int $signal, $signinfo)
    {
        if ($signal == SIGTERM || $signal == SIGINT) //Stop the daemon
        {
			$this->is_running = false;
        }
        else if ($signal == SIGHUP) //Restart the daemon
        {
			$this->on_stop();
			$this->on_start();
        }
        else if ($signal == SIGCHLD) //On daemon child stopping
        {
			pcntl_waitpid(-1, $status, WNOHANG);
        }
        else //All the other signals
        {
			$this->handle_other_signals($signal);
		}
    }


	/**
	 * Launch the infinite loop executing the "run" abstract method
	 */
    protected function start ()
    {
		$this->on_start();
        while ($this->is_running)
        {
			pcntl_signal_dispatch(); //Call dispatcher for signals
			$this->run();
		}
		$this->on_stop();
    }


	/**
	 * True if the daemon is running
	 */
    public function is_running()
    {
		return $this->is_running;
    }

	/**
	 * Override to implement the code that run infinetly (actually, it run one time but repeat the operation infinetly
	 */
    protected abstract function run();


	/**
	 * Override to execute code before the ''run'' method on daemon start
	 */
    protected abstract function on_start();


	/**
	 * Override to execute code after the ''run'' method on daemon shutdown
	 */
	protected abstract function on_stop();
    
    
    /**
	 * Override to handle additional POSIX signals
	 *
	 * @param int $signal : Signal sent by interrupt
	 */
	protected abstract function handle_other_signals(int $signal);
}
