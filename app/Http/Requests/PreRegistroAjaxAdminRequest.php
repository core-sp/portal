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

    protected function prepareForValidation()
    {
        $this->regraValor = ['max:500'];

        if(in_array(request()->campo, ['confere_anexos[]']))
            $this->merge([
                'campo' => str_replace('[]', '', request()->campo),
            ]);

        if(in_array(request()->campo, ['registro', 'registro_secundario']) && (request()->acao == 'editar'))
        {
            $this->regraValor = ['max:20'];
            $this->merge([
                'valor' => apenasNumeros(request()->valor),
            ]);
        }

        if($this->campo == 'confere_anexos')
        {
            $id = $this->preRegistro;
            $tipos_anexos = $this->service->getService('PreRegistro')->getAdminService()->getTiposAnexos($id);
            if(!isset($tipos_anexos))
            {
                $this->regraValor = ['required'];
                $this->merge([
                    'valor' => '',
                ]);
            }else
                $this->regraValor = ['in:' . implode(',', $tipos_anexos)];
        }
    }

    public function rules()
    {
        $todos = null;
        $campos_array = $this->service->getService('PreRegistro')->getNomesCampos();

        foreach($campos_array as $key => $campos)
            $todos .= isset($todos) ? ','.$campos : $campos;

        $todos .= ',registro,negado';
        if(request()->acao == 'editar')
            $todos = 'registro,registro_secundario';
        if(request()->acao == 'conferir')
            $todos = 'confere_anexos';

        return [
            'acao' => 'required|in:justificar,editar,conferir',
            'valor' => $this->regraValor,
            'campo' => 'required|in:'.$todos,
        ];
    }

    public function messages()
    {
        return [
            'max' => 'Limite de :max caracteres',
            'in' => 'Campo / ação não encontrado(a)',
            'required' => 'Falta dados para enviar a requisição',
        ];
    }
}
