<?php

namespace App\Services;

use App\Contracts\AgendamentoServiceInterface;
use App\Contracts\MediadorServiceInterface;
use App\Agendamento;
use App\Events\CrudEvent;
use Carbon\Carbon;

class AgendamentoService implements AgendamentoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'agendamento',
            'singulariza' => 'o agendamento',
            'plural' => 'agendamentos',
            'pluraliza' => 'agendamentos'
        ];
    }

    private function status()
    { 
        return [
            Agendamento::STATUS_COMPARECEU,
            Agendamento::STATUS_NAO_COMPARECEU,
            Agendamento::STATUS_CANCELADO
        ];
    }

    private function servicos()
    {
        return [
            Agendamento::SERVICOS_ATUALIZACAO_DE_CADASTRO,
            Agendamento::SERVICOS_CANCELAMENTO_DE_REGISTRO,
            Agendamento::SERVICOS_PLANTAO_JURIDICO,
            // Agendamento::SERVICOS_REFIS,
            Agendamento::SERVICOS_REGISTRO_INICIAL,
            Agendamento::SERVICOS_OUTROS
        ];
    }

    private function servicosCompletos()
    {
        $resultado = array();

        foreach($this->servicos() as $servico)
            foreach(Agendamento::TIPOS_PESSOA as $tipoPessoa)
                array_push($resultado, $servico.' para '.$tipoPessoa);

        return $resultado;
    }

    private function getBtnByStatus($resultado)
    {
        $default = '<form method="POST" id="statusAgendamento" action="'.route('agendamentos.updateStatus').'" class="d-inline">';
        $default .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
        $default .= '<input type="hidden" name="_method" value="PUT" id="method" />';
        $default .= '<input type="hidden" name="idagendamento" value="'.$resultado->idagendamento.'" />';
        $default .= '<button type="submit" name="status" id="btnSubmit" class="btn btn-sm btn-primary" value="'.Agendamento::STATUS_COMPARECEU.'">Confirmar</button>';
        $default .= '<button type="submit" name="status" id="btnSubmit" class="btn btn-sm btn-danger ml-1" value="'.Agendamento::STATUS_NAO_COMPARECEU.'">'.Agendamento::STATUS_NAO_COMPARECEU.'</button>';
        $default .= '</form>';
        
        $tiposStatus = [
            Agendamento::STATUS_CANCELADO => '<strong>'.Agendamento::STATUS_CANCELADO.'</strong>',
            Agendamento::STATUS_COMPARECEU => '<p class="d-inline"><i class="fas fa-check checkIcone"></i>&nbsp;&nbsp;'.Agendamento::STATUS_COMPARECEU.'</p>',
            Agendamento::STATUS_NAO_COMPARECEU => '<strong>'.Agendamento::STATUS_NAO_COMPARECEU.'</strong>'
        ];

        return isset($tiposStatus[$resultado->status]) ? $tiposStatus[$resultado->status] : $default;
    }

    private function validacaoFiltroAtivo($request)
    {
        $canFiltroRegional = auth()->user()->cannot('atendenteOrGerSeccionais', auth()->user());
        $datemin = $request->filled('datemin') ? Carbon::parse($request->datemin) : Carbon::today();
        $datemax = $request->filled('datemax') ? Carbon::parse($request->datemax) : Carbon::today();

        if($datemax->lt($datemin))
            return [
                'message' => '<i class="icon fa fa-ban"></i>Data final deve ser maior ou igual a data inicial',
                'class' => 'alert-danger'
            ];

        return [
            'datemin' => $datemin->format('Y-m-d'),
            'datemax' => $datemax->format('Y-m-d'),
            'regional' => $request->has('regional') && $canFiltroRegional ? $request->regional : auth()->user()->idregional,
            'status' => $request->filled('status') && ($request->status != 'Qualquer') ? $request->status : null,
            'servico' => $request->filled('servico') && ($request->servico != 'Qualquer') ? $request->servico : null
        ];
    }

    private function filtro($request, MediadorServiceInterface $service)
    {
        $filtro = '';
        $temFiltro = null;
        $this->variaveis['continuacao_titulo'] = 'em <strong>'.auth()->user()->regional->regional.' - '.date('d\/m\/Y').'</strong>';

        if($request->filled('filtro') && ($request->filtro == 'sim'))
        {
            $temFiltro = true;
            $this->variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
        }

        if(auth()->user()->cannot('atendenteOrGerSeccionais', auth()->user()))
        {
            $regionais = $service->getService('Regional')->all();
            $options = !isset($request->regional) ? 
            getFiltroOptions('', 'Todas', true) : getFiltroOptions('', 'Todas');

            foreach($regionais as $regional)
                $options .= isset($request->regional) && ($request->regional == $regional->idregional) ? 
                getFiltroOptions($regional->idregional, $regional->regional, true) : 
                getFiltroOptions($regional->idregional, $regional->regional);

            $filtro .= getFiltroCamposSelect('Seccional', 'regional', $options);
        }

        $options = isset($request->status) && ($request->status == 'Qualquer') ? 
        getFiltroOptions('', 'Qualquer', true) : getFiltroOptions('', 'Qualquer');

        foreach($this->status() as $s)
            $options .= isset($request->status) && ($request->status == $s) ? 
            getFiltroOptions($s, $s, true) : getFiltroOptions($s, $s);

        $filtro .= getFiltroCamposSelect('Status', 'status', $options);
    
        $options = isset($request->servico) && ($request->servico == 'Qualquer') ? 
        getFiltroOptions('Qualquer', 'Qualquer', true) : getFiltroOptions('Qualquer', 'Qualquer');

        foreach($this->servicosCompletos() as $servico)
            $options .= isset($request->servico) && ($request->servico == $servico) ? 
            getFiltroOptions($servico, $servico, true) : getFiltroOptions($servico, $servico);

        $filtro .= getFiltroCamposSelect('Serviço', 'servico', $options);
        $filtro .= getFiltroCamposDate($request->datemin, $request->datemax);
        $filtro = getFiltro(route('agendamentos.filtro'), $filtro);

        $this->variaveis['filtro'] = $filtro;

        return $temFiltro;
    }

    private function getResultadosFiltro($dados = null)
    {
        if(isset($dados) && !isset($dados['message']))
        {
            $regional = $dados['regional'];
            $status = $dados['status'];
            $servico = $dados['servico'];

            return Agendamento::with(['user', 'regional'])
                ->whereBetween('dia', [
                    $dados['datemin'], $dados['datemax']
                ])->when($regional, function ($query, $regional) {
                    $query->where('idregional', $regional);
                })->when($status, function ($query, $status) {
                    $query->where('status', $status);
                })->when($servico, function ($query, $servico) {
                    $query->where('tiposervico', $servico);
                })->orderBy('idregional')
                ->orderBy('dia','DESC')
                ->orderBy('hora')
                ->paginate(25);
        }

        return Agendamento::with(['user', 'regional'])
            ->where('dia', date('Y-m-d'))
            ->where('idregional', auth()->user()->idregional)
            ->orderBy('dia')
            ->orderBy('hora')
            ->paginate(25);
    }
    private function validarUpdate($dados)
    {
        $statusInvalido = isset($dados['status']) && !in_array($dados['status'], $this->status());
        $servicoInvalido = !in_array($dados['tiposervico'], $this->servicosCompletos());
        if($statusInvalido || $servicoInvalido)
            return [
                'message' => '<i class="icon fa fa-ban"></i>Status ou Tipo de Serviço não encontrado',
                'class' => 'alert-danger'
            ];
        
        if(!isset($dados['status']) && isset($dados['idusuario']))
            return [
                'message' => '<i class="icon fa fa-ban"></i>Agendamento sem status não pode ter atendente',
                'class' => 'alert-danger'
            ];

        $dados['nome'] = mb_convert_case(mb_strtolower($dados['nome']), MB_CASE_TITLE);

        return $dados;
    }

    private function tabelaCompleta($resultados)
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
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        foreach($resultados as $resultado) 
        {
            $acoes = $this->getBtnByStatus($resultado);
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

    public function index($request, MediadorServiceInterface $service)
    {
        $dados = $this->validacaoFiltroAtivo($request);
        $resultados = $this->getResultadosFiltro($dados);
        $this->variaveis['mostraFiltros'] = true;

        return [
            'erro' => isset($dados['message']) ? $dados : null,
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'temFiltro' => $this->filtro($request, $service),
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function view($id)
    {
        $agendamento = Agendamento::findOrFail($id);

        $atendOrGere = auth()->user()->can('atendenteOrGerSeccionais', auth()->user());
        $sameRegional = auth()->user()->can('sameRegional', $agendamento);
        abort_if($atendOrGere && !$sameRegional, 403);

        $atendentes = date('Y-m-d') >= $agendamento->dia ? 
        $agendamento->regional->users()->select('idusuario', 'nome')->where('idperfil', 8)->withoutTrashed()->get() : null;

        $msg = $agendamento->getMsgByStatus();
        $this->variaveis['cancela_idusuario'] = true;

        return [
            'servicos' => $this->servicosCompletos(),
            'status' => $this->status(),
            'variaveis' => (object) $this->variaveis,
            'atendentes' => $atendentes,
            'resultado' => $agendamento
        ];
    }

    public function save($dados, $id)
    {
        $agendamento = Agendamento::findOrFail($id);

        $atendOrGere = auth()->user()->can('atendenteOrGerSeccionais', auth()->user());
        $sameRegional = auth()->user()->can('sameRegional', $agendamento);
        abort_if($atendOrGere && !$sameRegional, 403);
        
        $valido = $this->validarUpdate($dados);

        if(isset($valido['message']))
            return $valido;

        $agendamento->update($valido);

        event(new CrudEvent('agendamento', 'editou', $id));
    }

    public function buscar($busca)
    {
        $regional = auth()->user()->can('atendenteOrGerSeccionais', auth()->user()) ? auth()->user()->idregional : null;

        $resultados = Agendamento::when($regional, function ($query, $regional) use ($busca) {
                return $query->where('idregional', $regional)
                    ->where(function($q) use ($busca) {
                        $q->where('cpf', 'LIKE', '%'.$busca.'%')
                        ->orWhere('email', 'LIKE', '%'.$busca.'%')
                        ->orWhere('protocolo', 'LIKE', '%'.$busca.'%');
                });
            }, function ($query) use ($busca) {
                return $query->where('nome', 'LIKE', '%'.$busca.'%')
                ->orWhere('cpf', 'LIKE', '%'.$busca.'%')
                ->orWhere('email', 'LIKE', '%'.$busca.'%')
                ->orWhere('protocolo', 'LIKE', '%'.$busca.'%');
            })->paginate(25);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }
}