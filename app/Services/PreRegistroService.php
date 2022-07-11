<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Anexo;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Facades\Storage;
use App\Repositories\GerentiRepositoryInterface;
use Carbon\Carbon;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\Traits\Gerenti;
use App\Events\CrudEvent;

class PreRegistroService implements PreRegistroServiceInterface {

    use Gerenti;

    const RELATION_ANEXOS = "anexos";
    const RELATION_CONTABIL = "contabil";
    const RELATION_PF = "pessoaFisica";
    const RELATION_PJ = "pessoaJuridica";
    const RELATION_PRE_REGISTRO = "preRegistro";
    const RELATION_RT = "pessoaJuridica.responsavelTecnico";

    private $totalFiles;
    private $variaveis;

    public function __construct()
    {
        $this->totalFiles = Anexo::TOTAL_PRE_REGISTRO;
        $this->variaveis = [
            'singular' => 'pré-registro',
            'singulariza' => 'o pré-registro',
            'pluraliza' => 'pré-registros',
            'plural' => 'pre-registros',
            'busca' => 'pre-registros',
            'slug' => 'pre-registros',
            'mostra' => 'pre-registro'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'CPF / CNPJ',
            'Nome',
            'Regional',
            'Atualizado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        // $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        foreach($resultados as $resultado) 
        {
            $texto = $resultado->atendentePodeEditar() ? 'Editar' : 'Visualizar';
            $cor = $resultado->atendentePodeEditar() ? 'primary' : 'info';
            $acoes = '<a href="'.route('preregistro.view', $resultado->id).'" class="btn btn-sm btn-' . $cor . '">'. $texto .'</a> ';
            // if($userPodeEditar)
            //     $acoes .= '<a href="'.route('regionais.edit', $resultado->idregional).'" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->id,
                formataCpfCnpj($resultado->userExterno->cpf_cnpj),
                $resultado->userExterno->nome,
                $resultado->regional->regional,
                formataData($resultado->updated_at),
                isset($resultado->user->nome) ? 
                $resultado->getLabelStatus() . '<small class="d-block">Atualizado por: <strong>'.$resultado->user->nome.'</strong></small>' : $resultado->getLabelStatus(),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    private function getMenu()
    {
        return explode(',', PreRegistro::MENU);
    }

    private function getRelacoes()
    {
        return [
            'App\Anexo' => PreRegistroService::RELATION_ANEXOS,
            'App\Contabil' => PreRegistroService::RELATION_CONTABIL,
            'App\PreRegistroCpf' => PreRegistroService::RELATION_PF,
            'App\PreRegistroCnpj' => PreRegistroService::RELATION_PJ,
            'App\PreRegistro' => PreRegistroService::RELATION_PRE_REGISTRO,
            'App\ResponsavelTecnico' => PreRegistroService::RELATION_RT,
        ];
    }

    private function getCodigos()
    {
        $codigos = array();
        $relacoes = $this->getRelacoes();

        foreach($relacoes as $key => $model)
            $codigos[$model] = $key::camposPreRegistro();
        
        return $codigos;
    }

    private function limparNomeCamposAjax($classe, $campo)
    {
        $chave = false;
        $campos = $this->getCodigos()[$classe];
        $siglas = [
            PreRegistroService::RELATION_ANEXOS => null,
            PreRegistroService::RELATION_PRE_REGISTRO => null,
            PreRegistroService::RELATION_PF => null,
            PreRegistroService::RELATION_PJ => '_empresa',
            PreRegistroService::RELATION_CONTABIL => '_contabil',
            PreRegistroService::RELATION_RT => '_rt',
        ];

        foreach($campos as $key => $cp)
        {
            $temp = $cp . $siglas[$classe];
            if(($campo == $cp) || ($campo == $temp))
            {
                $chave = $key;
                break;
            }
        }

        return isset($campos[$chave]) ? $campos[$chave] : $campo;
    }

    private function formatCampos($request)
    {
        if(isset($request['opcional_celular']))
            $request['opcional_celular'] = implode(',', $request['opcional_celular']);
        else
            $request['opcional_celular'] = null;
        if(isset($request['opcional_celular_1']))
            $request['opcional_celular_1'] = implode(',', $request['opcional_celular_1']);

        // Pergunta não será salva, apenas para reforçar a mensagem sobre ser Representante Comercial
        if(isset($request['pergunta']))
            unset($request['pergunta']);
        
        return $request;
    }

    private function limparNomeCampos($externo, $request)
    {
        $request = $this->formatCampos($request);
        $camposLimpos = array();
        $classe = null;
        $camposView = $this->getNomesCampos();
        $array = $externo->isPessoaFisica() ? [
            PreRegistroService::RELATION_PRE_REGISTRO => explode(',', $camposView[PreRegistroService::RELATION_PRE_REGISTRO]),
            PreRegistroService::RELATION_CONTABIL => explode(',', $camposView[PreRegistroService::RELATION_CONTABIL]),
            PreRegistroService::RELATION_PF => explode(',', $camposView[PreRegistroService::RELATION_PF]),
        ] : [
            PreRegistroService::RELATION_PRE_REGISTRO => explode(',', $camposView[PreRegistroService::RELATION_PRE_REGISTRO]),
            PreRegistroService::RELATION_CONTABIL => explode(',', $camposView[PreRegistroService::RELATION_CONTABIL]),
            PreRegistroService::RELATION_PJ => explode(',', $camposView[PreRegistroService::RELATION_PJ]),
            PreRegistroService::RELATION_RT => explode(',', $camposView[PreRegistroService::RELATION_RT]),
        ];

        foreach($request as $key => $value)
        {
            foreach($array as $relacao => $campos)
                if(in_array($key, $campos))
                {
                    $classe = $relacao;
                    break;
                }

            if(isset($classe))
            {
                if(isset($value))
                    $camposLimpos[$classe][$this->limparNomeCamposAjax($classe, $key)] = in_array($key, ['checkEndEmpresa', 'email_contabil']) ? $value : mb_strtoupper($value, 'UTF-8');
                else
                    $camposLimpos[$classe][$this->limparNomeCamposAjax($classe, $key)] = null;
            }
        }

        return $camposLimpos;
    }

    private function getNomeClasses()
    {
        return [
            PreRegistroService::RELATION_ANEXOS,
            PreRegistroService::RELATION_CONTABIL,
            PreRegistroService::RELATION_PF,
            PreRegistroService::RELATION_PJ,
            PreRegistroService::RELATION_PRE_REGISTRO,
            PreRegistroService::RELATION_RT,
        ];
    }

    private function getRTGerenti(GerentiRepositoryInterface $gerentiRepository, $cpf)
    {
        if(!isset($cpf) || (strlen($cpf) != 11))
            return null;

        $resultadosGerenti = $gerentiRepository->gerentiBusca("", null, $cpf);
        $ass_id = null;
        $nome = null;
        $gerenti = array();

        // Para testar: colocar 5 em "ASS_TP_ASSOC" em gerentiBusca() em GerentiRepositoryMock
        if(count($resultadosGerenti) > 0)
            foreach($resultadosGerenti as $resultado)
            {
                $naoCancelado = $resultado['CANCELADO'] == "F";
                $ativo = $resultado['ASS_ATIVO'] == "T";
                $tipo = $resultado["ASS_TP_ASSOC"] == $this->getCodigoRT();

                if($naoCancelado && $ativo && $tipo)
                {
                    $ass_id = $resultado["ASS_ID"];
                    $gerenti['nome'] = $resultado["ASS_NOME"];
                    $gerenti['registro'] = $resultado["ASS_REGISTRO"];
                }
            }
        
        if(isset($ass_id))
        {
            $resultadosGerenti = utf8_converter($gerentiRepository->gerentiDadosGeraisPF($ass_id));

            $gerenti['nome_mae'] = isset($resultadosGerenti['Nome da mãe']) ? $resultadosGerenti['Nome da mãe'] : null;
            $gerenti['nome_pai'] = isset($resultadosGerenti['Nome do pai']) ? $resultadosGerenti['Nome do pai'] : null;
            $gerenti['identidade'] = isset($resultadosGerenti['identidade']) ? $resultadosGerenti['identidade'] : null;
            $gerenti['orgao_emissor'] = isset($resultadosGerenti['emissor']) ? $resultadosGerenti['emissor'] : null;
            $gerenti['dt_expedicao'] = isset($resultadosGerenti['expedicao']) ? 
                Carbon::createFromFormat('d/m/Y', $resultadosGerenti['expedicao'])->format('Y-m-d') : null;
            $gerenti['dt_nascimento'] = isset($resultadosGerenti['Data de nascimento']) ? 
                Carbon::createFromFormat('d/m/Y', $resultadosGerenti['Data de nascimento'])->format('Y-m-d') : null;
            $gerenti['sexo'] = null;
            if(isset($resultadosGerenti['Sexo']))
                $gerenti['sexo'] = $resultadosGerenti['Sexo'] == "MASCULINO" ? "M" : "F";
            $gerenti['cpf'] = $cpf;
        }

        return $gerenti;
    }

    private function getAbasCampos($externo)
    {
        $pf = 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade,nome_mae,nome_pai,tipo_identidade,identidade,orgao_emissor,dt_expedicao';
        $pj = 'razao_social,capital_social,nire,tipo_empresa,dt_inicio_atividade,inscricao_municipal,inscricao_estadual';
        $dadosGerais = $externo->isPessoaFisica() ? $pf : $pj;

        // A índice é referente a índice do menu
        // Colocar na ordem dos campos nas blades
        return [
            'cnpj_contabil,nome_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $dadosGerais . ',segmento,idregional,pergunta',
            'cep,bairro,logradouro,numero,complemento,cidade,uf,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            'cpf_rt,registro,nome_rt,nome_social_rt,dt_nascimento_rt,sexo_rt,tipo_identidade_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt',
            'tipo_telefone,telefone,opcional_celular,tipo_telefone_1,telefone_1,opcional_celular_1',
            'path',
        ];
    }

    // Fazer os códigos automaticos
    private function getCodigosCampos($externo)
    {
        $arrayCampos = $this->getAbasCampos($externo);
        $codigos = array();

        foreach($arrayCampos as $key => $value)
        {
            $temp = explode(',', $value);
            $cont = 1;
            $chave = (string) $key + 1;
            foreach($temp as $campo)
            {
                $codigos[$key][$campo] = $chave . '.' . $cont;
                $cont++;
            }
        }

        return $codigos;
    }

    public function getNomesCampos()
    {
        $classes = $this->getNomeClasses();

        return [
            $classes[0] => 'path',
            $classes[1] => 'nome_contabil,cnpj_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $classes[4] => 'segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone,telefone,opcional_celular,tipo_telefone_1,telefone_1,opcional_celular_1,pergunta',
            $classes[2] => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade,nome_mae,nome_pai,tipo_identidade,identidade,orgao_emissor,dt_expedicao',
            $classes[3] => 'razao_social,capital_social,nire,tipo_empresa,dt_inicio_atividade,inscricao_estadual,inscricao_municipal,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            $classes[5] => 'nome_rt,nome_social_rt,sexo_rt,dt_nascimento_rt,cpf_rt,tipo_identidade_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt'
        ];
    }

    public function verificacao(GerentiRepositoryInterface $gerentiRepository, $externo)
    {
        $resultadosGerenti = $gerentiRepository->gerentiBusca("", null, $externo->cpf_cnpj);
        $gerenti = null;

        // Registro não pode estar cancelado; deve estar ativo; e se for pf busca pelo codigo de pf e rt, e pj pelo codigo pj
        if(count($resultadosGerenti) > 0)
            foreach($resultadosGerenti as $resultado)
            {
                $naoCancelado = $resultado['CANCELADO'] == "F";
                $ativo = $resultado['ASS_ATIVO'] == "T";
                $pf = $externo->isPessoaFisica() && (($resultado["ASS_TP_ASSOC"] == $this->getCodigoPF()) || ($resultado["ASS_TP_ASSOC"] == $this->getCodigoRT()));
                $pj = !$externo->isPessoaFisica() && ($resultado["ASS_TP_ASSOC"] == $this->getCodigoPJ());
                if($naoCancelado && $ativo && ($pf || $pj))
                {
                    $gerenti = $resultado['ASS_REGISTRO'];
                    
                    $string = 'Usuário Externo com ';
                    $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
                    $string .= $externo->cpf_cnpj . ', não pode realizar a solicitação de registro ';
                    $string .= 'devido constar no GERENTI um registro ativo : ' . formataRegistro($resultado['ASS_REGISTRO']);
                    event(new ExternoEvent($string));
                }
            }

        return [
            'gerenti' => $gerenti,
        ];
    }

    public function getPreRegistro(MediadorServiceInterface $service, $externo)
    {
        $resultado = $externo->load('preRegistro')->preRegistro;
        if(!isset($resultado))
        {
            $resultado = $externo->preRegistro()->create();
            $externo->isPessoaFisica() ? $resultado->pessoaFisica()->create() : $resultado->pessoaJuridica()->create();

            $string = 'Usuário Externo com ';
            $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
            $string .= $externo->cpf_cnpj . ', iniciou o processo de solicitação de registro com a id: ' . $resultado->id;
            event(new ExternoEvent($string));
        }

        return [
            'resultado' => $resultado,
            'codigos' => $this->getCodigosCampos($externo),
            'regionais' => $service->getService('Regional')
                ->all()
                ->splice(0, 13)
                ->sortBy('regional'),
            'classes' => $this->getNomeClasses(),
            'totalFiles' => $this->totalFiles,
            'abas' => $this->getMenu()
        ];
    }

    public function saveSiteAjax($request, GerentiRepositoryInterface $gerentiRepository, $externo)
    {
        $preRegistro = $externo->load('preRegistro')->preRegistro;

        if(!isset($preRegistro))
            throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

        if(!$preRegistro->userPodeEditar())
            throw new \Exception('Não autorizado a editar o formulário com a solicitação em análise ou finalizada', 401);

        $resultado = null;
        $objeto = null;
        $classeCriar = array_search($request['classe'], $this->getRelacoes());

        if(($request['classe'] != PreRegistroService::RELATION_ANEXOS) && ($request['classe'] != PreRegistroService::RELATION_PRE_REGISTRO))
            $objeto = $preRegistro->has($request['classe'])->where('id', $preRegistro->id)->first();
        
        $request['campo'] = $this->limparNomeCamposAjax($request['classe'], $request['campo']);
        $gerenti = ($request['classe'] == PreRegistroService::RELATION_RT) && ($request['campo'] == 'cpf') ? 
            $this->getRTGerenti($gerentiRepository, $request['valor']) : null;

        if(($request['classe'] == PreRegistroService::RELATION_PRE_REGISTRO) || isset($objeto))
            $resultado = $preRegistro->atualizarAjax($request['classe'], $request['campo'], $request['valor'], $gerenti);
        else
            $resultado = $preRegistro->criarAjax($classeCriar, $request['classe'], $request['campo'], $request['valor'], $gerenti);

        return [
            'resultado' => $resultado,
            'dt_atualizado' => $preRegistro->fresh()->updated_at->format('d\/m\/Y, \à\s H:i:s')
        ];
    }

    public function saveSite($request, GerentiRepositoryInterface $gerentiRepository, $externo)
    {
        $preRegistro = $externo->load('preRegistro')->preRegistro;

        if(!isset($preRegistro))
            throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

        if(!$preRegistro->userPodeEditar())
            throw new \Exception('Não autorizado a editar o formulário com a solicitação em análise ou finalizada', 401);

        $camposLimpos = $this->limparNomeCampos($externo, $request);

        foreach($camposLimpos as $key => $arrayCampos)
        {
            $gerenti = null;
            if($key != PreRegistroService::RELATION_PRE_REGISTRO)
            {
                $gerenti = $key == PreRegistroService::RELATION_RT ? $this->getRTGerenti($gerentiRepository, $arrayCampos['cpf']) : null;
                $objeto = $preRegistro->has($key)->where('id', $preRegistro->id)->first();
                $resultado = isset($objeto) ? $preRegistro->salvar($key, $arrayCampos, $gerenti) : 
                    $preRegistro->salvar($key, $arrayCampos, $gerenti, array_search($key, $this->getRelacoes()));
            } else
                $resultado = $preRegistro->salvar($key, $arrayCampos, $gerenti);
            
            if(!isset($resultado))
                throw new \Exception('Não salvou os dados em ' . $key, 500);
        }

        $status = !isset($preRegistro->status) ? $preRegistro::STATUS_ANALISE_INICIAL : $preRegistro::STATUS_ANALISE_CORRECAO;
        $resultado = $preRegistro->update(['status' => $status]);

        if(!isset($resultado))
            throw new \Exception('Não atualizou o status da solicitação de registro para ' . $status, 500);
        
        Mail::to($externo->email)->queue(new PreRegistroMail($preRegistro));

        $string = 'Usuário Externo com ';
        $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
        $string .= $externo->cpf_cnpj . ', atualizou o status para ' . $status . ' da solicitação de registro com a id: ' . $preRegistro->id;
        event(new ExternoEvent($string));
        
        return [
            'message' => '<i class="icon fa fa-check"></i> Solicitação de registro enviada para análise! <strong>Status atualizado para:</strong> ' . $status,
            'class' => 'alert-success'
        ];
    }

    public function downloadAnexo($id, $externo)
    {
        if(\Route::is('preregistro.anexo.download'))
            $preRegistro = PreRegistro::findOrFail($externo);
        else
            $preRegistro = $externo->load('preRegistro')->preRegistro;

        if(!isset($preRegistro))
            throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

        $anexo = $preRegistro->anexos()->where('id', $id)->first();

        if(isset($anexo) && Storage::exists($anexo->path))
            return response()->file(Storage::path($anexo->path), ["Cache-Control" => "no-cache"]);
        
        throw new \Exception('Arquivo não existe / não pode acessar', 401);
    }

    public function excluirAnexo($id, $externo)
    {
        $preRegistro = $externo->load('preRegistro')->preRegistro;

        if(!isset($preRegistro))
            throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

        if(!$preRegistro->userPodeEditar())
            throw new \Exception('Não autorizado a excluir arquivo com status diferente de ' . PreRegistro::STATUS_CORRECAO, 401);

        $anexo = $preRegistro->anexos()->where('id', $id)->first();

        if(isset($anexo) && Storage::exists($anexo->path))
        {
            if(Storage::delete($anexo->path))
            {
                $anexo->delete();
                $preRegistro->touch();
            }
            return [
                'resultado' => $id,
                'dt_atualizado' => $preRegistro->updated_at->format('d\/m\/Y, \à\s H:i:s')
            ];
        }

        throw new \Exception('Arquivo não existe / não pode acessar', 401);
    }

    public function getTiposAnexos()
    {
        return Anexo::getAceitosPreRegistro();
    }

    public function listar()
    {
        // ordenar depois por solicitações em estagio inicial, seguido de correçoes, etc
        $resultados = PreRegistro::with(['userExterno' => function ($query) {
            $query->select('id', 'cpf_cnpj', 'nome');
        }, 'regional' => function ($query2) {
            $query2->select('idregional', 'regional');
        }, 'user' => function ($query3) {
            $query3->select('idusuario', 'nome');
        }])
        ->select('id', 'updated_at', 'status', 'user_externo_id', 'idregional', 'idusuario')
        ->whereNotNull('status')
        ->orderBy('id')
        ->get();

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id)
    {
        $variaveis = $this->variaveis;
        $variaveis['btn_lista'] = '<a href="'.route('preregistro.index').'" class="btn btn-primary mr-1">Lista dos Pré-registros</a>';
        $resultado = PreRegistro::findOrFail($id);

        // Atendente não pode acessar um pré-registro sem status, pois ainda está sendo editado pelo usuário
        if(!isset($resultado->status))
            throw new \Exception('Não autorizado a acessar pré-registro sem status', 401);

        return [
            'resultado' => $resultado, 
            'variaveis' => (object) $variaveis,
            'abas' => $this->getMenu(),
            'codigos' => $this->getCodigosCampos($resultado->userExterno),
        ];
    }

    public function saveAjaxAdmin($request, $id, $user)
    {
        $preRegistro = PreRegistro::findOrFail($id);

        // Atendente não pode editar um pré-registro com status diferente de analise inicial e analise da correção
        if(!isset($preRegistro->status) || !$preRegistro->atendentePodeEditar())
            throw new \Exception('Não autorizado a editar o pré-registro sem status ou finalizado', 401);

        $campo = $request['campo'];
        $valor = $request['valor'];

        if($request['acao'] != 'editar')
        {
            $campo = $request['acao'] == 'justificar' ? 'justificativa' : 'confere_anexos';
            $valor = $request['acao'] == 'justificar' ? ['campo' => $request['campo'], 'valor' => $request['valor']] : $request['valor'];
        }
            
        $camposCanEdit = [
            'justificativa' => 'preRegistro',
            'confere_anexos' => 'preRegistro',
            'registro_secundario' => 'preRegistro',
            'registro' => 'pessoaJuridica.responsavelTecnico',
        ];

        $preRegistro->atualizarAjax($camposCanEdit[$campo], $campo, $valor, null);
        event(new CrudEvent('pré-registro', 'fez a ação de "' . $request['acao'] . '" o campo "' . $request['campo'] . '"', $preRegistro->id));

        return [
            'user' => $user->nome,
            'atualizacao' => $preRegistro->fresh()->updated_at->format('d\/m\/Y, \à\s H:i:s')
        ];
    }

    public function updateStatus($id, $user, $situacao)
    {
        $preRegistro = PreRegistro::findOrFail($id);
        $status = [
            'aprovar' => PreRegistro::STATUS_APROVADO,
            'negar' => PreRegistro::STATUS_NEGADO,
            'corrigir' => PreRegistro::STATUS_CORRECAO,
        ];
        $texto = $status[$situacao] != PreRegistro::STATUS_APROVADO ? 'não possui' : 'possui';
        $temp = $status[$situacao] == PreRegistro::STATUS_CORRECAO ? 'enviado para correção' : strtolower($status[$situacao]);

        if($preRegistro->canUpdateStatus($status[$situacao]))
        {
            $preRegistro->update(['idusuario' => $user->idusuario, 'status' => $status[$situacao]]);
            Mail::to($preRegistro->userExterno->email)->queue(new PreRegistroMail($preRegistro));
            event(new CrudEvent('pré-registro', 'atualizou status para ' . $status[$situacao], $preRegistro->id));

            return [
                'message' => '<i class="icon fa fa-check"></i>Pré-registro com a ID: ' . $preRegistro->id . ' foi ' . $temp . ' com sucesso', 
                'class' => 'alert-success'
            ];
        }
        
        $preRegistro->update(['justificativa' => null]);
        return [
            'message' => '<i class="icon fa fa-ban"></i>Pré-registro com a ID: ' . $preRegistro->id . ' não possui o status necessário para ser ' . $temp . ' ou ' . $texto . ' justificativas ou faltou anexos', 
            'class' => 'alert-danger'
        ];
    }
}