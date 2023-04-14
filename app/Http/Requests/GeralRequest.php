<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class GeralRequest extends FormRequest
{
    private $regras;
    private $regraData;
    private $msgData;

    protected function prepareForValidation()
    {
        $this->msgData = ['', ''];

        if(\Route::is('anuidade-ano-vigente.post'))
            $this->regras = !in_array(config('app.env'), ['testing']) ? 'required|recaptcha' : '';

        if(\Route::is('consultaSituacao.post') || \Route::is('anuidade-ano-vigente.post'))
            $this->merge(['cpfCnpj' => apenasNumeros($this->cpfCnpj)]);

        if(\Route::is('simulador.post'))
        {
            $this->regraData = $this->tipoPessoa != '1' ? '|date_equals:'.date('Y-m-d') : '|before_or_equal:'.date('Y-m-d').'|after_or_equal:1900-01-01';
            $this->msgData = $this->tipoPessoa != '1' ? [date('d/m/Y'), ''] : [date('d/m/Y'), '01/01/1900'];
            $this->tipoPessoa != '1' ? $this->merge(['capitalSocial' => null, 'empresaIndividual' => null, 'filialCheck' => null, 'filial' => null]) : null;
        }
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
        
        if(\Route::is('simulador.post'))
            return [
                'tipoPessoa' => 'required|in:1,2,5',
                'dataInicio' => 'required|date'.$this->regraData,
                'capitalSocial' => 'nullable|required_if:tipoPessoa,1|regex:/^((?!(0))[0-9\.]{1,}),([0-9]{2})$/',
                'filialCheck' => 'nullable|in:on',
                'filial' => 'nullable|required_if:filialCheck,on|in:50,'.implode(',',range(1,24)),
                'empresaIndividual' => 'nullable|in:on',
            ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'required_if' => 'O campo :attribute é obrigatório',
            'max' => 'O campo :attribute excedeu o limite de :max caracteres permitidos',
            'min' => 'O campo :attribute deve ter pelo menos :min caracteres',
            'g-recaptcha-response' => 'ReCAPTCHA inválido',
            'g-recaptcha-response.required' => 'ReCAPTCHA obrigatório',
            'regex' => 'O campo :attribute está num formato inválido',
            'email' => 'Formato inválido de e-mail',
            'unique' => 'Já está cadastrado em nosso sistema',
            'accepted' => 'Você deve concordar com o Termo de Consentimento',
            'in' => 'O campo :attribute possui valor inválido',
            'date' => 'Data no formato inválido',
            'date_equals' => 'Data deve ser igual a '.$this->msgData[0],
            'before_or_equal' => 'Data deve ser antes ou igual a '.$this->msgData[0],
            'after_or_equal' => 'Data deve ser igual ou depois de '.$this->msgData[1],
        ];
    }
}
