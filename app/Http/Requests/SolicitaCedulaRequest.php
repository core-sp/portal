<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;
use App\Contracts\MediadorServiceInterface;

class SolicitaCedulaRequest extends FormRequest
{
    private $service;
    private $statusAceitos;
    private $tiposAceitos;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service->getService('Cedula');
    }

    protected function prepareForValidation()
    {
        if(\Route::is('representante.inserirSolicitarCedula'))
        {
            $user = auth()->guard('representante')->user();
            if($user->tipoPessoa() == $user::PESSOA_FISICA)
                $this->merge([
                    'tipo_pessoa' => $user->tipoPessoa(),
                    'nome' => null,
                    'cpf' => null
                ]);
            else
                $this->merge([
                    'tipo_pessoa' => $user->tipoPessoa(),
                    'nome' => strtoupper($this->nome),
                    'rg' => strtoupper(apenasNumerosLetras($this->rg))
                ]);
        }

        $this->tiposAceitos = $this->service->getAllTipos();
        $allStatus = $this->service->getAllStatus();
        unset($allStatus[0]);
        $this->statusAceitos = $allStatus;

        if(request()->filled('status') && ($this->status == $this->statusAceitos[1]))
            $this->merge([
                'justificativa' => null
            ]);
    }

    public function rules()
    {
        return [
            'nome' => 'sometimes|required_if:tipo_pessoa,PJ|nullable|min:6|max:191|regex:/^\D*$/',
            'rg' => 'sometimes|required|min:7|max:15',
            'cpf' => [
                'sometimes', 
                'required_if:tipo_pessoa,PJ', 
                'nullable',
                new CpfCnpj
            ],
            "cep" => "sometimes|required|size:9",
            "bairro" => "sometimes|required|min:4|max:100",
            "logradouro" => "sometimes|required|min:4|max:100",
            "numero" => "sometimes|required|max:15",
            "complemento" => "max:100",
            "estado" => "sometimes|required|in:" . implode(',', array_keys(estados())),
            "municipio" => "sometimes|required|min:4|max:100",
            'tipo' => "sometimes|required|in:" . implode(',', $this->tiposAceitos),
            'justificativa' => 'sometimes|required_if:status,' . $this->statusAceitos[2] . '|nullable|min:5|max:600',
            'status' => 'sometimes|required|in:' . implode(',', $this->statusAceitos),
            'tipo_pessoa' => '',
        ];
    }

    public function messages()
    {
        return [
            "required" => "Campo obrigatório",
            "required_if" => "Campo obrigatório",
            "max" => "Excedido limite de :max caracteres",
            'min' => 'O :attribute deve ter, no mínimo, :min caracteres',
            'status.in' => 'Status não aceito',
            'in' => 'Valor não aceito',
            'regex' => 'Formato inválido',
            'cep.size' => 'Total de caracteres deve ser 9',
        ];
    }
}
