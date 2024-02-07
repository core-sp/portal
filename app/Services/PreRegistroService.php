<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Anexo;
use Illuminate\Support\Facades\Storage;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\Traits\Gerenti;
use App\Traits\PreRegistroApoio;

class PreRegistroService implements PreRegistroServiceInterface {

    use Gerenti, PreRegistroApoio;

    const RELATION_ANEXOS = "anexos";
    const RELATION_PRE_REGISTRO = "preRegistro";

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
        $gerenti = $request['campo'] == 'cpf' ? 
            $this->getRTGerenti($request['classe'], $gerentiRepository, $request['valor']) : null;

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

        $preRegistro->setCamposEspelho($request);
        if(!$preRegistro->confereJustificadosSubmit())
            return [
                'message' => '<i class="fas fa-times"></i> Formulário não foi enviado para análise da correção, pois precisa editar dados(s) conforme justificativa(s).',
                'class' => 'alert-danger'
            ];

        $camposLimpos = $this->getCamposLimpos($request, $preRegistro->userExterno->getCamposPreRegistro());

        foreach($camposLimpos as $key => $arrayCampos)
            $resultado = $preRegistro->salvar($key, $arrayCampos, $this->getRTGerenti($key, $gerentiRepository, isset($arrayCampos['cpf']) ? $arrayCampos['cpf'] : ''));

        unset($camposLimpos);
        
        $status = $preRegistro->criado() ? $preRegistro::STATUS_ANALISE_INICIAL : $preRegistro::STATUS_ANALISE_CORRECAO;
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