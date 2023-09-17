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

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Mailing class.
 */
class LinkShortener
{
    /**
     * Shorten an URL using the configured YOURLS instance
     */
    public static function shorten($url)
    {
        $api_url = URL_SHORTENER['HOST'] . '/yourls-api.php';

        $data = [
            'action'   => 'shorturl',
            'format'   => 'json',
            'username' => URL_SHORTENER['USER'],
            'password' => URL_SHORTENER['PASS'],
            'url'      => $url,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Enable follow location
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
        curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);

        try
        {
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (\Exception $e)
        {
            return false;
        }

        $shortlink = $response['shorturl'] ?? false;
        return $shortlink;
    }
}
