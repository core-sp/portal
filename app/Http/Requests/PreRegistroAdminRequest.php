<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class PreRegistroAdminRequest extends FormRequest
{
    private $service;
    private $msg;

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
        $this->msg = '';
        
        $preRegistro = $this->service->getService('PreRegistro')->admin()->view($this->preRegistro)['resultado'];

        $resp = $preRegistro->verificaAtendentePodeAtualizarStatus($this->situacao);

        if(!is_array($resp))
        {
            $this->merge(['status' => '']);
            $this->msg = $resp;
            return;
        }

        $this->merge($resp);
    }

    public function rules()
    {
        return \Route::is('preregistro.upload.doc') ? 
            [
                'file' => 'required|file|mimetypes:application/pdf|max:2048',
                'tipo' => 'required|in:'.implode(',', $this->service->getService('PreRegistro')->admin()->tiposDocsAtendente()),
            ] : [
                'situacao' => 'required|in:aprovar,negar,corrigir',
                'status' => 'required',
            ];
    }

    public function messages()
    {
        return [
            'situacao.required' => 'Obrigatório o status requisitado',
            'situacao.in' => 'Valor do status requisitado inválido',
            'status.required' => $this->msg,
            'mimetypes' => 'O arquivo não possui extensão .pdf ou está com erro',
            'file' => 'Deve ser um arquivo',
            'uploaded' => 'Falhou o upload por erro no servidor',
            'file.max' => 'O arquivo deve ter um limite de até 2MB',
            'file.required' => 'Campo obrigatório',
            'tipo.in' => 'Tipo de documento não aceito',
            'tipo.required' => 'Campo do tipo de documento é obrigatório'
        ];
    }
}
