<?php

namespace App\Validators;

class ReCaptcha
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        $client = new \GuzzleHttp\Client;
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
            [
                'form_params' =>
                    [
                        'secret' => env('GOOGLE_RECAPTCHA_SECRET'),
                        'response' => $value
                    ]
            ]
        );
        $body = json_decode((string)$response->getBody());
        return $body->success;
    }
}