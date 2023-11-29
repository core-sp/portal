<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeImagemRequest extends FormRequest
{
    private $regex_hex;
    private $tipo_campo;

    protected function prepareForValidation()
    {
        $this->regex_hex = "regex:/#{1}([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})/";
        $this->tipo_campo = 'string|max:191';

        if(isset($this->header_fundo))
        {
            $all = $this->all();
            unset($all['header_fundo_cor']);
            $this->replace($all);
        }
        
        if(isset($this->header_fundo_cor))
        {
            $this->merge(['header_fundo' => $this->header_fundo_cor]);
            $this->tipo_campo = $this->regex_hex;
        }
    }

    public function rules()
    {
        if(\Route::is('imagens.itens.home.storage.post'))
            return [
                'file_itens_home' => 'required|mimes:jpeg,jpg,png,JPEG,JPG,PNG|max:2048',
            ];

        return [
            'cards_1_default' => 'nullable|in:cards_1_default',
            'cards_1' => 'exclude_if:cards_1_default,cards_1_default|nullable|'.$this->regex_hex,
            'cards_2_default' => 'nullable|in:cards_2_default',
            'cards_2' => 'exclude_if:cards_2_default,cards_2_default|nullable|'.$this->regex_hex,
            'footer_default' => 'nullable|in:footer_default',
            'footer' => 'exclude_if:footer_default,footer_default|nullable|'.$this->regex_hex,
            'calendario_default' => 'nullable|in:calendario_default',
            'calendario' => 'exclude_if:calendario_default,calendario_default|nullable|string|max:191',
            'header_logo_default' => 'nullable|in:header_logo_default',
            'header_logo' => 'exclude_if:header_logo_default,header_logo_default|nullable|string|max:191',
            'header_fundo_default' => 'nullable|in:header_fundo_default',
            'header_fundo' => 'exclude_if:header_fundo_default,header_fundo_default|nullable|'.$this->tipo_campo,
        ];
    }

    public function messages()
    {
        return [
            'in' => 'Valor não aceito',
            'regex' => 'Formato inválido de cor',
        ];
    }
}
