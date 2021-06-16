<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RecaptchaRequired implements Rule
{
    public function __construct()
    {
    }

    public function passes($attribute, $recaptcha)
    {
        $teste = env("GOOGLE_RECAPTCHA_KEY") ? !empty($recaptcha) : true;

        return $teste;
    }

    public function message()
    {
        return "ReCAPTCHA obrigatório";
    }
}
