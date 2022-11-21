<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;
use App\Contracts\MediadorServiceInterface;

class SolicitaCedulaRequest extends FormRequest
{
    private $service;
    private $statusAceitos;

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
                    'nome' => strtoupper($this->nome),
                    'rg' => strtoupper(apenasNumerosLetras($this->rg))
                ]);
        }

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
            'nome' => 'sometimes|required_if:tipo_pessoa,PJ|nullable|min:6|max:191',
            'rg' => 'sometimes|required|max:15',
            'cpf' => [
                'sometimes', 
                'required_if:tipo_pessoa,PJ', 
                'nullable',
                new CpfCnpj
            ],
            "cep" => "sometimes|required",
            "bairro" => "sometimes|required|max:100",
            "logradouro" => "sometimes|required|max:100",
            "numero" => "sometimes|required|max:15",
            "complemento" => "max:100",
            "estado" => "sometimes|required|max:5",
            "municipio" => "sometimes|required|max:100",
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
            "justificativa.max" => "Excedido limite de 600 caracteres",
            "max" => "Excedido limite de caracteres",
            'min' => 'O :attribute deve ter, no mínimo, :min caracteres',
            'status.in' => 'Status não aceito',
        ];
    }
}
