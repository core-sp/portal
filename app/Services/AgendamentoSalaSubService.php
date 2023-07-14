<?php

namespace App\Services;

use App\Contracts\AgendamentoSalaSubServiceInterface;
use App\AgendamentoSala;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoSalaMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AgendamentoSalaSubService implements AgendamentoSalaSubServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'agendamento de sala',
            'singulariza' => 'o agendamento de sala',
            'plural' => 'agendamentos de salas',
            'pluraliza' => 'agendamentos de salas',
            'slug' => 'salas-reunioes/agendados',
            'busca' => 'salas-reunioes/agendados',
            'mostra' => 'agendamento-sala',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'Protocolo',
            'Representante Responsável',
            'Tipo de Sala / Dia e Período',
            'Regional',
            'Status',
            'Atualizado em',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $cor = $resultado->justificativaEnviada() ? 'warning' : 'primary';
            $acoes = '<a href="' .route('sala.reuniao.agendados.view', $resultado->id). '" class="btn btn-sm btn-'.$cor.'">Ver</a>&nbsp;&nbsp;&nbsp;';
            $acoes .= $resultado->getBtnStatusCompareceu();
            $recusado = isset($resultado->justificativa_admin) ? '<br><small><em><strong>Justificativa recusada</strong></em></small>' : '';
            $conteudo = [
                $resultado->id,
                $resultado->protocolo,
                $resultado->representante->cpf_cnpj,
                $resultado->getTipoSalaHTML().'<br><small><strong>Dia:</strong> '. onlyDate($resultado->dia) .' | <strong>Período:</strong> '.$resultado->getPeriodo(),
                $resultado->sala->regional->regional,
                $resultado->getStatusHTML() . $recusado,
                formataData($resultado->updated_at),
                $acoes
            ];
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $aviso = '<p class="text-primary mb-0"><i class="fas fa-info-circle"></i> O sistema irá atualizar o status para "<span class="text-danger font-weight-bold">'.AgendamentoSala::STATUS_NAO_COMPARECEU.'</span>" ';
        $aviso .= 'após 2 dias do dia do agendamento caso o representante <strong>não compareça e não envie uma justificativa</strong>.</p>';
        $aviso .= '<p class="text-primary mt-0"><i class="fas fa-info-circle"></i> O sistema irá excluir os comprovantes das justificativas recebidas após 1 mês com status ';
        $aviso .= '"<span class="text-danger font-weight-bold">'.AgendamentoSala::STATUS_NAO_COMPARECEU.'</span>" ou "<span class="text-secondary font-weight-bold">'.AgendamentoSala::STATUS_JUSTIFICADO.'</span>".</p>';

        $tabela = $aviso . montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }

    private function validacaoFiltroAtivo($request, $user)
    {
        $canFiltroRegional = $user->cannot('atendenteOrGerSeccionais', $user);
        $datemin = $request->filled('datemin') && Carbon::hasFormat($request->datemin, 'Y-m-d') ? Carbon::parse($request->datemin) : Carbon::today();
        $datemax = $request->filled('datemax') && Carbon::hasFormat($request->datemax, 'Y-m-d') ? Carbon::parse($request->datemax) : Carbon::today();

        if($datemax->lt($datemin))
            $datemax = $datemin;

        $status = 'Qualquer';
        if($request->filled('status'))
        {
            if(in_array($request->status, AgendamentoSala::status()))
                $status = $request->status;
            elseif($request->status == 'Sem status')
                $status = null;
        }

        return [
            'datemin' => $datemin->format('Y-m-d'),
            'datemax' => $datemax->format('Y-m-d'),
            'regional' => $request->filled('regional') && $canFiltroRegional ? $request->regional : $user->idregional,
            'status' => $status,
            'sala' => $request->filled('sala') && in_array($request->sala, ['reuniao', 'coworking']) ? $request->sala : 'Qualquer'
        ];
    }

    private function getResultadosFiltro($dados)
    {
        if(isset($dados))
        {
            $regional = $dados['regional'];
            $status = $dados['status'];
            $sala = $dados['sala'];

            return AgendamentoSala::with(['user', 'sala', 'representante'])
                ->whereBetween('dia', [
                    $dados['datemin'], $dados['datemax']
                ])
                ->when($regional != 'Todas', function ($query) use($regional) {
                    $query->where('sala_reuniao_id', $regional);
                })
                ->when($status != 'Qualquer', function ($query) use($status) {
                    $query->where('status', $status);
                })
                ->when($sala != 'Qualquer', function ($query) use($sala) {
                    $query->where('tipo_sala', $sala);
                })
                ->orderBy('dia','ASC')
                ->orderBy('periodo', 'ASC')
                ->orderBy('id', 'ASC')
                ->paginate(25);
        }
    }

    private function filtro($temFiltro = null, $request, $service, $user)
    {
        $filtro = '';
        $this->variaveis['continuacao_titulo'] = 'em <strong>'.$user->regional->regional.' - '.date('d\/m\/Y').'</strong>';

        if(isset($temFiltro) && $temFiltro)
        {
            $this->variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
            $this->variaveis['plural'] = 'salas-reunioes/agendados';
        }

        if($user->cannot('atendenteOrGerSeccionais', $user))
        {
            $regionais = $service->getService('Regional')->all()->sortBy('regional');
            $options = !isset($request->regional) ? 
            getFiltroOptions('Todas', 'Todas', true) : getFiltroOptions('Todas', 'Todas');

            foreach($regionais as $regional)
                $options .= isset($request->regional) && ($request->regional == $regional->idregional) ? 
                getFiltroOptions($regional->idregional, $regional->regional, true) : 
                getFiltroOptions($regional->idregional, $regional->regional);

            $filtro .= getFiltroCamposSelect('Seccional', 'regional', $options);
        }

        $options = isset($request->status) && ($request->status == 'Qualquer') ? 
        getFiltroOptions('Qualquer', 'Qualquer', true) : getFiltroOptions('Qualquer', 'Qualquer');

        $allStatus = array_merge(AgendamentoSala::status(), ['Sem status']);
        foreach($allStatus as $s)
            $options .= isset($request->status) && ($request->status == $s) ? 
            getFiltroOptions($s, $s, true) : getFiltroOptions($s, $s);

        $filtro .= getFiltroCamposSelect('Status', 'status', $options);
    
        $options = isset($request->sala) && ($request->sala == 'Qualquer') ? 
        getFiltroOptions('Qualquer', 'Qualquer', true) : getFiltroOptions('Qualquer', 'Qualquer');

        foreach(['reuniao' => 'Reunião', 'coworking' => 'Coworking'] as $valor => $sala)
            $options .= isset($request->sala) && ($request->sala == $valor) ? 
            getFiltroOptions($valor, $sala, true) : getFiltroOptions($valor, $sala);

        $filtro .= getFiltroCamposSelect('Sala', 'sala', $options);
        $filtro .= getFiltroCamposDate($request->datemin, $request->datemax);
        $filtro = getFiltro(route('sala.reuniao.agendados.filtro'), $filtro);

        $this->variaveis['filtro'] = $filtro;

        return $temFiltro;
    }

    public function listar($user, $temFiltro = null, $request = null, $service = null)
    {
        session(['url' => url()->full()]);

        if(isset($request) && isset($service))
        {
            $dados = $this->validacaoFiltroAtivo($request, $user);
            $resultados = $this->getResultadosFiltro($dados);
            $this->variaveis['mostraFiltros'] = true;
    
            return [
                'resultados' => $resultados, 
                'tabela' => $this->tabelaCompleta($resultados, $user), 
                'temFiltro' => $this->filtro($temFiltro, $request, $service, $user),
                'variaveis' => (object) $this->variaveis,
            ];
        }
    }

    public function view($user, $id, $anexo = null)
    {
        $agendado = AgendamentoSala::with(['sala', 'representante'])->findOrFail($id);

        $atendOrGere = $user->can('atendenteOrGerSeccionais', $user);
        $sameRegional = $user->can('sameRegional', $agendado);
        if($atendOrGere && !$sameRegional)
            throw new \Exception('Não autorizado', 403);

        if(isset($anexo))
        {
            if(($agendado->anexo == $anexo) && Storage::disk('local')->exists("representantes/agendamento_sala/" . $anexo))
                return Storage::disk('local')->path("representantes/agendamento_sala/" . $anexo);
            throw new \Exception('Arquivo anexo do agendamento da sala com ID '.$id.' não encontrado!', 404);
        }
        
        return [
            'resultado' => $agendado,
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function update($user, $id, $acao, $justificativa = ['justificativa_admin' => null])
    {
        $agendado = AgendamentoSala::with(['sala', 'representante'])->findOrFail($id);

        $atendOrGere = $user->can('atendenteOrGerSeccionais', $user);
        $sameRegional = $user->can('sameRegional', $agendado);
        if($atendOrGere && !$sameRegional)
            throw new \Exception('Não autorizado', 403);

        if($agendado->podeAtualizarStatus())
        {
            if(!isset($agendado->status) && ($acao == 'confirma'))
                $status = AgendamentoSala::STATUS_COMPARECEU;
            elseif($agendado->justificativaEnviada() && isset($justificativa['justificativa_admin']) && ($acao == 'recusa'))
                $status = AgendamentoSala::STATUS_NAO_COMPARECEU;
            elseif($agendado->justificativaEnviada() && !isset($justificativa['justificativa_admin']) && ($acao == 'aceito'))
                $status = AgendamentoSala::STATUS_JUSTIFICADO;
            else
                return [
                    'message' => '<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$id.' devido ao status.',
                    'class' => 'alert-danger'
                ];

            $agendado->update([
                'status' => $status,
                'justificativa_admin' => isset($justificativa['justificativa_admin']) ? $justificativa['justificativa_admin'] : null,
                'idusuario' => $user->idusuario
            ]);

            event(new CrudEvent('agendamento da sala de reunião', 'atualizou status para '.$status, $id));

            if(in_array($status, [AgendamentoSala::STATUS_NAO_COMPARECEU, AgendamentoSala::STATUS_JUSTIFICADO]))
                Mail::to($agendado->representante->email)->queue(new AgendamentoSalaMail($agendado->fresh(), $status != AgendamentoSala::STATUS_NAO_COMPARECEU ? 'aceito' : 'recusa'));
            
            if($status == AgendamentoSala::STATUS_NAO_COMPARECEU)
            {
                $texto = $agendado->updateRotina($user);
                \Log::channel('interno')->info('[IP: ' . request()->ip() . '] - ' . $texto);
            }

            return null;
        }

        return [
            'message' => '<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$id.' devido ao status ou dia.',
            'class' => 'alert-danger'
        ];
    }

    public function buscar($user, $busca)
    {
        $regional = $user->can('atendenteOrGerSeccionais', $user) ? $user->idregional : null;
        $possuiNumeros = strlen(apenasLetras($busca)) == 0;

        $resultados = AgendamentoSala::with(['user', 'representante', 'sala'])
            ->when($regional, function ($query) use ($regional, $busca, $possuiNumeros) {
                return $query->where('sala_reuniao_id', $regional)
                ->where(function($q) use ($busca, $possuiNumeros) {
                    $q->when($possuiNumeros, function ($q1) use ($busca) {
                        $q1->whereHas('representante', function ($q2) use ($busca){
                            $q2->where('cpf_cnpj', 'LIKE', '%'.apenasNumeros($busca).'%');
                        })
                        ->orWhere('id', apenasNumeros($busca));
                    }, function ($q1) use($busca) {
                        $q1->where('protocolo', 'LIKE', 'RC-AGE-'. str_replace('RC-AGE-', '', $busca).'%');
                    });
                });
            }, function ($query) use ($busca, $possuiNumeros) {
                return $query->when($possuiNumeros, function ($q1) use ($busca) {
                    $q1->whereHas('representante', function ($q2) use ($busca){
                        $q2->where('cpf_cnpj', 'LIKE', '%'.apenasNumeros($busca).'%');
                    })
                    ->orWhere('id', apenasNumeros($busca));
                }, function ($q1) use($busca) {
                    $q1->where('protocolo', 'LIKE', 'RC-AGE-'. str_replace('RC-AGE-', '', $busca).'%');
                });
            })
            ->paginate(25);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function executarRotina($removerAnexos = false)
    {
        if(!$removerAnexos)
        {
            $agendados = AgendamentoSala::whereNull('status')
            ->whereDate('dia', '<=', now()->subDays(3)->toDateString())
            ->get();
    
            foreach($agendados as $agendado)
            {
                $texto = $agendado->updateRotina();
                \Log::channel('interno')->info($texto);
            }
        }else{
            $agendados = AgendamentoSala::whereIn('status', [AgendamentoSala::STATUS_NAO_COMPARECEU, AgendamentoSala::STATUS_JUSTIFICADO])
            ->whereNotNull('anexo')
            ->whereDate('updated_at', '<=', now()->subMonth()->toDateString())
            ->get();
    
            foreach($agendados as $agendado)
            {
                $removeu = Storage::disk('local')->delete('representantes/agendamento_sala/'.$agendado->anexo);
                if($removeu)
                {
                    \Log::channel('interno')->info('[Rotina Portal - Sala de Reunião] - Removido anexo do agendamento de sala com ID ' . $agendado->id.'.');
                    $agendado->update(['anexo' => $agendado->anexo . ' - [removido]']);
                }else 
                    \Log::channel('interno')->info('[Rotina Portal - Sala de Reunião] - Não foi removido anexo do agendamento de sala com ID ' . $agendado->id.'.');
            }
        }
    }
}