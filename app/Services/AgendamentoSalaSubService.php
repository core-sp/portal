<?php

namespace App\Services;

use App\Contracts\AgendamentoSalaSubServiceInterface;
use App\AgendamentoSala;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\SalaReuniaoMail;
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

    private function validacaoFiltroAtivo($request, $user)
    {
        $canFiltroRegional = $user->cannot('atendenteOrGerSeccionais', $user);
        $datemin = $request->filled('datemin') ? Carbon::parse($request->datemin) : Carbon::today();
        $datemax = $request->filled('datemax') ? Carbon::parse($request->datemax) : Carbon::today();

        if($datemax->lt($datemin))
            $datemax = $datemin;

        return [
            'datemin' => $datemin->format('Y-m-d'),
            'datemax' => $datemax->format('Y-m-d'),
            'regional' => $request->filled('regional') && $canFiltroRegional ? $request->regional : $user->idregional,
            'status' => $request->filled('status') ? $request->status : 'Qualquer',
            'sala' => $request->filled('sala') ? $request->sala : 'Qualquer'
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
                ->orderBy('sala_reuniao_id')
                ->orderBy('dia','DESC')
                ->orderBy('periodo', 'ASC')
                ->paginate(25);
        }
    }

    private function filtro($request, $service, $user)
    {
        $filtro = '';
        $temFiltro = null;
        $this->variaveis['continuacao_titulo'] = 'em <strong>'.$user->regional->regional.' - '.date('d\/m\/Y').'</strong>';

        if(\Route::is('sala.reuniao.agendados.filtro'))
        {
            $temFiltro = true;
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

        foreach(AgendamentoSala::status() as $s)
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

    public function listar($user, $request = null, $service = null)
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
                'temFiltro' => $this->filtro($request, $service, $user),
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

    public function update($user, $id, $justificativa = [])
    {
        $agendado = AgendamentoSala::with(['sala', 'representante'])->findOrFail($id);

        $atendOrGere = $user->can('atendenteOrGerSeccionais', $user);
        $sameRegional = $user->can('sameRegional', $agendado);
        if($atendOrGere && !$sameRegional)
            throw new \Exception('Não autorizado', 403);

        if($agendado->podeAtualizarStatus())
        {
            $status = empty($justificativa) ? AgendamentoSala::STATUS_COMPARECEU : null;
            if(!isset($status))
                $status = isset($justificativa['justificativa_admin']) ? AgendamentoSala::STATUS_NAO_COMPARECEU : AgendamentoSala::STATUS_JUSTIFICADO;

            $agendado->update([
                'status' => $status,
                'justificativa_admin' => isset($justificativa['justificativa_admin']) ? $justificativa['justificativa_admin'] : null,
                'idusuario' => $user->idusuario
            ]);

            event(new CrudEvent('agendamento da sala de reunião', 'atualizou status para '.$status, $id));
        }
    }

    public function buscar($user, $busca)
    {
        $regional = $user->can('atendenteOrGerSeccionais', $user) ? $user->idregional : null;

        $resultados = AgendamentoSala::with(['user', 'representante', 'sala'])
            ->when($regional, function ($query) use ($regional, $busca) {
                return $query->where('sala_reuniao_id', $regional)
                    ->where(function($q) use ($busca) {
                        $q->whereHas('representante', function ($q1) use ($busca) {
                            $q1->where('cpf_cnpj', 'LIKE', '%'.apenasNumeros($busca).'%');
                        })
                        ->orWhere('protocolo', 'LIKE', '%'.$busca.'%')
                        ->orWhere('id', apenasNumeros($busca));
                });
            }, function ($query) use ($busca) {
                return $query->whereHas('representante', function ($q) use ($busca) {
                    $q->where('cpf_cnpj', 'LIKE', '%'.apenasNumeros($busca).'%');
                })
                ->orWhere('id', apenasNumeros($busca))
                ->orWhere('protocolo', 'LIKE', '%'.$busca.'%');
            })->paginate(25);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $this->variaveis
        ];
    }
}