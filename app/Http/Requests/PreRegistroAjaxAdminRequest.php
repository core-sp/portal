<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class PreRegistroAjaxAdminRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    public function authorize()
    {
        $user = auth()->user();
        return $user->can('updateOther', $user);
    }

    protected function prepareForValidation()
    {
        $this->regraValor = ['max:500'];

        if(in_array($this->campo, ['confere_anexos[]']))
            $this->merge([
                'campo' => str_replace('[]', '', $this->campo),
            ]);

        if(in_array($this->campo, ['registro', 'registro_secundario']) && ($this->acao == 'editar'))
        {
            $this->regraValor = ['max:20'];
            $this->merge([
                'valor' => apenasNumeros($this->valor),
            ]);
        }

        if($this->campo == 'confere_anexos')
        {
            $id = $this->preRegistro;
            $tipos_anexos = $this->service->getService('PreRegistro')->admin()->getTiposAnexos($id);
            if(!isset($tipos_anexos))
            {
                $this->regraValor = ['required'];
                $this->merge([
                    'valor' => '',
                ]);
            }else
                $this->regraValor = ['in:' . implode(',', $tipos_anexos)];
        }

        if($this->campo == 'exclusao_massa')
            $this->regraValor = ['array'];
    }

    public function rules()
    {
        $todos = implode(',', array_values($this->service->getService('PreRegistro')->getNomesCampos()));
        
        $todos .= ',registro,negado,registro_socio';
        if($this->acao == 'editar')
            $todos = 'registro,registro_secundario';
        if($this->acao == 'conferir')
            $todos = 'confere_anexos';
        if($this->acao == 'exclusao_massa')
            $todos = 'exclusao_massa';

        return [
            'acao' => 'required|in:justificar,editar,conferir,exclusao_massa',
            'valor' => $this->regraValor,
            'campo' => 'required|in:'.$todos,
        ];
    }

    public function messages()
    {
        return [
            'max' => 'Limite de :max caracteres',
            'in' => 'Campo / ação não encontrado(a)',
            'required' => $this->acao == 'conferir' ? 'Não pode editar e/ou não possui anexos' : 'Falta dados para enviar a requisição',
            'array' => 'Formato inválido',
        ];
    }
}
