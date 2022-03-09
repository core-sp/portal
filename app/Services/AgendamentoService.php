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
        $default .= '<input type="hidden" name="_token" id="tokenStatusAgendamento" value="'.csrf_token().'" />';
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

    private function confereFiltroAtivo($request)
    {
        $mindia = Carbon::today();
        $maxdia = Carbon::today();

        $mindiaArray = explode('/', $request->mindia);
        $checaMindia = (count($mindiaArray) != 3 || $mindiaArray[2] == null)  ? false : checkdate($mindiaArray[1], $mindiaArray[0], $mindiaArray[2]);
        if(!$checaMindia) 
            return [
                'message' => '<i class="icon fa fa-ban"></i>Data de início do filtro inválida',
                'class' => 'alert-danger'
            ];

        $mindia = date('Y-m-d', strtotime(str_replace('/', '-', $request->mindia)));

        $maxdiaArray = explode('/', $request->maxdia);
        $checaMaxdia = (count($maxdiaArray) != 3 || $maxdiaArray[2] == null)  ? false : checkdate($maxdiaArray[1], $maxdiaArray[0], $maxdiaArray[2]);
        if(!$checaMaxdia) 
            return [
                'message' => '<i class="icon fa fa-ban"></i>Data de término do filtro inválida',
                'class' => 'alert-danger'
            ];

        $maxdia = date('Y-m-d', strtotime(str_replace('/', '-', $request->maxdia)));

        $regional = isset($request->regional) ? $request->regional : auth()->user()->idregional;
        $status = isset($request->status) && ($request->status != 'Qualquer') ? $request->status : null;
        $servico = isset($request->servico) && ($request->servico != 'Qualquer') ? $request->servico : null;

        return [
            'mindia' => $mindia,
            'maxdia' => $maxdia,
            'regional' => $regional,
            'status' => $status,
            'servico' => $servico 
        ];
    }

    private function filtro($request, MediadorServiceInterface $service)
    {
        if($request->filled('filtro') && ($request->filtro == 'sim'))
        {
            $temFiltro = true;
            $this->variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
        } else
        {
            $temFiltro = null;
            $this->variaveis['continuacao_titulo'] = 'em <strong>'.auth()->user()->regional->regional.' - '.date('d\/m\/Y').'</strong>';
        }

        $filtro = '<form method="GET" action="'.route('agendamentos.filtro').'" id="filtroAgendamento" class="mb-0">';
        $filtro .= '<div class="form-row filtroAge">';
        $filtro .= '<input type="hidden" name="filtro" value="sim" />';

        if(auth()->user()->cannot('atendenteOrGerSeccionais', auth()->user()))
        {
            $regionais = $service->getService('Regional')->all();

            $filtro .= '<div class="form-group mb-0 col">';
            $filtro .= '<label>Seccional</label>';
            $filtro .= '<select class="custom-select custom-select-sm mr-2" id="regional" name="regional">';
            $filtro .= !isset($request->regional) ? '<option value="" selected>Todas</option>' : '<option value="">Todas</option>';

            foreach($regionais as $regional)
                $filtro .= isset($request->regional) && ($request->regional == $regional->idregional) ? 
                '<option value="'.$regional->idregional.'" selected>'.$regional->regional.'</option>' :
                '<option value="'.$regional->idregional.'">'.$regional->regional.'</option>';

            $filtro .= '</select>';
            $filtro .= '</div>';
        }

        $filtro .= '<div class="form-group mb-0 col">';
        $filtro .= '<label>Status</label>';
        $filtro .= '<select class="custom-select custom-select-sm" name="status">';
        $filtro .= isset($request->status) && ($request->status == 'Qualquer') ? 
        '<option value="Qualquer" selected>Qualquer</option>' : 
        '<option value="Qualquer">Qualquer</option>';

        $status = $this->status();

        foreach($status as $s)
            $filtro .= isset($request->status) && ($request->status == $s) ? 
            '<option value="'.$s.'" selected>'.$s.'</option>' :
            '<option value="'.$s.'">'.$s.'</option>';

        $filtro .= '</select>';
        $filtro .= '</div>';

        $filtro .= '<div class="form-group mb-0 col">';
        $filtro .= '<label>Serviço</label>';
        $filtro .= '<select class="custom-select custom-select-sm" name="servico">';
        $filtro .= isset($request->servico) && ($request->servico == 'Qualquer') ? 
        '<option value="Qualquer" selected>Qualquer</option>' : 
        '<option value="Qualquer">Qualquer</option>';

        $servicos = $this->servicosCompletos();

        foreach($servicos as $servico)
            $filtro .= isset($request->servico) && ($request->servico == $servico) ? 
            '<option value="'.$servico.'" selected>'.$servico.'</option>' :
            '<option value="'.$servico.'">'.$servico.'</option>';

        $filtro .= '</select>';
        $filtro .= '</div>';
    
        $filtro .= '<div class="form-group mb-0 col">';
        $filtro .= '<label>De</label>';

        $textoData = '<input type="date" class="form-control d-inline-block dataInput form-control-sm" name="mindia" id="mindiaFiltro" value="';
        $filtro .= isset($request->mindia) ? $textoData.$request->mindia.'" />' : $textoData.date('Y-m-d').'" />';

        $filtro .= '</div>';
        $filtro .= '<div class="form-group mb-0 col">';
        $filtro .= '<label>Até</label>';

        $textoData = '<input type="date" class="form-control d-inline-block dataInput form-control-sm" name="maxdia" id="maxdiaFiltro" value="';
        $filtro .= isset($request->maxdia) ? $textoData.$request->maxdia.'" />' : $textoData.date('Y-m-d').'" />';

        $filtro .= '</div>';
        $filtro .= '<div class="form-group mb-0 col-auto align-self-end">';
        $filtro .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
        $filtro .= '</div>';
        $filtro .= '</div>';
        $filtro .= '</form>';

        $this->variaveis['filtro'] = $filtro;

        return $temFiltro;
    }

    private function getResultadosToFiltro($request)
    {
        if($request->filled('filtro') && ($request->filtro == 'sim'))
        {
            $dados = $this->confereFiltroAtivo($request);

            $regional = $dados['regional'];
            $status = $dados['status'];
            $servico = $dados['servico'];

            return Agendamento::with(['user', 'regional'])
                ->whereBetween('dia', [
                    $dados['mindia'], $dados['maxdia']
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
        $resultados = $this->getResultadosToFiltro($request);
        $this->variaveis['mostraFiltros'] = true;

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'temFiltro' => $this->filtro($request, $service),
            'variaveis' => (object) $this->variaveis,
        ];
    }
}