<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PreRegistro extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros';
    protected $guarded = [];

    const STATUS_CRIADO = 'Sendo elaborado';
    const STATUS_ANALISE_INICIAL = 'Em análise inicial';
    const STATUS_CORRECAO = 'Aguardando correção';
    const STATUS_ANALISE_CORRECAO = 'Em análise da correção';
    const STATUS_APROVADO = 'Aprovado';
    const STATUS_NEGADO = 'Negado';
    const MENU = 'Contabilidade,Dados Gerais,Endereço,Contato / RT,Canal de Relacionamento,Anexos';
    const TOTAL_HIST = 1;

    private function horaUpdateHistorico()
    {
        $update = $this->getHistoricoArray()['update'];
        $updateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $update);
        $updateCarbon->addDay()->addHour();
        $updateCarbon->subMinutes($updateCarbon->minute);

        return $updateCarbon;
    }

    private function setHistorico()
    {
        $array = $this->getHistoricoArray();
        $totalTentativas = intval($array['tentativas']) < PreRegistro::TOTAL_HIST;

        if($totalTentativas)
            $array['tentativas'] = intval($array['tentativas']) + 1;
        $array['update'] = now()->format('Y-m-d H:i:s');

        return json_encode($array, JSON_FORCE_OBJECT);
    }

    private function getHistoricoCanEdit()
    {
        $array = $this->getHistoricoArray();
        $can = intval($array['tentativas']) < PreRegistro::TOTAL_HIST;
        $horaUpdate = $this->horaUpdateHistorico();
        
        return $can || (!$can && ($horaUpdate < now()));
    }

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
                if($valor['campo'] == 'negado')
                    $this->update(['justificativa' => null]);
                $texto = $this->formatTextoCorrecaoAdmin($valor['campo'], $valor['valor']);
                $final = [$campo => $texto];
                break;
            case 'confere_anexos':
                $texto = $this->formatTextoCorrecaoAdmin($campo, $valor);
                $final = [$campo => $texto];
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

    public static function getStatus()
    {
        $array = [
            PreRegistro::STATUS_CRIADO,
            PreRegistro::STATUS_ANALISE_INICIAL,
            PreRegistro::STATUS_CORRECAO,
            PreRegistro::STATUS_ANALISE_CORRECAO,
            PreRegistro::STATUS_APROVADO,
            PreRegistro::STATUS_NEGADO,
        ];
        sort($array, SORT_STRING);
        
        return $array;
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

    public function getLabelStatus($status = null)
    {
        $colorStatus = [
            PreRegistro::STATUS_CRIADO => '-light',
            PreRegistro::STATUS_ANALISE_INICIAL => '-primary',
            PreRegistro::STATUS_CORRECAO => '-secondary',
            PreRegistro::STATUS_ANALISE_CORRECAO => '-warning',
            PreRegistro::STATUS_APROVADO => '-success',
            PreRegistro::STATUS_NEGADO => '-danger',
        ];

        return isset($status) ? $colorStatus[$status] : $colorStatus[$this->status];
    }

    public function getLabelStatusUser()
    {
        $colorStatus = [
            PreRegistro::STATUS_CRIADO => '<span class="badge badge-secondary">' . PreRegistro::STATUS_CRIADO . '</span><small> - O formulário ainda está sendo elaborado pelo solicitante</small>',
            PreRegistro::STATUS_ANALISE_INICIAL => '<span class="badge badge-primary">' . PreRegistro::STATUS_ANALISE_INICIAL . '</span><small> - O formulário foi enviado pelo solicitante e está aguardando a análise pelo atendente</small>',
            PreRegistro::STATUS_CORRECAO => '<span class="badge badge-warning">' . PreRegistro::STATUS_CORRECAO . '</span><small> - O formulário foi analisado pelo atendente e possui correções a serem realizadas pelo solicitante</small>',
            PreRegistro::STATUS_ANALISE_CORRECAO => '<span class="badge badge-info">' . PreRegistro::STATUS_ANALISE_CORRECAO . '</span><small> - O formulário foi enviado pelo solicitante e está aguardando a análise da correção pelo atendente</small>',
            PreRegistro::STATUS_APROVADO => '<span class="badge badge-success">' . PreRegistro::STATUS_APROVADO . '</span><small> - O formulário foi aprovado pelo atendente</small>',
            PreRegistro::STATUS_NEGADO => '<span class="badge badge-danger">' . PreRegistro::STATUS_NEGADO . '</span><small> - O formulário foi negado pelo atendente com justificativa</small>',
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

    public function getHistoricoArray()
    {
        return json_decode($this->historico_contabil, true);
    }

    public function getNextUpdateHistorico()
    {
        return $this->horaUpdateHistorico()->format('d\/m\/Y, \à\s H:i');
    }

    public function getJustificativaNegado()
    {
        return isset($this->getJustificativaArray()['negado']) ? $this->getJustificativaArray()['negado'] : null;
    }

    public function userPodeCorrigir()
    {
        return $this->status == PreRegistro::STATUS_CORRECAO;
    }

    public function userPodeEditar()
    {
        return ($this->status == PreRegistro::STATUS_CRIADO) || $this->userPodeCorrigir();
    }

    public function atendentePodeEditar()
    {
        return in_array($this->status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]);
    }

    public function getCodigosJustificadosByAba($arrayAba)
    {
        $array = $this->getJustificativaArray();
        if($this->userPodeCorrigir() && ($array !== null))
        {
            $correcoes = array();
            foreach($array as $key => $campo)
                if(in_array($key, array_keys($arrayAba)))
                    array_push($correcoes, $arrayAba[$key]);

            natsort($correcoes);
            return $correcoes;
        }

        return null;
    }

    public function getTextosJustificadosByAba($arrayAba)
    {
        $array = $this->getJustificativaArray();
        if($this->userPodeCorrigir() && ($array !== null))
        {
            $correcoes = array();
            foreach($array as $key => $campo)
                if(in_array($key, array_keys($arrayAba)))
                    $correcoes[$arrayAba[$key]] = $campo;

            uksort($correcoes, "strnatcmp");
            return $correcoes;
        }

        return null;
    }

    public function canUpdateStatus($status)
    {
        $texto = $status != PreRegistro::STATUS_APROVADO ? 'não possui' : 'possui';
        $temp = $status == PreRegistro::STATUS_CORRECAO ? 'enviado para correção' : strtolower($status);
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

        if(!$anexosOk)
            return [
                'msg' => 'faltou anexos',
                'final' => false
            ];

        $verificaJustificativa = false;
        $verificaRegistro = false;
        if($status == PreRegistro::STATUS_APROVADO)
        {
            $verificaJustificativa = !isset($this->justificativa);
            $verificaRegistro = $this->userExterno->isPessoaFisica() || (!$this->userExterno->isPessoaFisica() && $this->pessoaJuridica->canUpdateStatus());
            if(!$verificaRegistro)
                return [
                    'msg' => 'faltou o registro do Responsável Técnico',
                    'final' => false
                ];
        }
        else
            $verificaJustificativa = $status == PreRegistro::STATUS_NEGADO ? isset($this->getJustificativaArray()['negado']) : 
                (isset($this->justificativa) && !isset($this->getJustificativaArray()['negado']));
        
        if(!$verificaJustificativa)
            return [
                'msg' => $texto . ' justificativa(s)',
                'final' => false
            ];

        $statusOK = in_array($this->status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]);
        if(!$statusOK)
            return [
                'msg' => 'não possui o status necessário para ser ' . $temp,
                'final' => false
            ];

        return [
            'msg' => $temp,
            'final' => true
        ];
    }

    public function setHistoricoStatus()
    {
        $historico = json_decode($this->historico_status, true);
        if(gettype($historico) != 'array')
            $historico = array();
        array_push($historico, $this->status . ';' . $this->updated_at);
        $this->update(['historico_status' => json_encode($historico, JSON_FORCE_OBJECT)]);
    }

    public function getHistoricoStatus()
    {
        return json_decode($this->historico_status, true);
    }

    public function setCamposEspelho($request)
    {
        $anexos = $this->anexos;
        $nomesAnexos = array();
        $final = array();
        foreach($anexos as $anexo)
            array_push($nomesAnexos, substr($anexo->path, strripos($anexo->path, '/') + 1));
        $request['path'] = implode(',', $nomesAnexos);

        if($this->status == PreRegistro::STATUS_CORRECAO)
            $this->update(['campos_editados' => array_diff_assoc(json_decode($this->campos_espelho, true), $request)]);
        $this->update(['campos_espelho' => json_encode($request, JSON_FORCE_OBJECT)]);
    }

    public function getCamposEditados()
    {
        return $this->status == PreRegistro::STATUS_ANALISE_CORRECAO ? json_decode($this->campos_editados, true) : [];
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
                $valido = $this->contabil->validarUpdateAjax($campo, $valor, $this->getHistoricoCanEdit());
                if(isset($valido))
                {
                    if($valido == 'notUpdate')
                        $valido = ['update' => $this->getNextUpdateHistorico()];
                    else
                        $valido == 'remover' ? $this->update(['contabil_id' => null]) : 
                        $this->update(['contabil_id' => $valido->id, 'historico_contabil' => $this->setHistorico()]);
                }
                else
                {
                    $this->contabil->updateAjax($campo, $valor);
                    $this->touch();
                }
                $resultado = $valido;
                break;
            case 'pessoaJuridica.responsavelTecnico':
                $valido = $this->pessoaJuridica->responsavelTecnico->validarUpdateAjax($campo, $valor, $gerenti, $this->pessoaJuridica->getHistoricoCanEdit());
                if(isset($valido))
                {
                    if($valido == 'notUpdate')
                        $valido = ['update' => $this->pessoaJuridica->getNextUpdateHistorico()];
                    else
                        $valido == 'remover' ? $this->pessoaJuridica->update(['responsavel_tecnico_id' => null]) : 
                        $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido->id, 'historico_rt' => $this->pessoaJuridica->setHistorico()]);
                }
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
                $valido = $campo == 'cpf' ? $classe::buscar($valor, $gerenti, $this->pessoaJuridica->getHistoricoCanEdit()) : null;
                if(isset($valido))
                {
                    if($valido == 'notUpdate')
                        $valido = ['update' => $this->pessoaJuridica->getNextUpdateHistorico()];
                    else
                        $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido->id, 'historico_rt' => $this->pessoaJuridica->setHistorico()]);
                }
                $resultado = $valido;
                break;
            case 'contabil':
                $valido = $campo == 'cnpj' ? $classe::buscar($valor, $this->getHistoricoCanEdit()) : null;
                if(isset($valido))
                {
                    if($valido == 'notUpdate')
                        $valido = ['update' => $this->getNextUpdateHistorico()];
                    else
                        $this->update(['contabil_id' => $valido->id, 'historico_contabil' => $this->setHistorico()]);
                }
                $resultado = $valido;
                break;
            case 'anexos':
                $anexos = $this->anexos();
                $valido = $classe::armazenar($anexos->count(), $valor, $this->userExterno->isPessoaFisica());
                if(isset($valido))
                {
                    $resultado = $anexos->create($valido);
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
