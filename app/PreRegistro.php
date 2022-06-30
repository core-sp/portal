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
        $tipo = explode(';', $this->tipo_telefone);

        return [
            isset($tipo[0]) ? $tipo[0] : null,
            isset($tipo[1]) ? $tipo[1] : null
        ];
    }

    public function getTelefone()
    {
        $tel = explode(';', $this->telefone);

        return [
            isset($tel[0]) ? $tel[0] : null,
            isset($tel[1]) ? $tel[1] : null
        ];
    }

    public function getOpcionalCelular()
    {
        $options = explode(';', $this->opcional_celular);

        return [
            isset($options[0]) ? explode(',', $options[0]) : null,
            isset($options[1]) ? explode(',', $options[1]) : null,
        ];
    }

    public function getJustificativaArray()
    {
        return json_decode($this->justificativa, true);
    }

    private function setOpcionalCelular($opcionais, $valor)
    {
        $valor = strpos($valor, ';') !== false ? str_replace(';', '', $valor) : $valor;
        
        // remover do explode os valores vazios
        foreach($opcionais as $key => $value)         
            if(empty($value))
                unset($opcionais[$key]);

        if(isset($opcionais) && in_array($valor, $opcionais))
            unset($opcionais[array_search($valor, $opcionais)]);
        else
            array_push($opcionais, $valor);

        return implode(',', $opcionais);
    }

    private function validarUpdateAjax($campo, $valor)
    {
        $tipo = explode(';', $this->tipo_telefone);
        $tel = explode(';', $this->telefone);
        $opcional = explode(';', $this->opcional_celular);
        $tipo[1] = isset($tipo[1]) ? $tipo[1] : '';
        $tel[1] = isset($tel[1]) ? $tel[1] : '';
        $opcional[1] = isset($opcional[1]) ? $opcional[1] : '';

        switch ($campo) {
            case 'tipo_telefone':
                $valor = strpos($valor, ';') !== false ? $tipo[0].$valor : $valor.';'.$tipo[1];
                break;
            case 'telefone':
                $valor = strpos($valor, ';') !== false ? $tel[0].$valor : $valor.';'.$tel[1];
                break;
            case 'opcional_celular':
                $valor = strpos($valor, ';') !== false ? 
                $opcional[0] . ';' . $this->setOpcionalCelular(explode(',', $opcional[1]), $valor) : 
                $this->setOpcionalCelular(explode(',', $opcional[0]), $valor) . ';' . $opcional[1]; 
                break;
        }

        // Pergunta não será salva, apenas para reforçar a mensagem sobre ser Representante Comercial
        if($campo == 'pergunta')
            return null;

        return [$campo => $valor];
    }

    private function validarUpdate($arrayCampos)
    {
        $tipo = explode(';', $this->tipo_telefone);
        $tel = explode(';', $this->telefone);
        $opcional = explode(';', $this->opcional_celular);
        $tipo[1] = isset($tipo[1]) ? $tipo[1] : '';
        $tel[1] = isset($tel[1]) ? $tel[1] : '';
        $opcional[1] = isset($opcional[1]) ? $opcional[1] : '';

        $arrayCampos['tipo_telefone'] .= isset($arrayCampos['tipo_telefone_1']) ? ';' . $arrayCampos['tipo_telefone_1'] : ';';
        $arrayCampos['telefone'] .= isset($arrayCampos['telefone_1']) ? ';' . $arrayCampos['telefone_1'] : ';';
        $arrayCampos['opcional_celular'] .= isset($arrayCampos['opcional_celular_1']) ? ';' . $arrayCampos['opcional_celular_1'] : ';';
        unset($arrayCampos['tipo_telefone_1']);
        unset($arrayCampos['telefone_1']);
        unset($arrayCampos['opcional_celular_1']);

        return $arrayCampos;
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
