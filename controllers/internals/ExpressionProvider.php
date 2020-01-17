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
        return [
            ExpressionFunction::fromPhp('is_null', 'exists'),
            ExpressionFunction::fromPhp('mb_strtolower', 'lower'),
            ExpressionFunction::fromPhp('mb_strtoupper', 'upper'),
            ExpressionFunction::fromPhp('mb_substr', 'substr'),
            ExpressionFunction::fromPhp('abs', 'abs'),
            ExpressionFunction::fromPhp('strtotime', 'date'),
        ];
    }
}
