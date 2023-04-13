<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class GeralRequest extends FormRequest
{
    private $regras;

    protected function prepareForValidation()
    {
        if(\Route::is('anuidade-ano-vigente.post'))
            $this->regras = !in_array(config('app.env'), ['testing']) ? 'required|recaptcha' : '';

        if(\Route::is('consultaSituacao.post') || \Route::is('anuidade-ano-vigente.post'))
            $this->merge(['cpfCnpj' => apenasNumeros($this->cpfCnpj)]);
    }

    public function rules()
    {
        if(\Route::is('site.busca'))
            return [
                'busca' => 'required|min:3'
            ];

        if(\Route::is('consultaSituacao.post'))
            return [
                'cpfCnpj' => ['required', new CpfCnpj],
            ];

        if(\Route::is('anuidade-ano-vigente.post'))
            return [
                'cpfCnpj' => ['required', new CpfCnpj],
                'g-recaptcha-response' => $this->regras,
            ];

        if(\Route::is('newsletter.post'))
            return [
                'nome' => 'required|min:5|max:191|regex:/^[a-zA-Z ÁáÉéÍíÓóÚúÃãÕõÂâÊêÔô]+$/',
                'email' => 'required|email|unique:newsletters,email',
                'celular' => 'required|max:17|regex:/(\([0-9]{2}\))\s([0-9]{5})\-([0-9]{4})/',
                'termo' => 'accepted'
            ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de :max caracteres permitidos',
            'min' => 'O :attribute deve ter pelo menos :min caracteres',
            'g-recaptcha-response' => 'ReCAPTCHA inválido',
            'g-recaptcha-response.required' => 'ReCAPTCHA obrigatório',
            'regex' => 'O :attribute está num formato inválido',
            'email' => 'Formato inválido de e-mail',
            'unique' => 'Já está cadastrado em nosso sistema',
            'accepted' => 'Você deve concordar com o Termo de Consentimento',
        ];
    }
}
