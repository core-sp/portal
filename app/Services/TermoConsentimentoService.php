<?php

namespace App\Services;

use App\TermoConsentimento;
use App\Contracts\TermoConsentimentoServiceInterface;
use App\Events\ExternoEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Events\CrudEvent;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use App\Mail\BeneficiosMail;

class TermoConsentimentoService implements TermoConsentimentoServiceInterface {

    private $path_termos_servicos;

    public function __construct()
    {
        $this->path_termos_servicos = 'termos';
    }

    public function save($ip, $email)
    {
        if(isset($email))
        {
            $existe = TermoConsentimento::where('email', $email)->first();

            if(isset($existe))
                return [
                    'message' => 'E-mail já cadastrado para continuar recebendo nossos informativos.', 
                    'class' => 'alert-warning'
                ]; 

            $termo = TermoConsentimento::create([
                'ip' => $ip, 
                'email' => $email
            ]);
            event(new ExternoEvent($termo->message()));

            return null;
        }
    }

    public function uploadFile($dados, $tipo_servico)
    {
        $tipo_servico = isset($tipo_servico) ? Str::studly(str_replace('-', '_', $tipo_servico)) : $tipo_servico;

        if(!perfisPermitidos($tipo_servico . 'Controller', 'edit'))
            throw new \Exception('Não autorizado a realizar upload do termo no serviço ' . $tipo_servico, 403);

        $nome = Str::snake($tipo_servico) . '_condicoes.pdf';

        event(new CrudEvent('arquivo de termo de consentimento com upload do file: ' . $dados['file']->getClientOriginalName(), 'está atualizando', '---'));

        $path = $dados['file']->storeAs($this->path_termos_servicos.'/', $nome, 'public');
        if(!Storage::disk('public')->exists($path))
            return [
                'message' => '<i class="fas fa-times"></i> Arquivo do termo não encontrado',
                'class' => 'alert-danger'
            ];

        event(new CrudEvent('arquivo de termo de consentimento ' . $nome, 'atualizou', '---'));
        return [];
    }

    public function caminhoFile($tipo_servico = null)
    {
        $tipo_servico = isset($tipo_servico) ? Str::studly(str_replace('-', '_', $tipo_servico)) : $tipo_servico;

        if(isset($tipo_servico) && ($tipo_servico == 'SalaReuniao'))
            return Storage::disk('public')->exists($this->path_termos_servicos.'/'.Str::snake($tipo_servico) . '_condicoes.pdf') ? 
            Storage::disk('public')->path($this->path_termos_servicos.'/'.Str::snake($tipo_servico) . '_condicoes.pdf') : null;

        if(Storage::disk('public')->exists($this->path_termos_servicos.'/CORE-SP_Termo_de_consentimento.pdf'))
            return Storage::disk('public')->path($this->path_termos_servicos.'/CORE-SP_Termo_de_consentimento.pdf');
        return null;
    }

    public function dataAtualizacaoTermoStorage($tipo_servico = null)
    {
        $tipo_servico = isset($tipo_servico) ? Str::studly(str_replace('-', '_', $tipo_servico)) : $tipo_servico;
        $data = null;

        if(isset($tipo_servico) && ($tipo_servico == 'SalaReuniao'))
            $data = Storage::disk('public')->exists($this->path_termos_servicos.'/'.Str::snake($tipo_servico) . '_condicoes.pdf') ? 
            Storage::disk('public')->lastModified($this->path_termos_servicos.'/'.Str::snake($tipo_servico) . '_condicoes.pdf') : null;

        elseif(!isset($tipo_servico) && Storage::disk('public')->exists($this->path_termos_servicos.'/CORE-SP_Termo_de_consentimento.pdf'))
            $data = Storage::disk('public')->lastModified($this->path_termos_servicos.'/CORE-SP_Termo_de_consentimento.pdf');

        if(isset($data))
            return Carbon::parse($data)->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i');
        return null;
    }

