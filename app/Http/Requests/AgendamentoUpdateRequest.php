<?php

namespace App\Http\Requests;

use App\Contracts\MediadorServiceInterface;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;

class AgendamentoUpdateRequest extends FormRequest
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Agendamento');
    }

    public function rules()
    {
        $completos = $this->service->getServicosOrStatusOrCompletos('completos');
        $status = $this->service->getServicosOrStatusOrCompletos('status');

        return [
            'antigo' => 'sometimes|boolean',
            'nome' => 'sometimes|exclude_if:antigo,1|required|max:191',
            'email' => 'sometimes|exclude_if:antigo,1|required|email|max:191',
            'cpf' => ['sometimes', 'exclude_if:antigo,1', 'required', 'max:14', new Cpf],
            'celular' => 'sometimes|exclude_if:antigo,1|required|max:17',
            'tiposervico' => 'sometimes|required|in:'.implode(',', $completos),
            'idusuario' => 'sometimes|nullable|exists:users,idusuario|required_if:status,==,'.$status[0],
            'status' => 'sometimes|nullable|in:'.implode(',', $status),
            'idagendamento' => 'sometimes|required_without_all:nome,email,cpf,celular,tiposervico,idusuario,antigo'
        ];
    }

    public function messages() 
    {
        return [
            'max' => 'O campo excedeu o limite de :max caracteres',
            'required' => 'O campo é obrigatório',
            'email' => 'Email inválido',
            'idusuario.required_if' => 'Informe o atendente que realizou o atendimento',
            'status.in' => 'Opção inválida de status',
            'tiposervico.in' => 'Opção inválida de tipo de serviço',
            'exists' => 'Usuário não existe'
        ];
    }
}
