<?php

namespace App\Services;

use App\Contracts\AgendamentoServiceInterface;
use App\Contracts\AgendamentoSiteSubServiceInterface;
use App\Contracts\AgendamentoBloqueioSubServiceInterface;
use App\Contracts\MediadorServiceInterface;
use App\Agendamento;
use App\Events\CrudEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoMailGuest;
use Exception;

class AgendamentoService implements AgendamentoServiceInterface {

    private $variaveis;
    private $site;
    private $bloqueio;

    public function __construct(AgendamentoSiteSubServiceInterface $site, AgendamentoBloqueioSubServiceInterface $bloqueio)
    {
        $this->variaveis = [
            'singular' => 'agendamento',
            'singulariza' => 'o agendamento',
            'plural' => 'agendamentos',
            'pluraliza' => 'agendamentos'
        ];

        $this->site = $site;
        $this->bloqueio = $bloqueio;
    }

    private function validacaoFiltroAtivo($user, $request)
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
            'servico' => $request->filled('servico') ? $request->servico : 'Qualquer'
        ];
    }

    private function filtro($user, $request, MediadorServiceInterface $service)
    {
        $filtro = '';
        $temFiltro = null;
        $this->variaveis['continuacao_titulo'] = 'em <strong>'.$user->regional->regional.' - '.date('d\/m\/Y').'</strong>';

        if(\Route::is('agendamentos.filtro'))
        {
            $temFiltro = true;
            $this->variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
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

        foreach(Agendamento::status() as $s)
            $options .= isset($request->status) && ($request->status == $s) ? 
            getFiltroOptions($s, $s, true) : getFiltroOptions($s, $s);

        $filtro .= getFiltroCamposSelect('Status', 'status', $options);
    
        $options = isset($request->servico) && ($request->servico == 'Qualquer') ? 
        getFiltroOptions('Qualquer', 'Qualquer', true) : getFiltroOptions('Qualquer', 'Qualquer');

        foreach(Agendamento::servicosCompletos() as $servico)
            $options .= isset($request->servico) && ($request->servico == $servico) ? 
            getFiltroOptions($servico, $servico, true) : getFiltroOptions($servico, $servico);

        $filtro .= getFiltroCamposSelect('Serviço', 'servico', $options);
        $filtro .= getFiltroCamposDate($request->datemin, $request->datemax);
        $filtro = getFiltro(route('agendamentos.filtro'), $filtro);

        $this->variaveis['filtro'] = $filtro;

        return $temFiltro;
    }

    private function getResultadosFiltro($dados)
    {
        if(isset($dados))
        {
            $regional = $dados['regional'];
            $status = $dados['status'];
            $servico = $dados['servico'];

            return Agendamento::with(['user', 'regional'])
                ->whereBetween('dia', [
                    $dados['datemin'], $dados['datemax']
                ])->when($regional != 'Todas', function ($query) use($regional) {
                    $query->where('idregional', $regional);
                })->when($status != 'Qualquer', function ($query) use($status) {
                    $query->where('status', $status);
                })->when($servico != 'Qualquer', function ($query) use($servico) {
                    $query->where('tiposervico', $servico);
                })->orderBy('idregional')
                ->orderBy('dia','DESC')
                ->orderBy('hora')
                ->paginate(25);
        }
    }

    private function validarUpdate($user, $dados, $agendamento)
    {   
        $updateStatus = \Route::is('agendamentos.updateStatus');

        if(!$updateStatus && !isset($dados['antigo'])) 
            throw new Exception('Erro por falta de campo no request', 400);

        if(isset($dados['antigo']))
        {
            if(($dados['antigo'] == 0 && !$agendamento->isAfter()) || ($dados['antigo'] == 1 && $agendamento->isAfter()))
                throw new Exception('Erro na validação de campo no request', 400);
            unset($dados['antigo']);
        }            

        if(isset($dados['nome']))
            $dados['nome'] = mb_convert_case(mb_strtolower($dados['nome']), MB_CASE_TITLE); 

        if(!isset($dados['status']) && isset($dados['idusuario']))
            return [
                'message' => '<i class="icon fa fa-ban"></i>Agendamento sem status não pode ter atendente',
                'class' => 'alert-danger'
            ];

        $cancelado = isset($dados['status']) && ($dados['status'] != Agendamento::STATUS_CANCELADO);
        if($agendamento->isAfter() && $cancelado)
            return [
                'message' => '<i class="icon fa fa-ban"></i>Status do agendamento não pode ser modificado para '
                .Agendamento::STATUS_COMPARECEU.' ou '.Agendamento::STATUS_NAO_COMPARECEU.' antes da data agendada',
                'class' => 'alert-danger'
            ];
        
        if($updateStatus)
            $dados = [
                'idagendamento' => $dados['idagendamento'],
                'status' => $dados['status'],
                'idusuario' => $user->idusuario
            ];

        return $dados;
    }

    private function tabelaCompleta($user, $resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Protocolo',
            'Nome/CPF',
            'Horário/Dia',
            'Serviço',
            'Status'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = $user->can('updateOther', $user);
        foreach($resultados as $resultado) 
        {
            $acoes = $resultado->getBtnByStatus();
            if($userPodeEditar)
                $acoes .= '&nbsp;&nbsp;<a href="'.route('agendamentos.edit', $resultado->idagendamento).'" class="btn btn-sm btn-default">Editar</a>';
            if($resultado->status == Agendamento::STATUS_COMPARECEU)
                $acoes .= '<small class="d-block">Atendido por: <strong>'.$resultado->user->nome.'</strong></small>';
            $conteudo = [
                $resultado->protocolo.'<br><small>Código: '.$resultado->idagendamento.'</small>',
                $resultado->nome.'<br>'.$resultado->cpf,
                $resultado->hora.'<br><small><strong>'.onlyDate($resultado->dia).'</strong></small>',
                $resultado->tiposervico.'<br><small>('.$resultado->regional->regional.')',
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-bordered',
            'table-striped'
        ];

        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function listar($user, $request = null, MediadorServiceInterface $service = null)
    {
        session(['url' => url()->full()]);

        if(isset($request) && isset($service))
        {
            $dados = $this->validacaoFiltroAtivo($user, $request);
            $resultados = $this->getResultadosFiltro($dados);
            $this->variaveis['mostraFiltros'] = true;
    
            return [
                'resultados' => $resultados, 
                'tabela' => $this->tabelaCompleta($user, $resultados), 
                'temFiltro' => $this->filtro($user, $request, $service),
                'variaveis' => (object) $this->variaveis,
            ];
        }

        $resultados = $this->pendentesByPerfil($user, false);
        
        $this->variaveis['continuacao_titulo'] = 'pendentes de validação';
        $this->variaveis['plural'] = 'agendamentos pendentes';
        $this->variaveis['btn_criar'] = '<a class="btn btn-primary" href="'.route('agendamentos.lista').'"><i class="fas fa-list"></i> Lista de Agendamentos</a>';

        return [
            'tabela' => $this->tabelaCompleta($user, $resultados),
            'resultados' => $resultados,
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function view($user, $id)
    {
        $agendamento = Agendamento::findOrFail($id);

        $atendOrGere = $user->can('atendenteOrGerSeccionais', $user);
        $sameRegional = $user->can('sameRegional', $agendamento);
        if($atendOrGere && !$sameRegional)
            throw new Exception('Não autorizado', 403);

        $status = Agendamento::status();
        if($agendamento->isAfter())
        {
            unset($status[0]);
            unset($status[1]);
        } 

        $this->variaveis['cancela_idusuario'] = true;

        // Enquanto não possui o UserService
        $idregional = $user->idregional;
        $atendentes = \App\User::select('idusuario', 'nome', 'idperfil')
            ->whereIn('idperfil', [4, 6, 10, 12, 13, 18])
            ->orWhere(function($query) use($idregional) {
                $query->whereIn('idperfil', [8, 21])
                ->where('idregional', $idregional);
            })
            ->orderBy('nome')
            ->get();
    
        return [
            'servicos' => Agendamento::servicosCompletos(),
            'status' => $status,
            'variaveis' => (object) $this->variaveis,
            'atendentes' => $atendentes,
            'resultado' => $agendamento
        ];
    }

    public function enviarEmail($user, $id)
    {
        $agendamento = Agendamento::findOrFail($id);

        if(!$agendamento->isAfter())
            return [
                'message' => '<i class="icon fa fa-ban"></i>Não pode reenviar email para agendamento de hoje para trás',
                'class' => 'alert-danger'
            ];

        $atendOrGere = $user->can('atendenteOrGerSeccionais', $user);
        $sameRegional = $user->can('sameRegional', $agendamento);
        if($atendOrGere && !$sameRegional)
            throw new Exception('Não autorizado', 403);

        Mail::to($agendamento->email)->queue(new AgendamentoMailGuest($agendamento));
    }

    public function save($user, $dados, $id = null)
    {
        $codigo = isset($id) ? $id : $dados['idagendamento'];
        $agendamento = Agendamento::findOrFail($codigo);

        $atendOrGere = $user->can('atendenteOrGerSeccionais', $user);
        $sameRegional = $user->can('sameRegional', $agendamento);
        if($atendOrGere && !$sameRegional)
            throw new Exception('Não autorizado', 403);

        $valido = $this->validarUpdate($user, $dados, $agendamento);
        if(isset($valido['message']))
            return $valido;

        $agendamento->update($valido);

        if(isset($id))
            event(new CrudEvent('agendamento', 'editou', $id));
        else
        {
            $status = $dados['status'] == Agendamento::STATUS_COMPARECEU ? 'presença' : 'falta';
            event(new CrudEvent('agendamento', 'confirmou '.$status, $agendamento->idagendamento));
        }
    }

    public function buscar($user, $busca)
    {
        $regional = $user->can('atendenteOrGerSeccionais', $user) ? $user->idregional : null;

        $resultados = Agendamento::with(['user', 'regional'])
            ->when($regional, function ($query) use ($regional, $busca) {
                return $query->where('idregional', $regional)
                    ->where(function($q) use ($busca) {
                        $q->where('cpf', 'LIKE', '%'.$busca.'%')
                        ->orWhere('email', 'LIKE', '%'.$busca.'%')
                        ->orWhere('protocolo', 'LIKE', '%'.$busca.'%')
                        ->orWhere('idagendamento', 'LIKE', $busca)
                        ->orWhere('nome', 'LIKE', '%'.$busca.'%');
                });
            }, function ($query) use ($busca) {
                return $query->where('nome', 'LIKE', '%'.$busca.'%')
                    ->orWhere('idagendamento', 'LIKE', $busca)
                    ->orWhere('cpf', 'LIKE', '%'.$busca.'%')
                    ->orWhere('email', 'LIKE', '%'.$busca.'%')
                    ->orWhere('protocolo', 'LIKE', '%'.$busca.'%');
            })->paginate(25);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($user, $resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function getServicosOrStatusOrCompletos($tipo)
    {
        $array = [
            'servicos' => Agendamento::servicos(),
            'status' => Agendamento::status(),
            'completos' => Agendamento::servicosCompletos()
        ];

        return isset($array[$tipo]) ? $array[$tipo] : null;
    }

    public function countAll()
    {
        return Agendamento::count();
    }

    public function pendentesByPerfil($user, $count = true)
    {
        $perfil = $user->idperfil;
        $idregional = $user->idregional;

        $resultados = Agendamento::with(['user', 'regional'])
            ->where('dia', '<', date('Y-m-d'))
            ->whereNull('status')
            ->when($perfil == 12, function ($query) {
                $query->where('idregional', 1);
            })->when($perfil == 13, function ($query) {
                $query->where('idregional', '!=', 1);
            })->when(($perfil == 8) || ($perfil == 21), function ($query) use ($idregional) {
                $query->where('idregional', $idregional);
            })->orderBy('dia', 'DESC')
            ->orderBy('hora')
            ->paginate(10);

        return $count ? $resultados->total() : $resultados;
    }

    public function site()
    {
        return $this->site;
    }

    public function bloqueio()
    {
        return $this->bloqueio;
    }
}