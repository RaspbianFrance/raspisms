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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    /**
     * Class to analyse rules used by conditional groups.
     */
    class Ruler extends \descartes\InternalController
    {
        private $expression_language;

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->expression_language = new ExpressionLanguage();

            //Add custom functions
            $this->expression_language->registerProvider(new ExpressionProvider());
        }

        /**
         * Verify if a condition is valid. i.e we can evaluate it without error.
         *
         * @param string $condition : The condition to evaluate
         * @param array  $data      : The data to made available to condition
         *
         * @return bool : false if invalid, true else
         */
        public function validate_condition(string $condition, array $data = []): bool
        {
            try
            {
                //Use @ to hide notices on non defined vars
                @$this->expression_language->parse($condition, array_keys($data));

                return true;
            }
            catch (\Throwable $t)
            { //Catch both, exceptions and php error
                return false;
            }
        }

        /**
         * Evaluate a condition.
         *
         * @param string $condition : The condition to evaluate
         * @param array  $data      : The data to made available to condition
         *
         * @return ?bool : false if invalid, true else, null only on error
         */
        public function evaluate_condition(string $condition, array $data = []): ?bool
        {
            try
            {
                //Use @ to hide notices on non defined vars
                @$result = $this->expression_language->evaluate($condition, $data);

                return (bool) $result;
            }
            catch (\Throwable $t)
            { //Catch both, exceptions and php error
                return null;
            }
        }
    }
