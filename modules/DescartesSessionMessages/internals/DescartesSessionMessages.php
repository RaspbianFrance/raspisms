<?php
	namespace modules\DescartesSessionMessages\internals;

	/**
     * Cette classe permet de passer des messages d'une page à l'autre via la session
	 */

	class DescartesSessionMessages
    {
        /**
         * Allow to add a message
         * @param string $type : Type of the message (usually success, info, warning or danger)
         * @param string $text : Text of the message
         */
        public static function push ($type, $text)
        {
            if (empty($_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME]))
            {
                $_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME] = [];
            }

            $_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME][] = [
                'type' => $type,
                'text' => $text,
            ];
        }

        /**
         * Allow to get the next message
         * @return mixed array|bool : If there is a next message, return it, else return false
         */
        public static function getNext ()
        {
            if (empty($_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME]))
            {
                return false;
            }

            $message = $_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME][0];
            unset($_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME][0]);
            $_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME] = array_values($_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME]);

            return $message;
        }

        /**
         * Allow to count message to display
         * @return int : Number of message to display
         */
        public static function countMessages ()
        {
            if (empty($_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME]))
            {
                return 0;
            }

            return count($_SESSION[DESCARTESSESSIONMESSAGES_VAR_NAME]);
        }

	}
