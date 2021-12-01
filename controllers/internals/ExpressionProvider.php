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

use DateTime;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        //Override default constant() function to make it return null
        //This will prevent the use of constant() func to read constants with security impact (such as session, db credentials, etc.)
        $neutralized_constant = new ExpressionFunction('constant', function ($str)
        {
            return null;
        }, function ($arguments, $str)
        {
            return null;
        });

        //Exists must be personnalized because it inverse is_null
        $exists = new ExpressionFunction('exists', function ($str)
        {
            return sprintf('isset(%1$s)', $str);
        }, function ($arguments, $var)
        {
            return isset($var);
        });

        //Birthdate allow to compare two date to check if a date is a birthdate
        $is_birthdate = new ExpressionFunction('is_birthdate', function ($birthdate, $comparison_date = null, $birthdate_format = null, $comparison_date_format = null)
        {
            return sprintf('isset(%1$s) && (new DateTime(%1$s, %3$s ?? null))->format(\'m-d\') == (new DateTime(%2$s ?? \'now\', %4$s ?? null))->format(\'m-d\'))', $birthdate, $comparison_date, $birthdate_format, $comparison_date_format);
        }, function ($arguments, $birthdate, $comparison_date = null, $birthdate_format = null, $comparison_date_format = null)
        {
            if (!$birthdate)
            {
                return false;
            }

            if ($birthdate_format)
            {
                $birthdate = DateTime::createFromFormat($birthdate_format, $birthdate);
            }
            else
            {
                $birthdate = new DateTime($birthdate);
            }

            if ($comparison_date_format)
            {
                $comparison_date = DateTime::createFromFormat($comparison_date_format, $comparison_date);
            }
            else
            {
                $comparison_date = new DateTime($comparison_date ?? 'now');
            }

            if (!$birthdate || !$comparison_date)
            {
                return false;
            }

            return ($birthdate->format('m-d') == $comparison_date->format('m-d'));
        });

        return [
            $neutralized_constant,
            $exists,
            $is_birthdate,
            ExpressionFunction::fromPhp('mb_strtolower', 'lower'),
            ExpressionFunction::fromPhp('mb_strtoupper', 'upper'),
            ExpressionFunction::fromPhp('mb_substr', 'substr'),
            ExpressionFunction::fromPhp('mb_strlen', 'strlen'),
            ExpressionFunction::fromPhp('abs', 'abs'),
            ExpressionFunction::fromPhp('date', 'strtotime'),
            ExpressionFunction::fromPhp('date', 'date'),
        ];
    }
}
