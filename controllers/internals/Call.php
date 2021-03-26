<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

    class Call extends StandardController
    {
        protected $model;

        /**
         * Create a call.
         *
         * @param int $id_user         : Id of the user
         * @param int $id_phone        : Id of the phone that emitted (outbound) or received (inbound) the call
         * @param string $uid          : Uid of the phone call
         * @param string $direction    : Direction of the call, \models\Call::DIRECTION_INBOUND | \models\Call::DIRECTION_OUTBOUND
         * @param string $start        : Date of the call beginning
         * @param ?string $end         : Date of the call end
         * @param ?string $origin      : Origin of the call or null if outbound
         * @param ?string $destination : Destination of the call or null if inbound
         *
         * @return mixed bool|int : false on error, new call id else
         */
        public function create(int $id_user, int $id_phone, string $uid, string $direction, string $start, ?string $end = null, ?string $origin = null, ?string $destination = null)
        {
            $call = [
                'id_user' => $id_user,
                'id_phone' => $id_phone,
                'uid' => $uid,
                'start' => $start,
                'end' => $end,
                'direction' => $direction,
                'origin' => $origin,
                'destination' => $destination,
            ];

            if (!$origin && !$destination)
            {
                return false;
            }

            switch ($direction)
            {
                case \models\Call::DIRECTION_OUTBOUND :
                    if (null === $destination) { return false; }
                    break;
                
                case \models\Call::DIRECTION_INBOUND :
                    if (null === $origin) { return false; }
                    break;

                default :
                    return false;
            }

            if (!\controllers\internals\Tool::validate_date($start, 'Y-m-d H:i:s'))
            {
                return false;
            }

            if (null !== $end && !\controllers\internals\Tool::validate_date($end, 'Y-m-d H:i:s'))
            {
                return false;
            }

            if (null !== $end && new \DateTime($end) < new \DateTime($start))
            {
                return false;
            }
            
            $new_call_id = $this->get_model()->insert($call);
            if (!$new_call_id)
            {
                return false;
            }

            $call['id'] = $new_call_id;
            
            $internal_webhook = new Webhook($this->bdd);
            $internal_webhook->trigger($id_user, \models\Webhook::TYPE_INBOUND_CALL, $call);

            return $new_call_id;
        }


        /**
         * End a call
         *
         * @param int $id_user : Id of the user to end call for
         * @param int $id_phone : If of the phone to end call for
         * @param string $uid : Uid of the call to end
         * @param string $end : End date of the call, format Y-m-d H:i:s
         *
         * @return bool : False if cannot end phone call, true else
         */
        public function end(int $id_user, int $id_phone, string $uid, string $end)
        {
            if (!\controllers\internals\Tool::validate_date($end, 'Y-m-d H:i:s'))
            {
                return false;
            }

            $call = $this->get_model()->get_by_uid_and_phone_for_user($id_user, $id_phone, $uid);
            if (!$call)
            {
                return false;
            }

            if (new \DateTime($end) < new \DateTime($call['start']))
            {
                return false;
            }

            $datas = [
                'end' => $end,
            ];

            return (bool) $this->get_model()->update_for_user($id_user, $call['id'], $datas);
        }
        
        /**
         * Get the model for the Controller.
         */
        protected function get_model(): \descartes\Model
        {
            $this->model = $this->model ?? new \models\Call($this->bdd);

            return $this->model;
        }
    }
