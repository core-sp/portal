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
        $this->tipo_campo = [
            'mimes:jpeg,jpg,png,JPEG,JPG,PNG|max:2048',
            'mimes:jpeg,jpg,png,JPEG,JPG,PNG|max:2048',
            'mimes:jpeg,jpg,png,JPEG,JPG,PNG|max:2048',
        ];

        if(isset($this->calendario_texto))
        {
            $this->merge(['calendario' => $this->calendario_texto]);
            $this->tipo_campo[0] = 'string|max:191';
        }

        if(isset($this->header_logo_texto))
        {
            $this->merge(['header_logo' => $this->header_logo_texto]);
            $this->tipo_campo[1] = 'string|max:191';
        }

        if(isset($this->header_fundo_texto) || isset($this->header_fundo))
        {
            $all = $this->all();
            unset($all['header_fundo_cor']);
            $this->replace($all);
        }

        if(isset($this->header_fundo_texto))
        {
            $this->merge(['header_fundo' => $this->header_fundo_texto]);
            $this->tipo_campo[2] = 'string|max:191';
        }

        
        if(isset($this->header_fundo_cor))
        {
            $this->merge(['header_fundo' => $this->header_fundo_cor]);
            $this->tipo_campo[2] = $this->regex_hex;
        }
    }

    public function rules()
    {
        return [
            'cards_1_default' => 'nullable|in:cards_1_default',
            'cards_1' => 'exclude_if:cards_1_default,cards_1_default|nullable|'.$this->regex_hex,
            'cards_2_default' => 'nullable|in:cards_2_default',
            'cards_2' => 'exclude_if:cards_2_default,cards_2_default|nullable|'.$this->regex_hex,
            'footer_default' => 'nullable|in:footer_default',
            'footer' => 'exclude_if:footer_default,footer_default|nullable|'.$this->regex_hex,
            'calendario_default' => 'nullable|in:calendario_default',
            'calendario' => 'exclude_if:calendario_default,calendario_default|nullable|'.$this->tipo_campo[0],
            'header_logo_default' => 'nullable|in:header_logo_default',
            'header_logo' => 'exclude_if:header_logo_default,header_logo_default|nullable|'.$this->tipo_campo[1],
            'header_fundo_default' => 'nullable|in:header_fundo_default',
            'header_fundo' => 'exclude_if:header_fundo_default,header_fundo_default|nullable|'.$this->tipo_campo[2],
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