    public function download()
    {
        $lista1 = TermoConsentimento::select('email', 'created_at')->whereNotNull('email')->get();
    
        if($lista1->isNotEmpty())
        {
            $headers = [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=emails-termo_consentimento-'.date('Ymd').'.csv',
                'Expires' => '0',
                'Pragma' => 'public',
            ];
            $array = array();
            
            foreach($lista1 as $temp) 
                $array[] = $temp->attributesToArray();
        
            array_unshift($array, array_keys($array[0]));

            $callback = function() use($array) {
                $fh = fopen('php://output','w');
                fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));

                foreach($array as $linha) 
                    fputcsv($fh,$linha,';');

                fclose($fh);
            };
    
            return response()->stream($callback, 200, $headers);
        }

        return null;
    }

    public function beneficios($rep = null)
    {
        $beneficios = TermoConsentimento::beneficios();
        array_unshift($beneficios, 'Todos');

        if(is_null($rep))
            return $beneficios;
        
        $inscricoes = $rep->termos()
        ->select('beneficio')
        ->whereNotNull('beneficio')
        ->get()
        ->pluck('beneficio');

        return [
            'beneficios' => $beneficios,
            'inscricoes' => collect(Arr::except($beneficios, 0))->diff($inscricoes)->isEmpty() ? 
                $inscricoes->push($beneficios[0]) : 
                $inscricoes,
        ];
    }

    private function registrarAcoesBeneficio($msg, $inscricao)
    {
        if(gettype($msg) != "string")
            return false;

        event(new ExternoEvent($inscricao->representante->nome . ' (CPF / CNPJ: ' . $inscricao->representante->cpf_cnpj . ') solicitou ' . $msg . ' no Programa de Benefícios para o benefício '. $inscricao->beneficio . ' e está registrado com a ID ' . $inscricao->id . ' o termo de consentimento.'));

        Mail::to(['comunicacao@core-sp.org.br'])->queue(new BeneficiosMail($inscricao));
    }

    public function beneficiosInscricoes($rep, $ip, $inscricoes)
    {
        $resposta = 'Ação realizada com sucesso e encaminhada à Comunicação!';
        $sem_resposta = 'Não há ação a ser realizada!';

        $inscricoes = isset($inscricoes['inscricoes']) ? collect($inscricoes['inscricoes']) : collect($inscricoes);
        $inscrito = $rep->termos()->whereNotNull('beneficio');
        $total = $inscrito->count();

        // Sem inscrições
        if($inscricoes->isEmpty() && ($total == 0))
            return ['message' => $sem_resposta, 'class' => 'alert-warning'];

        if($inscricoes->isEmpty())
        {
            $inscrito->get()->each(function ($item, $key) {
                $msg = $item->excluirBeneficio();
                $this->registrarAcoesBeneficio($msg, $item);
            });

            return ['message' => $resposta, 'class' => 'alert-success'];
        }

        $todos = $inscricoes->contains($this->beneficios()[0]);
        $final_colecao = $todos ? collect(Arr::except($this->beneficios(), 0)) : $inscricoes;

        $final_colecao->diff($inscrito->withTrashed()
            ->get()
            ->each(function ($item, $key) use($todos, $inscricoes) {
                $msg = $inscricoes->contains($item->beneficio) || $todos ? 
                    $item->restaurarBeneficio() : 
                    $item->excluirBeneficio();
                $this->registrarAcoesBeneficio($msg, $item);
            })
            ->pluck('beneficio'))
            ->each(function ($item, $key) use($rep, $ip) {
                $dados = $rep->inscreverBeneficio($ip, $item);
                $this->registrarAcoesBeneficio($dados['msg'], $dados['inscricao']);
            });

        return $total != $inscrito->count() ? 
            ['message' => $resposta, 'class' => 'alert-success'] : 
            ['message' => $sem_resposta, 'class' => 'alert-warning'];
    }
}