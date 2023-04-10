<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeralRequest extends FormRequest
{
    public function rules()
    {
        if(\Route::is('site.busca'))
            return [
                'busca' => 'required|min:3'
            ];
    }

    public function messages()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de :max caracteres permitidos',
            'min' => 'O :attribute deve ter pelo menos :min caracteres',
        ];
    }
}
