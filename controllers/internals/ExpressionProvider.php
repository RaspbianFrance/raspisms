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

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        //Override default constant() function to make it return null
        //This will prevent the use of constant() func to read constants with security impact (such as session, db credentials, etc.)
        $neutralized_constant = new ExpressionFunction('constant', function ($str) {
            return null;
        }, function ($arguments, $str) {
            return null;
        });

        //Exists must be personnalized because it inverse is_null
        $exists = new ExpressionFunction('exists', function ($var) {
            return sprintf('!is_null(%1$s)', $str);
        }, function ($arguments, $var) {
            return !is_null($var);
        });


        return [
            $neutralized_constant,
            $exists,
            ExpressionFunction::fromPhp('mb_strtolower', 'lower'),
            ExpressionFunction::fromPhp('mb_strtoupper', 'upper'),
            ExpressionFunction::fromPhp('mb_substr', 'substr'),
            ExpressionFunction::fromPhp('mb_strlen', 'strlen'),
            ExpressionFunction::fromPhp('abs', 'abs'),
            ExpressionFunction::fromPhp('strtotime', 'date'),
        ];
    }
}
