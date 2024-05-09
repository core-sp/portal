<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\CpfCnpj;
use Carbon\Carbon;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PreRegistroAjaxRequest extends FormRequest
{
    private $regraValor;
    private $service;
    private $classes;
    private $todos;
    private $msgUnique;
    private $msgNotIn;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $this->msgUnique = 'Valor não permitido';
        $this->msgNotIn = 'Valor inválido';
        $this->classes = implode(',', array_keys($this->service->getService('PreRegistro')->getNomesCampos()));
        $this->todos = implode(',', array_values($this->service->getService('PreRegistro')->getNomesCampos()));

        if((strpos($this->campo, 'contabil') !== false) && auth()->guard('contabil')->check())
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

        if(in_array($this->campo, array_keys($arrayIn)) && isset($this->valor))
            $this->regraValor = 'in:' . mb_strtoupper($arrayIn[$this->campo], 'UTF-8');

        if(in_array($this->campo, ['opcional_celular[]', 'opcional_celular_1[]']))
            $this->merge([
                'campo' => str_replace('[]', '', $this->campo),
            ]);

        if((strpos($this->campo, 'cpf') !== false) || (strpos($this->campo, 'cnpj') !== false))
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
                case 'cpf_cnpj_socio':
                    $pr = auth()->guard('user_externo')->check() ? auth()->guard('user_externo')->user()->preRegistro : 
                    auth()->guard('contabil')->user()->preRegistros->find($this->preRegistro);

                    if(!isset($pr))
                        throw new ModelNotFoundException("No query results for model [App\PreRegistro] ");

                    $rt_cpf_socio = !$pr->userExterno->isPessoaFisica() && $pr->pessoaJuridica->possuiRT()? $pr->pessoaJuridica->responsavelTecnico->cpf : '';

                    $this->regraValor = ['nullable', new CpfCnpj, 'unique:contabeis,cnpj', 'not_in:' . $pr->userExterno->cpf_cnpj . ',' . $rt_cpf_socio];
                    $this->msgUnique = 'O CNPJ fornecido já consta no Portal como Contabilidade, não pode ser sócio';
                    $this->msgNotIn = $rt_cpf_socio == $this->valor ? 'Para incluir o CPF do RT, deve confirmar no item 5.1' : 'O CNPJ do usuário externo deste pré-registro não pode ser sócio';
                    break;
                default:
                    $this->regraValor = ['nullable', new CpfCnpj];
            }
        }

        if($this->campo == 'path')
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

        if(strpos($this->campo, 'dt_nascimento') !== false)
            if(isset($this->valor))
                $this->regraValor = [
                    'date_format:Y-m-d',
                    'before_or_equal:' . Carbon::today()->subYears(18)->format('Y-m-d'),
                ];

        if((strpos($this->campo, 'dt_expedicao') !== false) || ($this->campo == 'dt_inicio_atividade'))
            if(isset($this->valor))
                $this->regraValor = [
                    'date_format:Y-m-d',
                    'before_or_equal:today',
                ];
        
        if(($this->campo == 'idregional') && isset($this->valor))
            $this->regraValor = [
                'exists:regionais,idregional'
            ];

        $notUpper = ['path', 'checkEndEmpresa', 'email_contabil', 'checkRT_socio'];
        if(!in_array($this->campo, $notUpper) && isset($this->valor) && !is_array($this->valor))
            $this->merge([
                'valor' => mb_strtoupper($this->valor, 'UTF-8')
            ]);

        // Sócios
        $this->merge([
            'id_socio' => (($this->campo == 'cpf_cnpj_socio') && (strlen($this->valor) >= 11)) || 
            (($this->campo == 'checkRT_socio') && ($this->valor == 'on')) ? 0 : $this->id_socio,
        ]);

        if((strpos($this->campo, '_socio') !== false) && !isset($this->id_socio))
            $this->merge([
                'campo' => null,
            ]);

        if(strpos($this->campo, '_socio') !== false)
            $this->merge([
                'campo' => [$this->id_socio, $this->campo],
            ]);
    }

    public function rules()
    {
        if($this->campo == 'path')
            return [
                'valor' => 'required|array|min:1|max:15',
                'valor.*' => 'file|mimetypes:application/pdf,image/jpeg,image/png',
                'campo' => 'required|in:'.$this->todos,
                'classe' => 'required|in:'.$this->classes,
                'total' => 'required',
            ];

        if(is_array($this->campo))
            return [
                'valor' => $this->regraValor,
                'campo.*' => 'required|in:'.$this->todos . ',' . $this->campo[0],
                'classe' => 'required|in:'.$this->classes,
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
            'max' => $this->campo != 'path' ? 'Limite de :max caracteres' : 'Existe mais de 15 arquivos',
            'total.required' => 'A soma do tamanho dos arquivos ultrapassa 5 MB',
            'campo.in' => 'Campo não encontrado ou não permitido alterar',
            'campo.*.in' => 'Campo não encontrado ou não permitido alterar',
            'valor.in' => 'Valor não encontrado',
            'valor.not_in' => $this->msgNotIn,
            'valor.unique' => $this->msgUnique,
            'valor.array' => 'Formato da requisição do upload do anexo está errado',
            'required' => 'Falta dados para enviar a requisição',
            'mimetypes' => 'O arquivo não possui extensão permitida ou está com erro',
            'file' => 'Deve ser um arquivo',
            'uploaded' => 'Falhou o upload por erro no servidor',
            'date_format' => 'Deve ser tipo data',
            'before_or_equal' => strpos($this->campo, 'dt_nascimento') !== false ? 'Deve ter 18 anos completos ou mais' : 'Data deve ser igual ou anterior a hoje',
            'exists' => 'Esta regional não existe',
        ];
    }
}
