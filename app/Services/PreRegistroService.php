<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Anexo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\Traits\Gerenti;

class PreRegistroService implements PreRegistroServiceInterface {

    use Gerenti;

    const RELATION_ANEXOS = "anexos";
    const RELATION_CONTABIL = "contabil";
    const RELATION_PF = "pessoaFisica";
    const RELATION_PJ = "pessoaJuridica";
    const RELATION_PRE_REGISTRO = "preRegistro";
    const RELATION_RT = "pessoaJuridica.responsavelTecnico";

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
        unset($request['pergunta']);
        
        return $request;
    }

    private function limparNomeCampos($preRegistro, $request)
    {
        $request = $this->formatCampos($request);
        $preRegistro->setCamposEspelho($request);
        if(!$preRegistro->confereJustificadosSubmit())
            return [
                'message' => '<i class="fas fa-times"></i> Formulário não foi enviado para análise da correção, pois precisa editar dados(s) conforme justificativa(s).',
                'class' => 'alert-danger'
            ];
        $camposLimpos = array();
        $classe = null;
        $camposView = $this->getNomesCampos();
        $array = $preRegistro->userExterno->isPessoaFisica() ? [
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
        $array = array();
        foreach($this->getRelacoes() as $value)
            array_push($array, $value);

        return $array;
    }

    private function getRTGerenti($gerentiRepository, $cpf)
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
                    $gerenti['registro'] = apenasNumeros($resultado["ASS_REGISTRO"]);
                }
            }
        
        if(isset($ass_id))
        {
            $resultadosGerenti = utf8_converter($gerentiRepository->gerentiDadosGeraisPF($ass_id));

            $gerenti['nome_mae'] = isset($resultadosGerenti['Nome da mãe']) ? $resultadosGerenti['Nome da mãe'] : null;
            $gerenti['nome_pai'] = isset($resultadosGerenti['Nome do pai']) ? $resultadosGerenti['Nome do pai'] : null;
            $gerenti['identidade'] = isset($resultadosGerenti['identidade']) ? $resultadosGerenti['identidade'] : null;
            $gerenti['orgao_emissor'] = isset($resultadosGerenti['emissor']) ? $resultadosGerenti['emissor'] : null;
            $gerenti['dt_expedicao'] = isset($resultadosGerenti['expedicao']) && Carbon::hasFormat($resultadosGerenti['expedicao'], 'd/m/Y') ? 
                Carbon::createFromFormat('d/m/Y', $resultadosGerenti['expedicao'])->format('Y-m-d') : null;
            $gerenti['dt_nascimento'] = isset($resultadosGerenti['Data de nascimento']) && Carbon::hasFormat($resultadosGerenti['Data de nascimento'], 'd/m/Y') ? 
                Carbon::createFromFormat('d/m/Y', $resultadosGerenti['Data de nascimento'])->format('Y-m-d') : null;
            $gerenti['sexo'] = null;
            if(isset($resultadosGerenti['Sexo']))
                $gerenti['sexo'] = $resultadosGerenti['Sexo'] == "MASCULINO" ? "M" : "F";
            $gerenti['cpf'] = $cpf;
        }

        return $gerenti;
    }

    public function getNomesCampos()
    {
        $classes = $this->getNomeClasses();

        return [
            $classes[0] => 'path',
            $classes[1] => 'nome_contabil,cnpj_contabil,email_contabil,nome_contato_contabil,telefone_contabil',
            $classes[4] => 'segmento,idregional,cep,bairro,logradouro,numero,complemento,cidade,uf,tipo_telefone,telefone,opcional_celular,tipo_telefone_1,telefone_1,opcional_celular_1,pergunta',
            $classes[2] => 'nome_social,sexo,dt_nascimento,estado_civil,nacionalidade,naturalidade_cidade,naturalidade_estado,nome_mae,nome_pai,tipo_identidade,identidade,orgao_emissor,dt_expedicao,titulo_eleitor,zona,secao,ra_reservista',
            $classes[3] => 'razao_social,nome_fantasia,capital_social,nire,tipo_empresa,dt_inicio_atividade,checkEndEmpresa,cep_empresa,bairro_empresa,logradouro_empresa,numero_empresa,complemento_empresa,cidade_empresa,uf_empresa',
            $classes[5] => 'nome_rt,nome_social_rt,sexo_rt,dt_nascimento_rt,cpf_rt,tipo_identidade_rt,identidade_rt,orgao_emissor_rt,dt_expedicao_rt,titulo_eleitor_rt,zona_rt,secao_rt,ra_reservista_rt,cep_rt,bairro_rt,logradouro_rt,numero_rt,complemento_rt,cidade_rt,uf_rt,nome_mae_rt,nome_pai_rt'
        ];
    }

    public function verificacao($gerentiRepository, $externo)
    {
        if(!isset($externo))
            throw new \Exception('Somente usuário externo pode ser verificado no sistema se consta registro.', 401);

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

    public function setPreRegistro($gerentiRepository, $service, $externo, $dados)
    {
        $resultado = PreRegistro::whereHas('userExterno', function ($query) use($dados) {
            $query->where('cpf_cnpj', $dados['cpf_cnpj'])->whereNotIn('status', ['Aprovado', 'Negado']);
        })->get();
        if($resultado->count() > 0)
            return [
                'message' => 'Este CPF / CNPJ já possui uma solicitação de registro em andamento. Por gentileza, peça que o representante insira no formulário o seu CNPJ.',
                'class' => 'alert-warning'
            ];

        $usuario = $service->getService('UserExterno')->findByCpfCnpj('user_externo', $dados['cpf_cnpj']);
        if(isset($usuario))
        {
            $gerenti = $this->verificacao($gerentiRepository, $usuario);
            if(isset($gerenti['gerenti']))
                return [
                    'message' => 'Este CPF / CNPJ já possui registro ativo no Core-SP: '.formataRegistro($gerenti['gerenti']),
                    'class' => 'alert-info'
                ];
            $criado = $this->getPreRegistro($service, $usuario);

            if(isset($criado['message']))
                return $criado;

            $criado['resultado']->update(['contabil_id' => $externo->id]);
            $string = 'Contabilidade com cnpj '.$externo->cnpj;
            $string .= ', criou a solicitação de registro com a id: ' . $criado['resultado']->id;
            event(new ExternoEvent($string));
            Mail::to($externo->email)->queue(new PreRegistroMail($criado['resultado']->fresh()));

            return $criado['resultado'];
        }          
        
        $classe = $service->getService('UserExterno')->getDefinicoes('user_externo')['classe'];
        $user_externo = new $classe;
        $user_externo->cpf_cnpj = $dados['cpf_cnpj'];
        $user_externo->nome = $dados['nome'];
        $user_externo->email = $dados['email'];
        $gerenti = $this->verificacao($gerentiRepository, $user_externo);
        if(isset($gerenti['gerenti']))
            return [
                'message' => 'Este CPF / CNPJ já possui registro ativo no Core-SP: '.formataRegistro($gerenti['gerenti']),
                'class' => 'alert-info'
            ];
        
        $user_externo->save();
        $criado = $this->getPreRegistro($service, $user_externo);

        if(isset($criado['message']))
            return $criado;

        $criado['resultado']->update(['contabil_id' => $externo->id]);
        $string = 'Contabilidade com cnpj '.$externo->cnpj;
        $string .= ', criou a solicitação de registro com a id: ' . $criado['resultado']->id . ' junto com a conta do Usuário Externo com o ';
        $string .= $user_externo->isPessoaFisica() ? 'cpf ' : 'cnpj ';
        $string .= $user_externo->cpf_cnpj . ' que foi notificado pelo e-mail '.$user_externo->email;
        event(new ExternoEvent($string));
        $service->getService('UserExterno')->sendEmailCadastroPrevio($externo, $user_externo);
        Mail::to($externo->email)->queue(new PreRegistroMail($criado['resultado']->fresh()));

        return $criado['resultado'];
    }

    public function getPreRegistros($externo)
    {
        $resultados = $externo->load('preRegistros')
        ->preRegistros()
        ->orderBy('updated_at', 'DESC')
        ->orderBy('user_externo_id')
        ->paginate(5);
        
        return [
            'resultados' => $resultados
        ];
    }

    public function getPreRegistro($service, $externo)
    {
        if(!isset($externo))
            throw new \Exception('Somente usuário externo ou contabilidade vinculada a um usuário externo pode solicitar registro.', 401);

        $resultado = $externo->load('preRegistro')->preRegistro;
        if($externo->preRegistroAprovado())
            return [
                'message' => 'Este CPF / CNPJ já possui uma solicitação aprovada.',
                'class' => 'alert-warning'
            ];
        if(!isset($resultado))
        {
            $resultado = $externo->preRegistros()->create();
            if(!$externo->isPessoaFisica())
            {
                $pj = $resultado->pessoaJuridica()->create();
                $pj->update(['historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT)]);
            }else
                $resultado->pessoaFisica()->create();
            $resultado->update([
                'historico_contabil' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT),
                'historico_status' => json_encode([PreRegistro::STATUS_CRIADO . ';' . now()->format('Y-m-d H:i:s')], JSON_FORCE_OBJECT)
            ]);

            $string = 'Usuário Externo com ';
            $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
            $string .= $externo->cpf_cnpj . ', iniciou o processo de solicitação de registro com a id: ' . $resultado->id;
            event(new ExternoEvent($string));
            Mail::to($externo->email)->queue(new PreRegistroMail($resultado->fresh()));
        }

        return [
            'resultado' => isset($resultado->status) ? $resultado : $resultado->fresh(),
            'codigos' => PreRegistro::getCodigosCampos($resultado->getAbasCampos()),
            'regionais' => $service->getService('Regional')
                ->all()
                ->whereNotIn('idregional', [14])
                ->sortBy('regional'),
            'classes' => $this->getNomeClasses(),
            'totalFiles' => $externo->isPessoaFisica() ? Anexo::TOTAL_PF_PRE_REGISTRO : Anexo::TOTAL_PJ_PRE_REGISTRO,
            'abas' => PreRegistro::getMenu()
        ];
    }

    public function saveSiteAjax($request, $gerentiRepository, $externo, $contabil = null)
    {
        if(!isset($externo))
            throw new \Exception('Não autorizado a acessar a solicitação de registro por falta relacionamento com usuário externo', 401);

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

        if(($request['classe'] == 'anexos') && isset($resultado->nome_original))
        {
            $string = isset($contabil) ? 'Contabilidade com cnpj '.$contabil->cnpj.' realizou a operação para o Usuário Externo com ' : 'Usuário Externo com ';
            $string .= $externo->isPessoaFisica() ? 'cpf ' : 'cnpj ';
            $string .= $externo->cpf_cnpj . ', anexou o arquivo "'.$resultado->nome_original.'", que possui a ID: '.$resultado->id;
            $string .= ' na solicitação de registro com a id: '.$preRegistro->id;
            event(new ExternoEvent($string));
        }
        
        return [
            'resultado' => $resultado,
            'dt_atualizado' => $preRegistro->fresh()->updated_at->format('d\/m\/Y, \à\s H:i:s')
        ];
    }

    public function saveSite($request, $gerentiRepository, $externo, $contabil = null)
    {
        if(!isset($externo))
            throw new \Exception('Não autorizado a acessar a solicitação de registro por falta relacionamento com usuário externo', 401);
            
        $preRegistro = $externo->load('preRegistro')->preRegistro;

        if(!isset($preRegistro))
            throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

        if(!$preRegistro->userPodeEditar())
            throw new \Exception('Não autorizado a editar o formulário com a solicitação em análise ou finalizada', 401);

        $camposLimpos = $this->limparNomeCampos($preRegistro, $request);
        if(isset($camposLimpos['message']))
            return $camposLimpos;

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
                throw new \Exception('Não salvou os dados do pré-registro com id ' .$preRegistro->id. ' em ' . $key, 500);
        }

        $status = $preRegistro->status == PreRegistro::STATUS_CRIADO ? $preRegistro::STATUS_ANALISE_INICIAL : $preRegistro::STATUS_ANALISE_CORRECAO;
        $resultado = $preRegistro->update(['status' => $status]);
        
        if(!$resultado)
            throw new \Exception('Não atualizou o status da solicitação de registro com id '.$preRegistro->id.' para ' . $status, 500);
        
        $preRegistro->setHistoricoStatus();
        Mail::to($externo->email)->queue(new PreRegistroMail($preRegistro->fresh()));
        if(isset($preRegistro->contabil) && $preRegistro->contabil->possuiLogin())
            Mail::to($preRegistro->contabil->email)->queue(new PreRegistroMail($preRegistro->fresh()));

        $string = isset($contabil) ? 'Contabilidade com cnpj '.$contabil->cnpj.' realizou a operação para o Usuário Externo com ' : 'Usuário Externo com ';
        $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
        $string .= $externo->cpf_cnpj . ', atualizou o status para ' . $status . ' da solicitação de registro com a id: ' . $preRegistro->id;
        event(new ExternoEvent($string));
        
        return [
            'message' => '<i class="icon fa fa-check"></i> Solicitação de registro enviada para análise! <strong>Status atualizado para:</strong> ' . $status,
            'class' => 'alert-success'
        ];
    }

    public function downloadAnexo($id, $idPreRegistro, $admin = false, $doc = null)
    {
        $preRegistro = PreRegistro::findOrFail($idPreRegistro);

        if(!isset($preRegistro))
            throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

        $anexo = $preRegistro->anexos()->where('id', $id)->first();
        $file = null;

        if(isset($anexo) && Storage::disk('local')->exists($anexo->path))
        {
            $file = Storage::disk('local')->path($anexo->path);
            if(isset($doc))
                $doc = isset($file) && ($anexo->nome_original == ('boleto_aprovado_' . $preRegistro->id));
        }

        if(isset($anexo) && isset($doc) && $doc && !$admin)
            event(new ExternoEvent('Foi realizado o download do boleto com ID ' . $anexo->id .' do pré-registro com ID ' . $preRegistro->id . '.'));

        if(isset($anexo) && isset($doc) && !$doc && !$admin)
            throw new \Exception('Somente os documentos anexados pelo atendente podem ser acessados após aprovação', 401);

        if(isset($file))
            return $file;

        throw new \Exception('Arquivo não existe / não pode acessar', 401);
    }

    public function excluirAnexo($id, $externo, $contabil = null)
    {
        if(!isset($externo))
            throw new \Exception('Não autorizado a acessar a solicitação de registro por falta relacionamento com usuário externo', 401);

        $preRegistro = $externo->load('preRegistro')->preRegistro;

        if(!isset($preRegistro))
            throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

        if(!$preRegistro->userPodeEditar())
            throw new \Exception('Não autorizado a excluir arquivo com status diferente de ' . PreRegistro::STATUS_CORRECAO, 401);

        $anexo = $preRegistro->anexos()->where('id', $id)->first();

        if(isset($anexo) && Storage::disk('local')->exists($anexo->path))
        {
            $deleted = Storage::disk('local')->delete($anexo->path);
            if($deleted)
            {
                $anexo->delete();
                $preRegistro->touch();

                $string = isset($contabil) ? 'Contabilidade com cnpj '.$contabil->cnpj.' realizou a operação para o Usuário Externo com ' : 'Usuário Externo com ';
                $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
                $string .= $externo->cpf_cnpj . ', excluiu o arquivo com a ID: '.$id.' na solicitação de registro com a id: '.$preRegistro->id;
                event(new ExternoEvent($string));

                return [
                    'resultado' => $id,
                    'dt_atualizado' => $preRegistro->updated_at->format('d\/m\/Y, \à\s H:i:s')
                ];
            }
            return [
                'resultado' => null,
                'dt_atualizado' => $preRegistro->updated_at->format('d\/m\/Y, \à\s H:i:s')
            ];
        }

        throw new \Exception('Arquivo não existe / não pode acessar', 401);
    }

    public function admin()
    {
        return resolve('App\Contracts\PreRegistroAdminSubServiceInterface');
    }
}