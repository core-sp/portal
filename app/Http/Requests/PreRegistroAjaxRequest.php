<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\CpfCnpj;
use Carbon\Carbon;

class PreRegistroAjaxRequest extends FormRequest
{
    private $regraValor;
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $this->regraValor = ['max:191'];

        if(in_array(request()->campo, ['opcional_celular[]', 'opcional_celular_1[]']))
            $this->merge([
                'campo' => str_replace('[]', '', request()->campo),
            ]);

        $telefoneOptions = ['tipo_telefone_1', 'telefone_1', 'opcional_celular_1'];
        if(in_array($this->campo, $telefoneOptions))
        {
            // a quantidade de ';' define qual a chave do valor no campo
            // lembrar de alterar a quantidade de campos no model do PreRegistro em: getChaveValorTotal($valor = null)
            $flag = '';
            $total = intval(substr($this->campo, strripos($this->campo, '_') + 1));
            for($i = 0; $i  < $total; $i++)
                $flag .= ';';
            $this->merge([
                'campo' => str_replace(substr($this->campo, strripos($this->campo, '_')), '', $this->campo),
                'valor' => request()->valor . $flag
            ]);
        }

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
            if(isset(request()->valor))
                $this->regraValor = [
                    'date',
                    'before_or_equal:' . Carbon::today()->subYears(18)->format('Y-m-d'),
                ];

        if((strpos(request()->campo, 'dt_expedicao') !== false) || (request()->campo == 'dt_inicio_atividade'))
            if(isset(request()->valor))
                $this->regraValor = [
                    'date',
                    'before_or_equal:today',
                ];
        
        if((request()->campo == 'idregional') && isset(request()->valor))
            $this->regraValor = [
                'exists:regionais,idregional'
            ];

        $notUpper = ['path', 'checkEndEmpresa', 'email_contabil'];
        if(!in_array($this->campo, $notUpper) && isset($this->valor))
            $this->merge([
                'valor' => mb_strtoupper($this->valor, 'UTF-8')
            ]);
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
            'mimetypes' => 'O arquivo não possui extensão permitida ou está com erro',
            'file' => 'Deve ser um arquivo',
            'date' => 'Deve ser tipo data',
            'before_or_equal' => strpos(request()->campo, 'dt_nascimento') !== false ? 'Deve ter 18 anos completos ou mais' : 'Data deve ser igual ou anterior a hoje',
            'exists' => 'Esta regional não existe',
        ];
    }
}
