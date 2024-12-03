<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

class RecaptchaHelper
{
    public static function validateToken(string $token, string $performedAction, float $scoreThreshold = 0.5): float
    {
        $siteKey = '6LerzpoaAAAAAMYGdaLXBtnPxc2kzHi7ypUuVAt9';
        $secretKey = '6LerzpoaAAAAAFLyv8NTv_1MVrftcyW9JsbMrO_g';


        $arrResponse = self::requestData($token, $secretKey);

        $success = $arrResponse['success'];
        if ($success === false) {
            throw new \Error('Unable to fetch score');
        }

        $score = $arrResponse["score"];
        $action = $arrResponse["action"];
        if ($action === $performedAction && $score >= $scoreThreshold) {
            return $score;
        }

        throw new \Error('Looks like spam (score: ' . $score . ')');
    }

    private static function requestData(string $token, string $secretKey): array
    {
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        \curl_setopt($ch, CURLOPT_POST, 1);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['secret' => $secretKey, 'response' => $token]));
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = \curl_exec($ch);
        \curl_close($ch);
        return \json_decode($response, true);

    }
}
