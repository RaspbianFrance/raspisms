<?php
    namespace adapters;

    /**
     * Interface for phones adapters
     * Phone's adapters allow RaspiSMS to use a platform to communicate with a phone number.
     * Its an adapter between internal and external code, as an API, command line software, physical modem, etc.
     *
     * All Phone Adapters must implement this interface
     */
    class OvhSmsAdapter implements AdapterInterface
    {
        /**
         * Classname of the adapter
         */
        public static function meta_classname() : string { return __CLASS__; }

        /**
         * Name of the adapter.
         * It should probably be the name of the service it adapt (e.g : Gammu SMSD, OVH SMS, SIM800L, etc.)
         */
        public static function meta_name() : string { return 'OVH SMS'; }

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description() : string { return 'Solution de SMS proposé par le groupe OVH, https://www.ovhtelecom.fr/sms/.'; }
        
        /**
         * Description of the datas expected by the adapter to help the user. (e.g : A list of expecteds Api credentials fields, with name and value)
         */
        public static function meta_datas_help() : string { return 'See later.'; } 

        /**
         * Does the implemented service support flash smss
         */
        public static function meta_support_flash() : bool { return false ; }


        /**
         * Phone number using the adapter
         */
        private $number;

        /**
         * Datas used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
         */
        private $datas;

        
        /**
         * Adapter constructor, called when instanciated by RaspiSMS
         * @param string $number : Phone number the adapter is used for
         * @param json string $datas : JSON string of the datas to configure interaction with the implemented service
         */
        public function __construct (string $number, string $datas)
        {
            $this->number = $number;
            $this->datas = $datas;
        }
    
    
        /**
         * Method called to send a SMS to a number
         * @param string $destination : Phone number to send the sms to
         * @param string $text : Text of the SMS to send
         * @param bool $flash : Is the SMS a Flash SMS
         * @return bool : True if send, False else
         */
        public function send (string $destination, string $text, boolean $flash) : boolean
        {
            return true;
        }


        /**
         * Method called to read SMSs of the number
         * @param float $since : Unix microtime representation of the date from wich we want to read the SMSs
         * @return array : Array of the sms reads
         */
        public function read (float $since) : array
        {
            return [];
        }
    }
