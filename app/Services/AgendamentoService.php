<?php

namespace App\Services;

use App\Contracts\AgendamentoServiceInterface;
use App\Contracts\MediadorServiceInterface;
use App\Agendamento;
use App\AgendamentoBloqueio;
use App\Events\CrudEvent;
use App\Events\ExternoEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoMailGuest;
use Exception;

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
            'regional' => $request->filled('regional') && $canFiltroRegional ? $request->regional : auth()->user()->idregional,
            'status' => $request->filled('status') ? $request->status : 'Qualquer',
            'servico' => $request->filled('servico') ? $request->servico : 'Qualquer'
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
            $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14])->sortBy('regional');
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

    private function getResultadosFiltro($dados)
    {
        if(isset($dados) && !isset($dados['message']))
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

    private function validarUpdate($dados, $agendamento)
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

    private function validarStore($dados, MediadorServiceInterface $service)
    {
        $dia = Carbon::createFromFormat('d/m/Y', $dados['dia'])->format('Y-m-d');
        $agendamentos = Agendamento::where('dia', $dia)
            ->where('cpf', $dados['cpf'])
            ->whereNull('status')
            ->get();

        $total = $agendamentos->where('hora', $dados['hora'])->count();
        if($total == 1)
            return [
                'message' => '<i class="icon fa fa-ban"></i>Este CPF já possui um agendamento neste dia e horário',
                'class' => 'alert-danger'
            ];

        $total = $agendamentos->count();
        if($total >= 2)
            return [
                'message' => '<i class="icon fa fa-ban"></i>É permitido apenas 2 agendamentos por cpf por dia',
                'class' => 'alert-danger'
            ];

        $total = Agendamento::where('cpf', $dados['cpf'])
            ->where('status', Agendamento::STATUS_NAO_COMPARECEU)
            ->whereBetween('dia', [Carbon::today()->subDays(90)->format('Y-m-d'), date('Y-m-d')])
            ->count();
        if($total >= 3)
            return [
                'message' => '<i class="icon fa fa-ban"></i>Agendamento bloqueado por excesso de falta nos últimos 90 dias. 
                Favor entrar em contato com o Core-SP para regularizar o agendamento.',
                'class' => 'alert-danger'
            ];

        if($dados['servico'] == Agendamento::SERVICOS_PLANTAO_JURIDICO)
        {
            $regional = $dados['object_regional'];
            $plantao = $regional->plantaoJuridico;
            $total = $regional->agendamentos()
                ->where('cpf', $dados['cpf'])
                ->where('tiposervico', 'LIKE', Agendamento::SERVICOS_PLANTAO_JURIDICO.'%')
                ->whereNull('status')
                ->whereBetween('dia', [$plantao->dataInicial, $plantao->dataFinal])
                ->count();
            if($total >= 1)
                return [
                    'message' => '<i class="icon fa fa-ban"></i>Durante o período deste plantão jurídico é permitido apenas 1 agendamento por cpf',
                    'class' => 'alert-danger'
                ];
        }

        $dados['dia'] = $dia;
        $dados['nome'] = mb_convert_case(mb_strtolower($dados['nome']), MB_CASE_TITLE);
        $dados['protocolo'] = $this->getProtocolo();
        $dados['tiposervico'] = $dados['servico'].' para '.$dados['pessoa'];
        unset($dados['servico']);
        unset($dados['pessoa']);
        unset($dados['termo']);
        unset($dados['object_regional']);

        return $dados;
    }

    private function getProtocolo()
    {
        // Gera a HASH (protocolo) aleatória
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVXZ0123456789';
        do {
            $protocoloGerado = substr(str_shuffle($characters), 0, 6);
            $protocoloGerado = 'AGE-'.$protocoloGerado;
            $countProtocolo = Agendamento::where('protocolo', $protocoloGerado)->count();
        } while($countProtocolo != 0);

        return $protocoloGerado;
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
        if($atendOrGere && !$sameRegional)
            throw new Exception('Não autorizado', 403);

        $status = $this->status();
        if($agendamento->isAfter())
        {
            unset($status[0]);
            unset($status[1]);
        } 

        $this->variaveis['cancela_idusuario'] = true;

        // Enquanto não possui o UserService
        $idregional = auth()->user()->idregional;
        $atendentes = \App\User::select('idusuario', 'nome', 'idperfil')
            ->whereIn('idperfil', [4, 6, 10, 12, 13, 18])
            ->orWhere(function($query) use($idregional) {
                $query->whereIn('idperfil', [8, 21])
                ->where('idregional', $idregional);
            })
            ->orderBy('nome')
            ->get();
    
        return [
            'servicos' => $this->servicosCompletos(),
            'status' => $status,
            'variaveis' => (object) $this->variaveis,
            'atendentes' => $atendentes,
            'resultado' => $agendamento
        ];
    }

    public function viewBloqueio($id = null, MediadorServiceInterface $service = null)
    {
        if(isset($service) && !isset($id))
        {
            $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14]);
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
        $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14]);
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
        if($atendOrGere && !$sameRegional)
            throw new Exception('Não autorizado', 403);

        Mail::to($agendamento->email)->queue(new AgendamentoMailGuest($agendamento));
    }

    public function save($dados, $id = null)
    {
        $codigo = isset($id) ? $id : $dados['idagendamento'];
        $agendamento = Agendamento::findOrFail($codigo);

        $atendOrGere = auth()->user()->can('atendenteOrGerSeccionais', auth()->user());
        $sameRegional = auth()->user()->can('sameRegional', $agendamento);
        if($atendOrGere && !$sameRegional)
            throw new Exception('Não autorizado', 403);

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

    public function saveBloqueio($dados, MediadorServiceInterface $service, $id = null)
    {
        $dados['idusuario'] = auth()->user()->idusuario;
        $dados['horarios'] = $dados['idregional'] != 'Todas' ? implode(',', $dados['horarios']) : null;

        if(isset($id))
        {
            unset($dados['idregional']);
            $bloqueio = AgendamentoBloqueio::findOrFail($id);
            $bloqueio->update($dados);

            event(new CrudEvent('bloqueio de agendamento', 'editou', $id));
            return null;
        }

        if($dados['idregional'] == 'Todas')
        {
            $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14]);
            foreach($regionais as $regional)
            {
                $dados['idregional'] = $regional->idregional;
                $dados['horarios'] = $regional->horariosage;
                $bloqueio = AgendamentoBloqueio::create($dados);
                event(new CrudEvent('bloqueio de agendamento', 'criou', $bloqueio->idagendamentobloqueio));
            }

            return null;
        }

        $bloqueio = AgendamentoBloqueio::create($dados);
        event(new CrudEvent('bloqueio de agendamento', 'criou', $bloqueio->idagendamentobloqueio));
        return null;
    }

    public function saveSite($dados, MediadorServiceInterface $service)
    {
        $valid = $this->validarStore($dados, $service);
        if(isset($valid['message']))
            return $valid;

        $agendamento = Agendamento::create($valid);
        $termo = $agendamento->termos()->create([
            'ip' => request()->ip()
        ]);
        $agendamento->regional = $dados['object_regional'];

        $string = $agendamento->nome.' (CPF: '.$agendamento->cpf.') *agendou* atendimento em *'.$agendamento->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia).' para o serviço '.$agendamento->tiposervico.' e ' .$termo->message();
        event(new ExternoEvent($string));

        $email = new AgendamentoMailGuest($agendamento);
        Mail::to($agendamento->email)->queue($email);

        return [
            'agradece' => $email->body,
            'adendo' => '<i>* As informações serão enviadas ao email cadastrado no formulário</i>'
        ];
    }

    public function consultaSite($dados)
    {
        $protocolo = 'AGE-'.strtoupper($dados['protocolo']);

        return Agendamento::with('regional')
            ->where('protocolo', $protocolo)
            ->where('dia', '>=', date('Y-m-d'))
            ->first();
    }

    public function cancelamentoSite($dados)
    {
        if(!isset($dados['protocolo']))
            return [
                'message' => '<i class="icon fa fa-ban"></i>Protocolo não recebido. Faça a consulta do agendamento novamente.',
                'class' => 'alert-danger'
            ];

        $protocolo = 'AGE-'.strtoupper($dados['protocolo']);
        $agendamento = Agendamento::where('protocolo', $protocolo)
            ->where('dia', '>=', date('Y-m-d'))
            ->whereNull('status')
            ->first();

        if(!isset($agendamento))
            return [
                'message' => '<i class="icon fa fa-ban"></i>O protocolo não existe para agendamentos de hoje em diante',
                'class' => 'alert-danger'
            ];
        if($agendamento->cpf != $dados['cpf'])
            return [
                'message' => '<i class="icon fa fa-ban"></i>O CPF informado não corresponde ao protocolo. Por favor, pesquise novamente o agendamento',
                'class' => 'alert-danger'
            ];

        if(!$agendamento->isAfter())
            return [
                'message' => '<i class="icon fa fa-ban"></i>Cancelamento do agendamento deve ser antes do dia do atendimento',
                'class' => 'alert-danger'
            ];
            
        $update = $agendamento->update(['status' => Agendamento::STATUS_CANCELADO]);
        if(!$update)
            throw new Exception('Erro ao atualizar o status do agendamento', 500);

        $string = $agendamento->nome.' (CPF: '.$agendamento->cpf.') *cancelou* atendimento em *'.$agendamento->regional->regional;
        $string .= '* no dia '.onlyDate($agendamento->dia);
        event(new ExternoEvent($string));
                
        return 'Agendamento cancelado com sucesso!';            
    }

    public function delete($id)
    {
        return AgendamentoBloqueio::findOrFail($id)->delete() ? event(new CrudEvent('bloqueio de agendamento', 'cancelou', $id)) : null;
    }

    public function buscar($busca)
    {
        $regional = auth()->user()->can('atendenteOrGerSeccionais', auth()->user()) ? auth()->user()->idregional : null;

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
            ->orderBy('hora')
            ->paginate(10);

        return $count ? $resultados->total() : $resultados;
    }

    public function getDiasHorasAjaxSite($dados)
    {
        $regional = $dados['regional'];
        if(isset($regional))
        {
            $resultado = !isset($dados['servico']) || ($dados['servico'] == Agendamento::SERVICOS_PLANTAO_JURIDICO) ? 
            $regional->plantaoJuridico()->with('bloqueios')->first() : $regional;
    
            if(!isset($dados['servico']) && !isset($dados['dia']))
            {
                $plantao = $resultado->qtd_advogados > 0 ? $resultado : null;
        
                if(isset($plantao))
                {
                    $inicial = Carbon::parse($plantao->dataInicial);
                    $final = Carbon::parse($plantao->dataFinal);

                    return [
                        $inicial->gt(Carbon::today()) ? $plantao->dataInicial : null,
                        $final->gt(Carbon::today()) ? $plantao->dataFinal : null
                    ];
                }
        
                return [];
            }
        
            if(isset($dados['dia']))
            {
                $dia = Carbon::createFromFormat('d/m/Y', $dados['dia'])->format('Y-m-d');
                return $resultado->removeHorariosSeLotado($dia);
            }
            
            return $resultado->getDiasSeLotado();
        }

        return null;
    }
}