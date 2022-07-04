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

    private function validarUpdateAjax($campo, $valor)
    {
        switch ($campo) {
            case 'tipo_telefone':
                $tipos = explode(';', $this->tipo_telefone);
                $array = $this->getChaveValorTotal($valor);
                $valor = $this->setUmCampo($tipos, $array);
                break;
            case 'telefone':
                $tels = explode(';', $this->telefone);
                $array = $this->getChaveValorTotal($valor);
                $valor = $this->setUmCampo($tels, $array);
                break;
            case 'opcional_celular':
                $options = explode(';', $this->opcional_celular);
                $array = $this->getChaveValorTotal($valor);
                $valor = $this->setCheckbox($options, $array);
                break;
        }

        // Pergunta não será salva, apenas para reforçar a mensagem sobre ser Representante Comercial
        if($campo == 'pergunta')
            return null;

        return [$campo => $valor];
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

    // seguir ordem de apresentação dos campos nas blades
    public static function codigosPreRegistro()
    {
        return [
            '3.1' => 'pergunta',
            '3.2' => 'segmento',
            '3.3' => 'idregional',
            '6.1' => 'tipo_telefone',
            '6.2' => 'telefone',
            '6.3' => 'opcional_celular',
            '4.1' => 'cep',
            '4.2' => 'bairro',
            '4.3' => 'logradouro',
            '4.4' => 'numero',
            '4.5' => 'complemento',
            '4.6' => 'cidade',
            '4.7' => 'uf',
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
