<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomeImagemRequest extends FormRequest
{
    private $regex_hex;
    private $tipo_campo;
    private $required_video;

    protected function prepareForValidation()
    {
        if(\Route::is('imagens.itens.home.storage.post') || \Route::is('imagens.itens.home.storage'))
            return;

        $this->regex_hex = "regex:/#{1}([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})/";
        $this->tipo_campo = 'string|max:191';

        if(!isset($this->neve_default))
            $this->merge(['neve_default' => null]);

        if(isset($this->header_fundo_default) && isset($this->neve_default))
            $this->merge(['neve_default' => 'erro']);

        if(isset($this->popup_video_default) && ($this->popup_video_default == 'popup_video'))
        {
            $all = $this->all();
            unset($all['popup_video_default']);
            $this->replace($all);
        }

        if(isset($this->popup_video_default) && ($this->popup_video_default == 'sem_video'))
        {
            $all = $this->all();
            unset($all['popup_video']);
            $this->replace($all);
            $this->merge(['popup_video_default' => null]);
        }

        $this->required_video = isset($this->popup_video) ? '|required|' : '|';

        if(isset($this->header_fundo))
        {
            $all = $this->all();
            unset($all['header_fundo_cor']);
            $this->replace($all);

            if(isset($this->neve_default))
                $this->merge(['neve_default' => 'erro']);
        }
        
        if(isset($this->header_fundo_cor))
        {
            $this->merge(['header_fundo' => $this->header_fundo_cor]);
            $this->tipo_campo = $this->regex_hex;
        }
    }

    public function rules()
    {
        if(\Route::is('imagens.itens.home.storage'))
            return [];
        
        if(\Route::is('imagens.itens.home.storage.post'))
            return [
                'file_itens_home' => 'required|mimes:jpeg,jpg,png,JPEG,JPG,PNG|max:2048|regex:/^[^áéíóúýÁÉÍÓÚÝâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÿÄËÏÖÜŸçÇ]*$/',
            ];

        return [
            'cards_1_default' => 'nullable|in:cards_1_default',
            'cards_1' => 'exclude_if:cards_1_default,cards_1_default|nullable|'.$this->regex_hex,
            'cards_2_default' => 'nullable|in:cards_2_default',
            'cards_2' => 'exclude_if:cards_2_default,cards_2_default|nullable|'.$this->regex_hex,
            'footer_default' => 'nullable|in:footer_default',
            'footer' => 'exclude_if:footer_default,footer_default|nullable|'.$this->regex_hex,
            'calendario_default' => 'nullable|in:calendario_default',
            'calendario' => 'exclude_if:calendario_default,calendario_default|required_unless:calendario_default,calendario_default|nullable|string|max:191',
            'header_logo_default' => 'nullable|in:header_logo_default',
            'header_logo' => 'exclude_if:header_logo_default,header_logo_default|required_unless:header_logo_default,header_logo_default|nullable|string|max:191',
            'header_fundo_default' => 'nullable|in:header_fundo_default',
            'header_fundo' => 'exclude_if:header_fundo_default,header_fundo_default|required_unless:header_fundo_default,header_fundo_default|nullable|'.$this->tipo_campo,
            'neve_default' => 'nullable|in:neve_default',
            'popup_video_default' => 'nullable|in:popup_video_default',
            'popup_video' => 'exclude_if:popup_video_default,popup_video_default' . $this->required_video .'nullable|url|max:191',
        ];
    }

    public function messages()
    {
        return [
            'in' => 'Valor não aceito',
            'file_itens_home.regex' => 'Não pode conter acentuação no nome do arquivo',
            'regex' => 'Formato inválido de cor',
            'max' => 'O campo não permite mais que :max caracteres',
            'string' => 'Deve ser um texto',
            'required' => 'Campo obrigatório',
            'mimes' => 'Tipo de arquivo não aceito',
            'file_itens_home.max' => 'Somente imagens de até 2MB',
            'required_unless' => 'Campo obrigatório se o padrão não for escolhido',
            'neve_default.in' => 'Valor não aceito quando fundo do logo principal é uma imagem',
            'url' => 'Deve ser um link https://',
        ];
    }
}
