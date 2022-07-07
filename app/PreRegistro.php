<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreRegistro extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros';
    protected $guarded = [];

    // Ideias de status. A verificar
    const STATUS_ANALISE_INICIAL = 'Em análise inicial';
    const STATUS_CORRECAO = 'Aguardando correção';
    const STATUS_ANALISE_CORRECAO = 'Em análise da correção';
    const STATUS_APROVADO = 'Aprovado';
    const STATUS_NEGADO = 'Negado';
    const STATUS_PENDENTE_PGTO = 'Pendente de pagamento';
    const MENU = 'Contabilidade,Dados Gerais,Endereço,Contato / RT,Canal de Relacionamento,Anexos';

    private function getChaveValorTotal($valor = null)
    {
        // total é a quantidade de campos que deve armazenar
        $total = 2;

        return [
            'chave' => substr_count($valor, ';'),
            'valor' => str_replace(';', '', $valor),
            'total' => $total
        ];
    }

    private function setUmCampo($arrayValor, $arrayOpcoes)
    {
        for($i = 0; $i < $arrayOpcoes['total']; $i++)
            if($arrayOpcoes['chave'] == $i)
                $arrayValor[$i] = $arrayOpcoes['valor'];
            else
                $arrayValor[$i] = isset($arrayValor[$i]) && (strlen($arrayValor[$i]) > 0) ? $arrayValor[$i] : '';

        return implode(';', $arrayValor);
    }

    private function setCheckbox($arrayValor, $arrayOpcoes)
    {
        for($i = 0; $i < $arrayOpcoes['total']; $i++)
        {
            if($arrayOpcoes['chave'] == $i)
            {
                $temp = array();
                if(isset($arrayValor[$i]))
                {
                    $temp = explode(',', $arrayValor[$i]);
                    // remover do explode os valores vazios
                    foreach($temp as $key => $value)         
                        if(empty($value))
                            unset($temp[$key]);
                }

                if(isset($temp) && in_array($arrayOpcoes['valor'], $temp))
                    unset($temp[array_search($arrayOpcoes['valor'], $temp)]);
                else
                    array_push($temp, $arrayOpcoes['valor']);
                $arrayValor[$i] = implode(',', $temp);
            }
            else
                $arrayValor[$i] = isset($arrayValor[$i]) && (strlen($arrayValor[$i]) > 0) ? $arrayValor[$i] : '';
        }

        return implode(';', $arrayValor);
    }

    private function formatTextoCorrecaoAdmin($campo, $valor)
    {
        $original = $campo == 'confere_anexos' ? $this->confere_anexos : $this->justificativa;
        $texto = json_decode($original, true);
        
        if($campo != 'confere_anexos')
        {                
            if(isset($valor) && (strlen($valor) > 0))
                $texto[$campo] = $valor;
            elseif(isset($texto[$campo]))
                unset($texto[$campo]);
        
            $texto = count($texto) == 0 ? null : json_encode($texto, JSON_FORCE_OBJECT);
        }
        else
        {
            if(!isset($texto[$valor]))
                $texto[$valor] = "OK";
            else
                unset($texto[$valor]);
        
            $texto = count($texto) == 0 ? null : json_encode($texto, JSON_FORCE_OBJECT);
        }
        
        return $texto;
    }

    private function validarUpdateAjax($campo, $valor)
    {
        $final = [$campo => $valor];

        switch ($campo) {
            case 'tipo_telefone':
            case 'telefone':
                $temp = $campo == 'tipo_telefone' ? explode(';', $this->tipo_telefone) : explode(';', $this->telefone);
                $array = $this->getChaveValorTotal($valor);
                $valor = $this->setUmCampo($temp, $array);
                $final = [$campo => $valor];
                break;
            case 'opcional_celular':
                $options = explode(';', $this->opcional_celular);
                $array = $this->getChaveValorTotal($valor);
                $valor = $this->setCheckbox($options, $array);
                $final = [$campo => $valor];
                break;
            case 'justificativa':
                $texto = $this->formatTextoCorrecaoAdmin($valor['campo'], $valor['valor']);
                $final = [
                    'idusuario' => auth()->user()->idusuario,
                    $campo => $texto
                ];
                break;
            case 'confere_anexos':
                $texto = $this->formatTextoCorrecaoAdmin($campo, $valor);
                $final = [
                    'idusuario' => auth()->user()->idusuario,
                    $campo => $texto
                ];
                break;
            case 'pergunta':
                // Pergunta não será salva, apenas para reforçar a mensagem sobre ser Representante Comercial
                $final = null;
                break;
        }

        return $final;
    }

    private function validarUpdate($arrayCampos)
    {
        $camposObrig = [
            'tipo_telefone' => [
                0 => $arrayCampos['tipo_telefone']
            ],
            'telefone' => [
                0 => $arrayCampos['telefone']
            ],
            'opcional_celular' => [
                0 => isset($arrayCampos['opcional_celular']) ? $arrayCampos['opcional_celular'] : ''
            ]
        ];

        foreach($camposObrig as $key => $valor)
        {
            $total = $this->getChaveValorTotal()['total'];
            for($i = 1; $i < $total; $i++)
            {
                $chave = $key . '_' . $i;
                if(isset($arrayCampos[$chave]))
                    $camposObrig[$key][$i] = $arrayCampos[$chave];
                else
                    $camposObrig[$key][$i] = '';
                unset($arrayCampos[$chave]);
            }
            $arrayCampos[$key] = implode(';', $camposObrig[$key]);
        }

        return $arrayCampos;
    }

    public static function camposPreRegistro()
    {
        return [
            'p1' => 'segmento',
            'p2' => 'idregional',
            'p3' => 'tipo_telefone',
            'p4' => 'telefone',
            'p5' => 'opcional_celular',
            'p6' => 'cep',
            'p7' => 'bairro',
            'p8' => 'logradouro',
            'p9' => 'numero',
            'p10' => 'complemento',
            'p11' => 'cidade',
            'p12' => 'uf',
        ];
    }

    public function userExterno()
    {
        return $this->belongsTo('App\UserExterno')->withTrashed();
    }

    public function regional()
    {
        return $this->belongsTo('App\Regional', 'idregional');
    }

    public function contabil()
    {
        return $this->belongsTo('App\Contabil')->withTrashed();
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function pessoaFisica()
    {
        return $this->hasOne('App\PreRegistroCpf')->withTrashed();
    }

    public function pessoaJuridica()
    {
        return $this->hasOne('App\PreRegistroCnpj')->withTrashed();
    }

    public function anexos()
    {
        return $this->hasMany('App\Anexo');
    }

    public function getLabelStatus()
    {
        $colorStatus = [
            PreRegistro::STATUS_ANALISE_INICIAL => '<span class="font-weight-bolder text-primary">' . PreRegistro::STATUS_ANALISE_INICIAL . '</span>',
            PreRegistro::STATUS_CORRECAO => '<span class="font-weight-bolder text-warning">' . PreRegistro::STATUS_CORRECAO . '</span>',
            PreRegistro::STATUS_ANALISE_CORRECAO => '<span class="font-weight-bolder text-info">' . PreRegistro::STATUS_ANALISE_CORRECAO . '</span>',
            PreRegistro::STATUS_APROVADO => '<span class="font-weight-bolder text-success">' . PreRegistro::STATUS_APROVADO . '</span>',
            PreRegistro::STATUS_NEGADO => '<span class="font-weight-bolder text-danger">' . PreRegistro::STATUS_NEGADO . '</span>',
        ];

        return isset($colorStatus[$this->status]) ? $colorStatus[$this->status] : null;
    }

    public function getTipoTelefone()
    {
        $tipos = explode(';', $this->tipo_telefone);

        foreach($tipos as $key => $valor)
            $tipos[$key] = isset($valor) && (strlen($valor) > 0) ? $valor : null;

        return $tipos;
    }

    public function getTelefone()
    {
        $tels = explode(';', $this->telefone);

        foreach($tels as $key => $valor)
            $tels[$key] = isset($valor) && (strlen($valor) > 0) ? $valor : null;

        return $tels;
    }

    public function getOpcionalCelular()
    {
        $options = explode(';', $this->opcional_celular);

        foreach($options as $key => $valor)
            $options[$key] = isset($valor) && (strlen($valor) > 0) ? explode(',', $valor) : null;

        return $options;
    }

    public function getJustificativaArray()
    {
        return json_decode($this->justificativa, true);
    }

    public function getConfereAnexosArray()
    {
        return json_decode($this->confere_anexos, true);
    }

    public function getCodigosJustificadosByAba($arrayAba)
    {
        if($this->status == PreRegistro::STATUS_CORRECAO)
        {
            $correcoes = array();
            $array = $this->getJustificativaArray();
            foreach($array as $key => $campo)
                if(in_array($key, array_keys($arrayAba)))
                    array_push($correcoes, $arrayAba[$key]);

            $final = implode(' * ', $correcoes);
            return strlen($final) > 0 ? $final : null;
        }

        return null;
    }

    public function getTextosJustificadosByAba($arrayAba)
    {
        if($this->status == PreRegistro::STATUS_CORRECAO)
        {
            $correcoes = array();
            $array = $this->getJustificativaArray();
            foreach($array as $key => $campo)
                if(in_array($key, array_keys($arrayAba)))
                    $correcoes[$arrayAba[$key]] = $campo;

            return $correcoes;
        }

        return null;
    }

    public function canUpdateStatus($status)
    {
        $anexosOk = true;

        if($status == PreRegistro::STATUS_APROVADO)
        {
            $tipos = $this->anexos->first()->getObrigatoriosPreRegistro();
            $anexos = $this->getConfereAnexosArray();
            
            if($anexos !== null)
                foreach($anexos as $key => $value)
                    if(in_array($key, $tipos))
                        unset($tipos[array_search($key, $tipos)]);

            $anexosOk = count($tipos) == 0;
        }

        $verificaJustificativa = false;
        if($status == PreRegistro::STATUS_APROVADO)
            $verificaJustificativa = !isset($this->justificativa);
        else
            $verificaJustificativa = $status == PreRegistro::STATUS_NEGADO ? isset($this->getJustificativaArray()['negado']) : isset($this->justificativa);
        $statusOK = in_array($this->status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]);

        return $statusOK && $verificaJustificativa && $anexosOk;
    }

    public function atualizarAjax($classe, $campo, $valor, $gerenti)
    {
        $resultado = null;

        switch ($classe) {
            case 'preRegistro':
                $valido = $this->validarUpdateAjax($campo, $valor);
                if(isset($valido))
                    $this->update($valido);
                break;
            case 'pessoaFisica':
                $this->pessoaFisica->update([$campo => $valor]);
                break;
            case 'pessoaJuridica':
                $valido = $this->pessoaJuridica->validarUpdateAjax($campo, $valor);
                $this->pessoaJuridica->update($valido);
                break;
            case 'contabil':
                $valido = $this->contabil->validarUpdateAjax($campo, $valor);
                if(isset($valido))
                    $resultado = $this->update(['contabil_id' => $valido == 'remover' ? null : $valido->id]);
                else
                {
                    $this->contabil->updateAjax($campo, $valor);
                    $this->touch();
                }
                $resultado = $valido;
                break;
            case 'pessoaJuridica.responsavelTecnico':
                $valido = $this->pessoaJuridica->responsavelTecnico->validarUpdateAjax($campo, $valor, $gerenti);
                if(isset($valido))
                    $resultado = $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido == 'remover' ? null : $valido->id]);
                else
                {
                    $this->pessoaJuridica->responsavelTecnico->updateAjax($campo, $valor);
                    $this->touch();
                }
                $resultado = $valido;
                break;
        }

        return $resultado;
    }

    public function criarAjax($classe, $relacao, $campo, $valor, $gerenti)
    {
        $resultado = null;

        switch ($relacao) {
            case 'pessoaJuridica.responsavelTecnico':
                $valido = $campo == 'cpf' ? $classe::buscar($valor, $gerenti) : null;
                if(isset($valido))
                    $resultado = $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido->id]);
                $resultado = $valido;
                break;
            case 'contabil':
                $valido = $campo == 'cnpj' ? $classe::buscar($valor) : null;
                if(isset($valido))
                    $resultado = $this->update(['contabil_id' => $valido->id]);
                $resultado = $valido;
                break;
            case 'anexos':
                $anexos = $this->anexos();
                $valido = $classe::armazenar($anexos->count(), $valor);
                if(isset($valido))
                {
                    $resultado = $anexos->create([$campo => $valido, 'nome_original' => $valor->getClientOriginalName()]);
                    $this->touch();
                }
                break;
        }

        return $resultado;
    }

    public function salvar($classe, $arrayCampos, $gerenti, $criar = null)
    {
        $resultado = null;
        $valido = null;
        if(isset($criar))
            $valido = $criar::atualizar($arrayCampos, $gerenti);
        
        switch ($classe) {
            case 'preRegistro':
                $valido = $this->validarUpdate($arrayCampos);
                $resultado = $this->update($valido);
                break;
            case 'pessoaFisica':
                $resultado = $this->pessoaFisica->update($arrayCampos);
                break;
            case 'pessoaJuridica':
                $valido = $this->pessoaJuridica->validarUpdate($arrayCampos);
                $resultado = $this->pessoaJuridica->update($valido);
                break;
            case 'contabil':
                if(!isset($valido))
                    $valido = $this->contabil->atualizar($arrayCampos);
                $resultado = $this->update(['contabil_id' => $valido == 'remover' ? null : $valido->id]);
                $this->touch();
                break;
            case 'pessoaJuridica.responsavelTecnico':
                if(!isset($valido))
                    $valido = $this->pessoaJuridica->responsavelTecnico->atualizar($arrayCampos, $gerenti);
                $resultado = $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido == 'remover' ? null : $valido->id]);
                $this->touch();
                break;
        }

        return $resultado;
    }
}
