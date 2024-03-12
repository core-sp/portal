<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\CpfCnpj;
use Carbon\Carbon;
use App\Rules\Cnpj;
use App\Rules\Cpf;

class PreRegistroAjaxRequest extends FormRequest
{
    private $regraValor;
    private $service;
    private $classes;
    private $todos;
    private $msgUnique;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $this->msgUnique = 'Valor não permitido';
        $this->classes = implode(',', array_keys($this->service->getService('PreRegistro')->getNomesCampos()));
        $this->todos = implode(',', array_values($this->service->getService('PreRegistro')->getNomesCampos()));

        if(auth()->guard('contabil')->check() && (strpos($this->campo, 'contabil') !== false))
            $this->merge([
                'campo' => null,
            ]);

        $this->regraValor = ['max:191'];
        $arrayIn = [
            'segmento' => implode(',', segmentos()),
            'uf' => implode(',', array_keys(estados())),
            'tipo_telefone' => implode(',', tipos_contatos()),
            'opcional_celular' => implode(',', opcoes_celular()),
            'tipo_telefone_1' => implode(',', tipos_contatos()),
            'opcional_celular_1' => implode(',', opcoes_celular()),
            'sexo' => implode(',', array_keys(generos())),
            'estado_civil' => implode(',', estados_civis()),
            'nacionalidade' => implode(',', nacionalidades()),
            'naturalidade_estado' => implode(',', array_keys(estados())),
            'tipo_identidade' => implode(',', tipos_identidade()),
            'tipo_empresa' => implode(',', tipos_empresa()),
            'uf_empresa' => implode(',', array_keys(estados())),
            'sexo_rt' => implode(',', array_keys(generos())),
            'tipo_identidade_rt' => implode(',', tipos_identidade()),
            'uf_rt' => implode(',', array_keys(estados())),
        ];

        if(in_array(request()->campo, array_keys($arrayIn)) && isset(request()->valor))
            $this->regraValor = 'in:' . mb_strtoupper($arrayIn[request()->campo], 'UTF-8');

        if(in_array(request()->campo, ['opcional_celular[]', 'opcional_celular_1[]']))
            $this->merge([
                'campo' => str_replace('[]', '', request()->campo),
            ]);

        if((strpos(request()->campo, 'cpf') !== false) || (strpos(request()->campo, 'cnpj') !== false))
        {
            $this->merge(['valor' => apenasNumeros($this->valor)]);
            switch ($this->campo) {
                case 'cpf_rt':
                    $this->regraValor = ['nullable', new Cpf];
                    break;
                case 'cnpj_contabil':
                    $this->regraValor = ['nullable', new Cnpj, 'unique:users_externo,cpf_cnpj'];
                    $this->msgUnique = 'O CNPJ fornecido já consta no Portal com outro tipo de conta';
                    break;
                default:
                    $this->regraValor = ['nullable', new CpfCnpj];
            }
        }

        if(request()->campo == 'path')
        {
            $total = 0;
            if(request()->hasFile('valor'))
            {
                $files = request()->file('valor');
                foreach($files as $value){
                    $total += round($value->getSize() / 1024);
                    if($total > 5120){
                        $total = '';
                        break;
                    }
                }
            }
            $this->merge(['total' => $total]);
        }

        if(strpos(request()->campo, 'dt_nascimento') !== false)
            if(isset(request()->valor))
                $this->regraValor = [
                    'date_format:Y-m-d',
                    'before_or_equal:' . Carbon::today()->subYears(18)->format('Y-m-d'),
                ];

        if((strpos(request()->campo, 'dt_expedicao') !== false) || (request()->campo == 'dt_inicio_atividade'))
            if(isset(request()->valor))
                $this->regraValor = [
                    'date_format:Y-m-d',
                    'before_or_equal:today',
                ];
        
        if((request()->campo == 'idregional') && isset(request()->valor))
            $this->regraValor = [
                'exists:regionais,idregional'
            ];

        $notUpper = ['path', 'checkEndEmpresa', 'email_contabil'];
        if(!in_array($this->campo, $notUpper) && isset($this->valor) && !is_array($this->valor))
            $this->merge([
                'valor' => mb_strtoupper($this->valor, 'UTF-8')
            ]);
    }

    public function rules()
    {
        if(request()->campo == 'path')
            return [
                'valor' => 'required|array|min:1|max:15',
                'valor.*' => 'file|mimetypes:application/pdf,image/jpeg,image/png',
                'campo' => 'required|in:'.$this->todos,
                'classe' => 'required|in:'.$this->classes,
                'total' => 'required',
            ];

        return [
            'valor' => $this->regraValor,
            'campo' => 'required|in:'.$this->todos,
            'classe' => 'required|in:'.$this->classes
        ];
    }

    public function messages()
    {
        return [
            'max' => request()->campo != 'path' ? 'Limite de :max caracteres' : 'Existe mais de 15 arquivos',
            'total.required' => 'A soma do tamanho dos arquivos ultrapassa 5 MB',
            'campo.in' => 'Campo não encontrado ou não permitido alterar',
            'valor.in' => 'Valor não encontrado',
            'valor.unique' => $this->msgUnique,
            'valor.array' => 'Formato da requisição do upload do anexo está errado',
            'required' => 'Falta dados para enviar a requisição',
            'mimetypes' => 'O arquivo não possui extensão permitida ou está com erro',
            'file' => 'Deve ser um arquivo',
            'uploaded' => 'Falhou o upload por erro no servidor',
            'date_format' => 'Deve ser tipo data',
            'before_or_equal' => strpos(request()->campo, 'dt_nascimento') !== false ? 'Deve ter 18 anos completos ou mais' : 'Data deve ser igual ou anterior a hoje',
            'exists' => 'Esta regional não existe',
        ];
    }
}
