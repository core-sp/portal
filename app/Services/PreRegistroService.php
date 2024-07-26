<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;
use App\PreRegistro;
use App\Anexo;
use Illuminate\Support\Facades\Storage;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\Traits\PreRegistroApoio;

class PreRegistroService implements PreRegistroServiceInterface {

    use PreRegistroApoio;

    private function criarPreRegistro($externo, $contabil = null, $previo = false)
    {
        if(!isset($externo))
            throw new \Exception('Deve ter um usuário externo para criar o pré-registro.', 401);

        if($externo->preRegistroAprovado())
            return [
                'message' => 'Este CPF / CNPJ já possui uma solicitação aprovada.',
                'class' => 'alert-warning'
            ];

        $resultado = $externo->load('preRegistro')->preRegistro;
        if(isset($resultado))
            return $resultado;
        
        $resultado = $externo->criarPreRegistro();

        $string = 'Usuário Externo com ';
        $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
        $string .= $externo->cpf_cnpj . ', iniciou o processo de solicitação de registro com a id: ' . $resultado->id;
        event(new ExternoEvent($string));
        Mail::to($externo->email)->queue(new PreRegistroMail($resultado));

        if(isset($contabil))
        {
            $resultado->update(['contabil_id' => $contabil->id]);

            $string = 'Contabilidade com cnpj '.$contabil->cnpj . ', criou a solicitação de registro com a id: ' . $resultado->id;
            if($previo)
            {
                $string .= ' junto com a conta do Usuário Externo com o ';
                $string .= $externo->isPessoaFisica() ? 'cpf ' : 'cnpj ';
                $string .= $externo->cpf_cnpj . ' que foi notificado pelo e-mail '.$externo->email;
            }

            event(new ExternoEvent($string));
            Mail::to($contabil->email)->queue(new PreRegistroMail($resultado));
        }

        return $resultado;
    }

    private function verificaPodeProsseguir($gerentiRepository, $externo)
    {
        $gerenti = $this->verificacao($gerentiRepository, $externo);
        if(isset($gerenti['gerenti']))
            return [
                'message' => 'Este CPF / CNPJ já possui registro ativo no Core-SP: '.formataRegistro($gerenti['gerenti']),
                'class' => 'alert-info'
            ];
    }

    public function verificacao($gerentiRepository, $externo)
    {
        if(!isset($externo))
            throw new \Exception('Somente usuário externo pode ser verificado no sistema se consta registro.', 401);

        $resultadosGerenti = $gerentiRepository->gerentiBusca("", null, $externo->cpf_cnpj);
        $gerenti = null;

        // Registro não pode estar cancelado; deve estar ativo; e se for pf busca pelo codigo de pf e rt, e pj pelo codigo pj
        foreach($resultadosGerenti as $resultado)
        {
            $possuiRegistro = $externo->possuiRegistroAtivo($resultado["ASS_TP_ASSOC"], $resultado['CANCELADO'], $resultado['ASS_ATIVO']);
            if($possuiRegistro)
            {
                $gerenti = $resultado['ASS_REGISTRO'];
                    
                $string = 'Usuário Externo com ';
                $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
                $string .= $externo->cpf_cnpj . ', não pode realizar a solicitação de registro ';
                $string .= 'devido constar no GERENTI um registro ativo : ' . formataRegistro($resultado['ASS_REGISTRO']);
                event(new ExternoEvent($string));
                break;
            }
        }

        return [
            'gerenti' => $gerenti,
        ];
    }

    public function setPreRegistro($gerentiRepository, $service, $contabil, $dados)
    {
        $resultado = PreRegistro::whereHas('userExterno', function ($query) use($dados) {
            $query->where('cpf_cnpj', $dados['cpf_cnpj'])->whereNotIn('status', [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]);
        })->count();
        if($resultado > 0)
            return [
                'message' => 'Este CPF / CNPJ já possui uma solicitação de registro em andamento. Por gentileza, peça que o representante insira no formulário o seu CNPJ.',
                'class' => 'alert-warning'
            ];

        $externo = $service->getService('UserExterno')->findByCpfCnpj('user_externo', $dados['cpf_cnpj']);
        if(isset($externo))
        {
            $msg = $this->verificaPodeProsseguir($gerentiRepository, $externo);
            if(isset($msg['message']))
                return $msg;
            return $this->criarPreRegistro($externo, $contabil);
        }          
        
        $externo = $service->getService('UserExterno')->cadastroPrevio($contabil, $dados, true);
        $msg = $this->verificaPodeProsseguir($gerentiRepository, $externo);
        if(isset($msg['message']))
            return $msg;
        
        $externo = $service->getService('UserExterno')->cadastroPrevio($contabil, $externo);
        return $this->criarPreRegistro($externo, $contabil, true);
    }

