<?php

namespace App\Services;

use App\Contracts\AgendamentoServiceInterface;
use App\Contracts\MediadorServiceInterface;
use App\Agendamento;
use App\AgendamentoBloqueio;
use App\Events\CrudEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoMailGuest;

class AgendamentoService implements AgendamentoServiceInterface {

    private $variaveis;
    private $variaveisBloqueio;
    private $renameSede;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'agendamento',
            'singulariza' => 'o agendamento',
            'plural' => 'agendamentos',
            'pluraliza' => 'agendamentos'
        ];

        $this->variaveisBloqueio = [
            'singular' => 'bloqueio',
            'singulariza' => 'o bloqueio',
            'plural' => 'bloqueios de agendamento',
            'pluraliza' => 'bloqueios',
            'form' => 'agendamentobloqueio',
            'cancelar' => 'agendamentos/bloqueios',
            'titulo_criar' => 'Cadastrar novo bloqueio',
            'btn_criar' => '<a href="'.route('agendamentobloqueios.criar').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Bloqueio</a>',
            'busca' => 'agendamentos/bloqueios',
        ];

        $this->renameSede = 'São Paulo - Avenida Brigadeiro Luís Antônio';
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
        if($resultado->isAfter())
            $default = null;
        else
        {
            $default = '<form method="POST" action="'.route('agendamentos.updateStatus').'" class="d-inline">';
            $default .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $default .= '<input type="hidden" name="_method" value="PUT" id="method" />';
            $default .= '<input type="hidden" name="idagendamento" value="'.$resultado->idagendamento.'" />';
            $default .= '<button type="submit" name="status" class="btn btn-sm btn-primary" value="'.Agendamento::STATUS_COMPARECEU.'">Confirmar</button>';
            $default .= '<button type="submit" name="status" class="btn btn-sm btn-danger ml-1" value="'.Agendamento::STATUS_NAO_COMPARECEU.'">'.Agendamento::STATUS_NAO_COMPARECEU.'</button>';
            $default .= '</form>';
        }
        
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

        if(\Route::is('agendamentos.filtro'))
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

    private function validarUpdate($dados, $agendamento)
    {   
        $updateStatus = \Route::is('agendamentos.updateStatus');

        if(!$updateStatus && !isset($dados['antigo'])) 
            abort(500, 'Erro por falta de campo no request');

        if(isset($dados['antigo']))
        {
            if(($dados['antigo'] == 0 && !$agendamento->isAfter()) || ($dados['antigo'] == 1 && $agendamento->isAfter()))
                abort(500, 'Erro na validação de campo no request');
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
                'idusuario' => auth()->user()->idusuario
            ];

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

    private function tabelaCompletaBloqueio($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Regional',
            'Duração',
            'Horas bloqueadas/qtd de agend. alterada',
            'Qtd. de agend. por horário',
            'Ações',
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        $userPodeExcluir = auth()->user()->can('delete', auth()->user());
        foreach($resultados as $resultado) 
        {
            $acoes = '';
            $duracao = 'Início: '.onlyDate($resultado->diainicio).'<br />';
            $duracao .= 'Término: '.$resultado->getMsgDiaTermino();
            if($userPodeEditar) 
                $acoes .= '<a href="'.route('agendamentobloqueios.edit', $resultado->idagendamentobloqueio).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir) {
                $acoes .= '<form method="POST" action="'.route('agendamentobloqueios.delete', $resultado->idagendamentobloqueio).'" class="d-inline-block">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar" onclick="return confirm(\'Tem certeza que deseja cancelar o bloqueio?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->idagendamentobloqueio,
                $resultado->regional->regional,
                $duracao,
                $resultado->horarios,
                $resultado->qtd_atendentes,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        // Monta e retorna tabela        
        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function listar($request = null, MediadorServiceInterface $service = null)
    {
        session(['url' => url()->full()]);

        if(isset($request) && isset($service))
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

        $resultados = $this->pendentesByPerfil(false);
        
        $this->variaveis['continuacao_titulo'] = 'pendentes de validação';
        $this->variaveis['plural'] = 'agendamentos pendentes';
        $this->variaveis['btn_criar'] = '<a class="btn btn-primary" href="'.route('agendamentos.lista').'"><i class="fas fa-list"></i> Lista de Agendamentos</a>';

        return [
            'tabela' => $this->tabelaCompleta($resultados),
            'resultados' => $resultados,
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function listarBloqueio()
    {
        $resultados = AgendamentoBloqueio::with('regional')
            ->orderBy('idagendamentobloqueio', 'DESC')
            ->where('diatermino', '>=', date('Y-m-d'))
            ->orWhereNull('diatermino')
            ->paginate(10);

        if(auth()->user()->cannot('create', auth()->user()))
            unset($this->variaveisBloqueio['btn_criar']);
        
        return [
            'tabela' => $this->tabelaCompletaBloqueio($resultados),
            'resultados' => $resultados,
            'variaveis' => (object) $this->variaveisBloqueio,
        ];
    }

    public function view($id)
    {
        $agendamento = Agendamento::findOrFail($id);

        $atendOrGere = auth()->user()->can('atendenteOrGerSeccionais', auth()->user());
        $sameRegional = auth()->user()->can('sameRegional', $agendamento);
        abort_if($atendOrGere && !$sameRegional, 403);

        $status = $this->status();
        if($agendamento->isAfter())
        {
            unset($status[0]);
            unset($status[1]);
        } 

        $this->variaveis['cancela_idusuario'] = true;
    
        return [
            'servicos' => $this->servicosCompletos(),
            'status' => $status,
            'variaveis' => (object) $this->variaveis,
            'atendentes' => $agendamento->regional->users()->select('idusuario', 'nome')->where('idperfil', 8)->withoutTrashed()->get(),
            'resultado' => $agendamento
        ];
    }

    public function viewBloqueio($id = null, MediadorServiceInterface $service = null)
    {
        if(isset($service) && !isset($id))
        {
            $regionais = $service->getService('Regional')->all();
            $regionais->find(1)->regional = $this->renameSede;
    
            return [
                'variaveis' => (object) $this->variaveisBloqueio,
                'regionais' => $regionais->sortBy('regional'),
            ];
        }
        
        $bloqueio = AgendamentoBloqueio::findOrFail($id);

        if($bloqueio->idregional == 1)
            $bloqueio->regional->regional = $this->renameSede;

        return [
            'variaveis' => (object) $this->variaveisBloqueio,
            'resultado' => $bloqueio,
        ];
    }

    public function viewSite(MediadorServiceInterface $service)
    {
        $regionais = $service->getService('Regional')->all();
        $regionais->find(1)->regional = $this->renameSede;

        $servicos = $this->servicos();

        if(!$service->getService('PlantaoJuridico')->plantaoJuridicoAtivo()) 
            unset($servicos[array_search(Agendamento::SERVICOS_PLANTAO_JURIDICO, $servicos)]);      

        return [
            'regionais' => $regionais->sortBy('regional'),
            'pessoas' => Agendamento::TIPOS_PESSOA,
            'servicos' => $servicos
        ];
    }

    public function enviarEmail($id)
    {
        $agendamento = Agendamento::findOrFail($id);

        if(!$agendamento->isAfter())
            return [
                'message' => '<i class="icon fa fa-ban"></i>Não pode reenviar email para agendamento de hoje para trás',
                'class' => 'alert-danger'
            ];

        $atendOrGere = auth()->user()->can('atendenteOrGerSeccionais', auth()->user());
        $sameRegional = auth()->user()->can('sameRegional', $agendamento);
        abort_if($atendOrGere && !$sameRegional, 403);

        Mail::to($agendamento->email)->send(new AgendamentoMailGuest($agendamento));
    }

    public function save($dados, $id = null)
    {
        $codigo = isset($id) ? $id : $dados['idagendamento'];
        $agendamento = Agendamento::findOrFail($codigo);

        $atendOrGere = auth()->user()->can('atendenteOrGerSeccionais', auth()->user());
        $sameRegional = auth()->user()->can('sameRegional', $agendamento);
        abort_if($atendOrGere && !$sameRegional, 403);

        $valido = $this->validarUpdate($dados, $agendamento);
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

    public function saveBloqueio($dados, $id = null)
    {
        $dados['idusuario'] = auth()->user()->idusuario;
        $dados['horarios'] = implode(',', $dados['horarios']);

        if(isset($id))
        {
            unset($dados['idregional']);
            $bloqueio = AgendamentoBloqueio::findOrFail($id);
            $bloqueio->update($dados);

            event(new CrudEvent('bloqueio de agendamento', 'editou', $id));
            return null;
        }

        $bloqueio = AgendamentoBloqueio::create($dados);
        event(new CrudEvent('bloqueio de agendamento', 'criou', $bloqueio->idagendamentobloqueio));
        return null;
    }

    public function delete($id)
    {
        return AgendamentoBloqueio::findOrFail($id)->delete() ? event(new CrudEvent('bloqueio de agendamento', 'cancelou', $id)) : null;
    }

    public function buscar($busca)
    {
        $regional = auth()->user()->can('atendenteOrGerSeccionais', auth()->user()) ? auth()->user()->idregional : null;

        $resultados = Agendamento::with(['user', 'regional'])
            ->when($regional, function ($query, $regional) use ($busca) {
                return $query->where('idregional', $regional)
                    ->where(function($q) use ($busca) {
                        $q->where('cpf', 'LIKE', '%'.$busca.'%')
                        ->orWhere('email', 'LIKE', '%'.$busca.'%')
                        ->orWhere('protocolo', 'LIKE', '%'.$busca.'%');
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
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function buscarBloqueio($busca)
    {
        $resultados = AgendamentoBloqueio::with('regional')
            ->whereHas('regional', function($q) use($busca){
                $q->where('regional', 'LIKE', '%'.$busca.'%');
            })->paginate(10);

        $this->variaveisBloqueio['slug'] = 'agendamentos/bloqueios';

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompletaBloqueio($resultados), 
            'variaveis' => (object) $this->variaveisBloqueio
        ];
    }

    public function getServicosOrStatusOrCompletos($tipo)
    {
        $array = [
            'servicos' => $this->servicos(),
            'status' => $this->status(),
            'completos' => $this->servicosCompletos()
        ];

        return isset($array[$tipo]) ? $array[$tipo] : null;
    }

    public function countAll()
    {
        return Agendamento::count();
    }

    public function pendentesByPerfil($count = true)
    {
        $perfil = auth()->user()->idperfil;
        $idregional = auth()->user()->idregional;

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
            ->paginate(10);

        return $count ? $resultados->total() : $resultados;
    }

    public function getDiasHorasAjaxSite($dados, MediadorServiceInterface $service)
    {
        $regional = $service->getService('Regional')->getById($dados['idregional']);

        $resultado = $dados['servico'] == Agendamento::SERVICOS_PLANTAO_JURIDICO ? 
            $regional->plantaoJuridico()->with('bloqueios')->where('qtd_advogados', '>', 0)->first() : $regional;

        if(isset($dados['dia']))
        {
            $dia = Carbon::createFromFormat('d/m/Y', $dados['dia'])->format('Y-m-d');
            $agendados = $resultado->getAgendadosPorPeriodo($dia, $dia);
            $agendadosFinal = $agendados->isNotEmpty() ? $agendados[$dia] : $agendados;
            $horarios = $resultado->removeHorariosSeLotado($agendadosFinal , $dia, $resultado->getHorariosComBloqueio());

            return $horarios;
        }
    
        if($dados['servico'] == Agendamento::SERVICOS_PLANTAO_JURIDICO)
            $agendados = $resultado->getAgendadosPorPeriodo($resultado->dataInicial, $resultado->dataFinal);
        else
            $agendados = $resultado->getAgendadosPorPeriodo(Carbon::tomorrow()->format('Y-m-d'), Carbon::tomorrow()->addDays(30)->format('Y-m-d'));
        $diasLotados = $resultado->getDiasSeLotado($agendados);

        return $diasLotados;
    }

    /** 
     * =======================================================================================================
     * PLANTÃO JURÍDICO
     * =======================================================================================================
     */

    public function countPlantaoJuridicoByCPF($cpf, $regional, $plantao)
    {
        return Agendamento::where('cpf', $cpf)
            ->where('idregional', $regional)
            ->where('tiposervico', 'LIKE', Agendamento::SERVICOS_PLANTAO_JURIDICO.'%')
            ->whereNull('status')
            ->whereBetween('dia', [$plantao->dataInicial, $plantao->dataFinal])
            ->count();
    }

    // Momentaneo até refatorar o AgendamentoSite
    public function getByRegional($idregional)
    {
        return AgendamentoBloqueio::where('idregional', $idregional)
            ->where('diatermino','>=', date('Y-m-d'))
            ->get();
    }
}