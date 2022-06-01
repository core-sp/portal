<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\CpfCnpj;
use Carbon\Carbon;

class PreRegistroAjaxRequest extends FormRequest
{
    private $regraValor;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $this->regraValor = ['max:191'];

        if((request()->campo == 'tipo_telefone_1') || (request()->campo == 'telefone_1'))
            $this->merge([
                'campo' => request()->campo == 'tipo_telefone_1' ? 'tipo_telefone' : 'telefone',
                'valor' => ';'.request()->valor
            ]);

        if((strpos(request()->campo, 'cpf') !== false) || (strpos(request()->campo, 'cnpj') !== false))
        {
            if(isset(request()->valor))
            {
                $this->regraValor = [new CpfCnpj];
                $this->merge([
                    'valor' => apenasNumeros(request()->valor)
                ]);
            }
        }

        if(request()->campo == 'path')
            $this->regraValor = [
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:5120',
            ];

        if(strpos(request()->campo, 'dt_nascimento') !== false)
            $this->regraValor = [
                'date',
                'before_or_equal:' . Carbon::today()->subYears(18)->format('Y-m-d'),
            ];

        if((strpos(request()->campo, 'dt_expedicao') !== false) || (request()->campo == 'dt_inicio_atividade'))
            $this->regraValor = [
                'date',
                'before_or_equal:today',
            ];
        
        if(request()->campo == 'idregional')
            $this->regraValor = [
                'exists:regionais,idregional'
            ];
    }

    public function rules()
    {
        $classes = null;
        $todos = null;
        $campos_array = $this->service->getService('PreRegistro')->getNomesCampos();

        foreach($campos_array as $key => $campos)
        {
            $classes .= isset($classes) ? ','.$key : $key;
            $todos .= isset($todos) ? ','.$campos : $campos;
        }

        return [
            'valor' => $this->regraValor,
            'campo' => 'required|in:'.$todos,
            'classe' => 'required|in:'.$classes
        ];
    }

    public function messages()
    {
        return [
            'max' => request()->campo != 'path' ? 'Limite de :max caracteres' : 'Limite do tamanho do arquivo é de 5 MB',
            'in' => 'Campo não encontrado ou não permitido alterar',
            'required' => 'Falta dados para enviar a requisição',
            'mimetypes' => 'O arquivo não possue extensão permitida ou está com erro',
            'file' => 'Deve ser um arquivo',
            'date' => 'Deve ser tipo data',
            'before_or_equal' => strpos(request()->campo, 'dt_nascimento') !== false ? 'Deve ter 18 anos completos ou mais' : 'Data deve ser igual ou anterior a hoje',
            'exists' => 'Esta regional não existe',
        ];
    }
}
