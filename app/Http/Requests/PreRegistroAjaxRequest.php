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
    private $classes;
    private $todos;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {
        $campos_array = $this->service->getService('PreRegistro')->getNomesCampos();
        if(auth()->guard('contabil')->check() && (strpos($this->campo, 'contabil') !== false))
            $this->merge([
                'campo' => null,
            ]);

        foreach($campos_array as $key => $campos)
        {
            $this->classes .= isset($this->classes) ? ','.$key : $key;
            $this->todos .= isset($this->todos) ? ','.$campos : $campos;
        }

        $this->regraValor = ['max:191'];
        $arrayIn = [
            'segmento' => implode(',', segmentos()),
            'uf' => implode(',', array_keys(estados())),
            'tipo_telefone' => implode(',', tipos_contatos()),
            'opcional_celular' => implode(',', opcoes_celular()),
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

        $telefoneOptions = [
            'tipo_telefone_1' => tipos_contatos(), 
            'telefone_1' => null, 
            'opcional_celular_1' => opcoes_celular(),
        ];
        if(in_array($this->campo, array_keys($telefoneOptions)))
        {
            // a quantidade de ';' define qual a chave do valor no campo
            // lembrar de alterar a quantidade de campos no model do PreRegistro em: getChaveValorTotal($valor = null)
            $flag = '';
            $total = intval(substr($this->campo, strripos($this->campo, '_') + 1));
            for($i = 0; $i  < $total; $i++)
                $flag .= ';';

            $implode = $telefoneOptions[$this->campo];
            if(isset(request()->valor) && isset($implode))
                $this->regraValor = 'in:' . mb_strtoupper(implode($flag . ',', $implode), 'UTF-8') . $flag;
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
        {
            $total = 0;
            if(!request()->hasFile('valor'))
                $this->merge([
                    'total' => $total
                ]);  
            else{
                $files = request()->file('valor');
                foreach($files as $value)
                    $total += round($value->getSize() / 1024);
                $this->merge([
                    'total' => $total > 5120 ? '' : $total
                ]);  
            }
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
