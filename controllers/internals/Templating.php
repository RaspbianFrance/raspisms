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

    /**
     * Templating questions relative class
     * Not a standard controller as it's not linked to a model in any way.
     */
    class Templating
    {
        /**
         * Twig environment.
         */
        private $sandbox;

        public function __construct()
        {
            $tags = [
                'if',
                'for',
                'apply',
                'set',
            ];

            $filters = [
                'abs', 'capitalize', 'country_name', 'currency_name',
                'currency_symbol', 'date', 'date_modify', 'default', 'escape',
                'first', 'format', 'format_currency', 'format_datetime',
                'format_number', 'join', 'json_encode', 'keys', 'language_name',
                'last', 'length', 'locale_name', 'lower', 'number_format',
                'replace', 'reverse', 'round', 'slice',
                'sort', 'spaceless', 'split', 'timezone_name',
                'title', 'trim', 'upper', 'url_encode',
            ];

            $methods = [];
            $properties = [];
            $functions = [
                'date', 'max', 'min', 'random',
                'range',
            ];

            $policy = new \Twig\Sandbox\SecurityPolicy($tags, $filters, $methods, $properties, $functions);
            $this->sandbox = new \Twig\Extension\SandboxExtension($policy, true);
        }

        /**
         * Render a string as a twig template.
         *
         * @param string $template : Template string
         * @param array  $datas    : Datas to pass to the template
         *
         * @return array : keys, success, error, result
         */
        public function render(string $template, array $datas = [])
        {
            try
            {
                $loader = new \Twig\Loader\ArrayLoader([
                    'template' => $template,
                ]);

                $twig = new \Twig\Environment($loader, [
                    'debug' => false,
                    'charset' => 'utf-8',
                    'cache' => false,
                    'auto_reload' => false,
                    'strict_variables' => false,
                    'autoescape' => false,
                    'optimizations' => -1,
                ]);

                $twig->addExtension($this->sandbox);
                $result = $twig->render('template', $datas);

                return [
                    'success' => true,
                    'result' => $result,
                ];
            }
            catch (\Exception $e)
            {
                return [
                    'success' => false,
                    'result' => $e->getMessage(),
                ];
            }
        }
    }