    public function getPreRegistros($contabil)
    {
        return [
            'resultados' => $contabil->preRegistros()
                ->with('userExterno')
                ->orderBy('updated_at', 'DESC')
                ->orderBy('user_externo_id')
                ->paginate(5),
        ];
    }

    public function getPreRegistro($service, $externo)
    {
        if(!isset($externo))
            throw new \Exception('Somente usuário externo ou contabilidade vinculada a um usuário externo pode solicitar registro.', 401);

        $resultado = $this->criarPreRegistro($externo);
        if(isset($resultado['message']))
            return $resultado;

        return [
            'resultado' => $resultado,
            'codigos' => $this->getCodigosCampos($externo->isPessoaFisica()),
            'regionais' => $service->getService('Regional')->getRegionais()->sortBy('regional'),
            'classes' => $this->getNomesRelacoes(),
            'totalFiles' => $externo->isPessoaFisica() ? Anexo::TOTAL_PF_PRE_REGISTRO : Anexo::TOTAL_PJ_PRE_REGISTRO,
            'abas' => $this->getMenu()
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

        $resultado = $preRegistro->salvarAjax($request, $gerentiRepository);

        if($resultado instanceof Anexo)
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

        if(!$preRegistro->confereJustificadosSubmit($request))
            return [
                'message' => '<i class="fas fa-times"></i> Formulário não foi enviado para análise da correção, pois precisa editar dados(s) conforme justificativa(s).',
                'class' => 'alert-danger'
            ];

        $status = $preRegistro->salvar(/*$request, $gerentiRepository*/);
        
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

    public function downloadAnexo($id, $idPreRegistro, $admin = false)
    {
        $preRegistro = PreRegistro::findOrFail($idPreRegistro);

        $anexo = $preRegistro->anexos->where('id', $id)->first();

        if(!isset($anexo) || !Storage::disk('local')->exists($anexo->path))
            throw new \Exception('Arquivo de anexo do pré-registro não existe / não pode acessar', 401);

        $file = Storage::disk('local')->path($anexo->path);
        if(!isset($file))
            throw new \Exception('Arquivo de anexo com ID '.$id.' do pré-registro não existe no storage', 404);

        if($anexo->anexadoPeloAtendente() && !$admin)
            event(new ExternoEvent('Foi realizado o download do boleto com ID ' . $anexo->id .' do pré-registro com ID ' . $preRegistro->id . '.'));

        return $file;
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

        $anexo = $preRegistro->anexos->where('id', $id)->first();

        if(!isset($anexo) || !Storage::disk('local')->exists($anexo->path))
            throw new \Exception('Arquivo não existe / não pode acessar', 401);

        $deleted = Storage::disk('local')->delete($anexo->path);
        if($deleted)
        {
            $anexo->delete();
            $preRegistro->touch();

            $string = isset($contabil) ? 'Contabilidade com cnpj '.$contabil->cnpj.' realizou a operação para o Usuário Externo com ' : 'Usuário Externo com ';
            $string .= $externo->isPessoaFisica() ? 'cpf: ' : 'cnpj: ';
            $string .= $externo->cpf_cnpj . ', excluiu o arquivo com a ID: '.$id.' na solicitação de registro com a id: '.$preRegistro->id;
            event(new ExternoEvent($string));
        }

        return [
            'resultado' => $deleted ? $id : null,
            'dt_atualizado' => $preRegistro->updated_at->format('d\/m\/Y, \à\s H:i:s')
        ];
    }

    public function admin()
    {
        return resolve('App\Contracts\PreRegistroAdminSubServiceInterface');
    }
}