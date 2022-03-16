<?php

namespace App\Http\Controllers;

// use App\User;
// use App\Regional;
// use App\Agendamento;
// use App\Events\CrudEvent;
// use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
// use App\Mail\AgendamentoMailGuest;
// use App\Repositories\UserRepository;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Http\RedirectResponse;
// use App\Repositories\AgendamentoRepository;
use App\Http\Requests\AgendamentoUpdateRequest;
use App\Contracts\MediadorServiceInterface;
// use Illuminate\Support\Facades\Request as IlluminateRequest;

class AgendamentoController extends Controller
{
    // use TabelaAdmin;

    // // Nome da classe
    // private $class = 'AgendamentoController';
    // private $agendamentoRepository;
    private $service;
    // private $userRepository;

    // // Variáveis para páginas no Admin
    // private $agendamentoVariaveis = [
    //     'singular' => 'agendamento',
    //     'singulariza' => 'o agendamento',
    //     'plural' => 'agendamentos',
    //     'pluraliza' => 'agendamentos'
    // ];

    public function __construct(/*AgendamentoRepository $agendamentoRepository, */MediadorServiceInterface $service/*, UserRepository $userRepository*/)
    {
        $this->middleware('auth');
        // $this->agendamentoRepository = $agendamentoRepository;
        $this->service = $service;
        // $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->listar($request, $this->service);
            $temFiltro = $dados['temFiltro'];
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os agendamentos.");
        }

        // $variaveis = $this->agendamentoVariaveis;

        // // Checa se tem filtro
        // if(IlluminateRequest::input('filtro') === 'sim') {
        //     $temFiltro = true;

        //     $variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';

        //     $resultados = $this->checaAplicaFiltros();

        //     if($resultados instanceof RedirectResponse) {
        //         return $resultados;
        //     }
        // } 
        // else {
        //     $temFiltro = null;
        //     $diaFormatado = date('d\/m\/Y');
        //     $regional = $this->service->getService('Regional')->getById(Auth::user()->idregional);
        //     $variaveis['continuacao_titulo'] = 'em <strong>' . $regional->regional . ' - ' . $diaFormatado . '</strong>';

        //     $resultados = $this->agendamentoRepository->getToTable($regional->idregional);
        // }
        // // Monta tabela com resultados
        // $tabela = $this->tabelaCompleta($resultados);
        // $variaveis['filtro'] = $this->montaFiltros();
        // $variaveis['mostraFiltros'] = true;
        // $variaveis = (object) $variaveis;

        return !isset($dados['erro']['message']) ? 
            view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro')) : 
            redirect(session('url'))->with($dados['erro']);
    }

    public function updateStatus(AgendamentoUpdateRequest $request)
    {
        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Agendamento')->save($validated);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao atualizar o status do agendamento.");
        }

        // $idusuario = Auth::user()->idusuario;
        // $idagendamento = $request->idagendamento;
        // $status = $request->status;

        // $agendamento = $this->agendamentoRepository->getById($idagendamento);

        // // Checa se o usuário pode editar apenas agendamentos de sua regional. Caso tente  editar agendamento fora
        // // de sua regional, aborta com erro de permissão.
        // if($this->limitaPorRegional()) {
        //     if($agendamento->idregional != Auth::user()->idregional) {
        //         abort(403);
        //     }
        // }

        // if($agendamento) {
        //     if($agendamento->dia > date('Y-m-d')) {
        //         return redirect()->back()
        //             ->with('message', '<i class="icon fa fa-ban"></i>Status do agendamento não pode ser modificado antes da data agendada')
        //             ->with('class', 'alert-danger');
        //     }
        // }

        // $update = $this->agendamentoRepository->update($idagendamento, ['status' => $status, 'idusuario' => $idusuario], $agendamento);

        // if(!$update) {
        //     abort(500);
        // }

        // if($status === Agendamento::STATUS_COMPARECEU) {
        //     event(new CrudEvent('agendamento', 'confirmou presença', $idagendamento));
        // } 
        // else {
        //     event(new CrudEvent('agendamento', 'confirmou falta', $idagendamento));
        // }
        
        $id = $validated['idagendamento'];

        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : 
                '<i class="icon fa fa-check"></i>Status do agendamento com ID '.$id.' foi editado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Agendamento')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao buscar o texto em agendamentos.");
        }

        // $busca = IlluminateRequest::input('q');
    
        // // "Atendente" e "Gerente Seccionais" devem visualizar apenas agendamentos de sua respectiva regional.
        // if(!$this->limitaPorRegional()) {
        //     $resultados = $this->agendamentoRepository->getToBusca($busca);
        // }
        // else {
        //     $resultados = $this->agendamentoRepository->getToBuscaByRegional($busca, Auth::user()->idregional);
        // }
        
        // $tabela = $this->tabelaCompleta($resultados);
        // $variaveis = (object) $this->agendamentoVariaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('Agendamento')->view($id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
            $atendentes = $dados['atendentes'];
            $servicos = $dados['servicos'];
            $status = $dados['status'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            method_exists($e, 'getStatusCode') ? abort($e->getStatusCode()) : 
            abort(500, "Erro ao carregar o agendamento.");
        }

        // $resultado = $this->agendamentoRepository->getById($id);

        // // Checa se o usuário pode editar apenas agendamentos de sua regional. Caso tente  editar agendamento fora
        // // de sua regional, aborta com erro de permissão.
        // if($this->limitaPorRegional()) {
        //     if($resultado->idregional != Auth::user()->idregional) {
        //         abort(403);
        //     }
        // }

        // $atendentes = $this->userRepository->getAtendentesByRegional($resultado->idregional);

        // $servicos = Agendamento::servicosCompletos();
        // $status = Agendamento::status();
        // $variaveis = $this->agendamentoVariaveis;
        // $variaveis['mensagem_agendamento'] = $this->mensagemAgendamento($resultado->dia, $resultado->hora, $resultado->status, $resultado->protocolo, $id);
        // $variaveis['cancela_idusuario'] = true;
        // $variaveis = (object) $variaveis;

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'atendentes', 'servicos', 'status'));
    }

    public function update(AgendamentoUpdateRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Agendamento')->save($validated, $id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            method_exists($e, 'getStatusCode') ? abort($e->getStatusCode()) : 
            abort(500, "Erro ao atualizar o agendamento.");
        }

        // // Checa se o usuário pode editar apenas agendamentos de sua regional. Caso tente  editar agendamento fora
        // // de sua regional, aborta com erro de permissão.
        // // Neste caso, é usado o nome da regional ao invés do ID.
        // if($this->limitaPorRegional()) {
        //     if($request->idregional != Auth::user()->regional->regional) {
        //         abort(403);
        //     }
        // }

        // $update = $this->agendamentoRepository->update($id, $request->toModel());

        // if(!$update) {
        //     abort(500);
        // }

        // event(new CrudEvent('agendamento', 'editou', $id));

        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : '<i class="icon fa fa-check"></i>Agendamento com a ID '.$id.' foi editado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function reenviarEmail($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $this->service->getService('Agendamento')->enviarEmail($id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            method_exists($e, 'getStatusCode') ? abort($e->getStatusCode()) : 
            abort(500, "Erro ao reenviar email do agendamento.");
        }

        // $agendamento = $this->agendamentoRepository->getById($id);
       
        // // Reenvia o email
        // Mail::to($agendamento->email)->send(new AgendamentoMailGuest($agendamento));
        
        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => '<i class="icon fa fa-check"></i>Email do agendamento com ID '.$id.' foi enviado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function pendentes()
    {
        $this->authorize('viewAny', auth()->user());
        $this->authorize('viewPendentes', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->listar();
            $resultados = $dados['resultados'];
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os agendamentos pendentes.");
        }

        // $idPerfil = Auth::user()->perfil->idperfil;

        // // "Coordenadoria de Atendimento" e "Admin" podem ver todos os agendamentos pendentes
        // if($idPerfil === 6 || $idPerfil === 1) {
        //     $resultados = $this->agendamentoRepository->getAllPastAgendamentoPendente();
        // } 
        // // "Gestão de Atendimento - Sede" pode ver apenas agendamentos pendentes da Sede (São Paulo, id=1)
        // elseif($idPerfil === 12 ) {
        //     $resultados = $this->agendamentoRepository->getAllPastAgendamentoPendenteSede();
        // }
        // // "Gestão de Atendimento - Seccionais" pode ver apenas agendamentos pendentes das seccionais (id!=1)
        // elseif($idPerfil === 13) {
        //     $resultados = $this->agendamentoRepository->getAllPastAgendamentoPendenteSeccionais();
        // } 
        // // "Atendente" e "Gerente Seccionais" podem ver apenas agendamentos pendentes da sua regional
        // elseif($idPerfil === 8 || $idPerfil === 21) {
        //     $resultados = $this->agendamentoRepository->getPastAgendamentoPendenteByRegional(Auth::user()->idregional);
        // } 
        // else {
        //     abort(401);
        // }

        // if($resultados->isEmpty()) {
        //     $resultados = [];
        // }

        // $tabela = $this->tabelaCompleta($resultados);
        // $variaveis = $this->agendamentoVariaveis;
        // $variaveis['continuacao_titulo'] = 'pendentes de validação';
        // $variaveis['plural'] = 'agendamentos pendentes';
        // $variaveis['btn_criar'] = '<a class="btn btn-primary" href="/admin/agendamentos">Lista de Agendamentos</a>';
        // $variaveis = (object) $variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    // public function checaAplicaFiltros()
    // {
    //     $this->authorize('viewAny', auth()->user());

    //     // Valores default dos filtros
    //     $mindia = date('Y-m-d');
    //     $maxdia = date('Y-m-d');
    //     $regional = '';
    //     $status = '';
    //     $servico = '';

    //     // Valida e prepara filtro de data mínima
    //     if(IlluminateRequest::has('mindia')) {
    //         if(!empty(IlluminateRequest::input('mindia'))) {
    //             $mindiaArray = explode('/', IlluminateRequest::input('mindia'));
    //             $checaMindia = (count($mindiaArray) != 3 || $mindiaArray[2] == null)  ? false : checkdate($mindiaArray[1], $mindiaArray[0], $mindiaArray[2]);

    //             if($checaMindia === false) {
    //                 return redirect()->back()->with('message', '<i class="icon fa fa-ban"></i>Data de início do filtro inválida')
    //                     ->with('class', 'alert-danger');
    //             }

    //             $mindia = date('Y-m-d', strtotime(str_replace('/', '-', IlluminateRequest::input('mindia'))));
    //         }
    //     } 

    //     // Valida e prepara filtro de data máxima
    //     if(IlluminateRequest::has('maxdia')) {
    //         if(!empty(IlluminateRequest::input('maxdia'))) {
    //             $maxdiaArray = explode('/', IlluminateRequest::input('maxdia'));
    //             $checaMaxdia = (count($maxdiaArray) != 3 || $maxdiaArray[2] == null)  ? false : checkdate($maxdiaArray[1], $maxdiaArray[0], $maxdiaArray[2]);

    //             if($checaMaxdia === false) {
    //                 return redirect()->back()->with('message', '<i class="icon fa fa-ban"></i>Data de término do filtro inválida')
    //                     ->with('class', 'alert-danger');
    //             }

    //             $maxdia = date('Y-m-d', strtotime(str_replace('/', '-', IlluminateRequest::input('maxdia'))));
    //         }         
    //     } 

    //     // Valida e prepara filtro de regional
    //     if(IlluminateRequest::has('regional')) {
    //         if(!empty(IlluminateRequest::input('regional'))) {
    //             $regional = IlluminateRequest::input('regional');
    //         }
    //     }
    //     else {
    //         $regional = Auth::user()->idregional;
    //     }

    //     // Valida e prepara filtro de status
    //     if(IlluminateRequest::has('status')) {
    //         if(!empty(IlluminateRequest::input('status')) && IlluminateRequest::input('status') !== 'Qualquer') {
    //             $status = IlluminateRequest::input('status');
    //         }
    //     } 

    //     // Valida e prepara filtro de serviço
    //     if(IlluminateRequest::has('servico')) {
    //         if(!empty(IlluminateRequest::input('servico')) && IlluminateRequest::input('servico') !== 'Qualquer') {
    //             $servico = IlluminateRequest::input('servico');
    //         }
    //     } 

    //     return $this->agendamentoRepository->getToTableFilter($mindia, $maxdia, $regional, $status, $servico);
    // }

    // public function montaFiltros()
    // {
    //     $regionais = $this->service->getService('Regional')->getToList();

    //     $filtro = '<form method="GET" action="/admin/agendamentos/filtro" id="filtroAgendamento" class="mb-0">';
    //     $filtro .= '<div class="form-row filtroAge">';
    //     $filtro .= '<input type="hidden" name="filtro" value="sim" />';

    //     // Montando filtro de regional. "Atendente" e "Gerente Seccionais" não podem usar este filtro.
    //     if(!$this->limitaPorRegional()) {
    //         $filtro .= '<div class="form-group mb-0 col">';
    //         $filtro .= '<label>Seccional</label>';
    //         $filtro .= '<select class="custom-select custom-select-sm mr-2" id="regional" name="regional">';
            
    //         if(IlluminateRequest::input('regional') === '') {
    //             $select .= '<option value="" selected>Todas</option>';
    //         } 
    //         else {
    //             $filtro .= '<option value="">Todas</option>';
    //         }

    //         foreach($regionais as $regional) {
    //             if(IlluminateRequest::has('regional')) {
    //                 if($regional->idregional == IlluminateRequest::input('regional')) {
    //                     $filtro .= '<option value="' . $regional->idregional . '" selected>' . $regional->regional . '</option>';
    //                 } 
    //                 else {
    //                     $filtro .= '<option value="' . $regional->idregional . '">' . $regional->regional . '</option>';
    //                 }
    //             } 
    //             else {
    //                 $filtro .= '<option value="' . $regional->idregional . '">' . $regional->regional . '</option>';
    //             }
    //         }

    //         $filtro .= '</select>';
    //         $filtro .= '</div>';
    //     }

    //     $filtro .= '<div class="form-group mb-0 col">';
    //     $filtro .= '<label>Status</label>';
    //     $filtro .= '<select class="custom-select custom-select-sm" name="status">';
        
    //     // Montando filtro de status
    //     if(IlluminateRequest::input('status') === 'Qualquer') {
    //         $filtro .= '<option value="Qualquer" selected>Qualquer</option>';
    //     }
           
    //     else {
    //         $filtro .= '<option value="Qualquer">Qualquer</option>';
    //     }
        
    //     $status = Agendamento::status();

    //     foreach($status as $s) {
    //         if(IlluminateRequest::has('status')) {
    //             if(IlluminateRequest::input('status') === $s) {
    //                 $filtro .= '<option value="' . $s . '" selected>' . $s . '</option>';
    //             } else {
    //                 $filtro .= '<option value="' . $s . '">' . $s . '</option>';
    //             }
    //         } else {
    //             $filtro .= '<option value="' . $s . '">' . $s . '</option>';
    //         }
    //     }

    //     $filtro .= '</select>';
    //     $filtro .= '</div>';

    //     // Montando filtro de serviço
    //     $filtro .= '<div class="form-group mb-0 col">';
    //     $filtro .= '<label>Serviço</label>';
    //     $filtro .= '<select class="custom-select custom-select-sm" name="servico">';
        
    //     if(IlluminateRequest::input('servico') === 'Qualquer') {
    //         $filtro .= '<option value="Qualquer" selected>Qualquer</option>';
    //     }
           
    //     else {
    //         $filtro .= '<option value="Qualquer">Qualquer</option>';
    //     }
        
    //     $servicos = Agendamento::servicosCompletos();

    //     foreach($servicos as $s) {
    //         if(IlluminateRequest::has('servico')) {
    //             if(IlluminateRequest::input('servico') === $s) {
    //                 $filtro .= '<option value="' . $s . '" selected>' . $s . '</option>';
    //             } else {
    //                 $filtro .= '<option value="' . $s . '">' . $s . '</option>';
    //             }
    //         } else {
    //             $filtro .= '<option value="' . $s . '">' . $s . '</option>';
    //         }
    //     }

    //     $filtro .= '</select>';
    //     $filtro .= '</div>';

    //     $filtro .= '<div class="form-group mb-0 col">';

    //     $hoje = date('d\/m\/Y');

    //     $filtro .= '<label>De</label>';
       
    //     // Montando filtro de data mínima
    //     if(IlluminateRequest::has('mindia')) {
    //         $mindia = IlluminateRequest::input('mindia');
    //         $filtro .= '<input type="text" class="form-control d-inline-block dataInput form-control-sm" name="mindia" id="mindiaFiltro" placeholder="dd/mm/aaaa" value="' . $mindia . '" />';
    //     } 
    //     else {
    //         $filtro .= '<input type="test" class="form-control d-inline-block dataInput form-control-sm" name="mindia" id="mindiaFiltro" placeholder="dd/mm/aaaa" value="' . $hoje . '" />';
    //     }

    //     $filtro .= '</div>';
    //     $filtro .= '<div class="form-group mb-0 col">';
    //     $filtro .= '<label>Até</label>';
        
    //     // Montando filtro de data máxima
    //     if(IlluminateRequest::has('maxdia')) {
    //         $maxdia = IlluminateRequest::input('maxdia');
    //         $filtro .= '<input type="text" class="form-control d-inline-block dataInput form-control-sm" name="maxdia" id="maxdiaFiltro" placeholder="dd/mm/aaaa" value="' . $maxdia . '" />';
    //     } 
    //     else {
    //         $filtro .= '<input type="test" class="form-control d-inline-block dataInput form-control-sm" name="maxdia" id="maxdiaFiltro" placeholder="dd/mm/aaaa" value="' . $hoje . '" />';
    //     }

    //     $filtro .= '</div>';
    //     $filtro .= '<div class="form-group mb-0 col-auto align-self-end">';
    //     $filtro .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
    //     $filtro .= '</div>';
    //     $filtro .= '</div>';
    //     $filtro .= '</form>';

    //     return $filtro;
    // }

    // public function mensagemAgendamento($dia, $hora, $status, $protocolo, $id)
    // {
    //     if(date('Y-m-d') >= $dia) {
    //         if($status === Agendamento::STATUS_CANCELADO) {
    //             $mensagem =  "<p class='mb-0 text-muted'><strong><i class='fas fa-ban'></i>&nbsp;&nbsp;Atendimento cancelado</strong></p>";
    //         } 
    //         elseif($status === Agendamento::STATUS_NAO_COMPARECEU) {
    //             $mensagem = "<p class='mb-0 text-warning'><strong><i class='fas fa-user-alt-slash'></i>&nbsp;&nbsp;Não compareceu</strong></p>";
    //         } 
    //         elseif($status === Agendamento::STATUS_COMPARECEU) {
    //             $mensagem = "<p class='mb-0 text-success'><strong><i class='fas fa-check-circle'></i>&nbsp;&nbsp;Atendimento realizado com sucesso no dia " . onlyDate($dia) . ", às " . $hora . "</strong></p>";
    //         } 
    //         else {
    //             $mensagem = "<p class='mb-0 text-danger'><strong><i class='fas fa-exclamation-triangle'></i>&nbsp;&nbsp;Validação pendente</strong></p>";
    //         }
    //     } 
    //     else {
    //         if($status === Agendamento::STATUS_CANCELADO) {
    //             $mensagem = "<p class='mb-0 text-muted'><strong><i class='fas fa-ban'></i> Atendimento cancelado</strong></p>";
    //         } 
    //         else {
    //             // Botão de reenviar email
    //             $mensagem = '<form method="POST" action="/admin/agendamentos/reenviar-email/' . $id . '" class="d-inline">';
    //             $mensagem .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    //             $mensagem .= '<input type="submit" class="btn btn-sm btn-default" value="Reenviar email de confirmação"></input>';
    //             $mensagem .= '</form>';
    //         }
    //     }

    //     return $mensagem;
    // }

    // /**
    //  * Método usado para checar se o perfil do usuário exige limitação de visualização de agendamentos
    //  * de sua própria regional. Retorna true se for necessário limitar, do contrário, retorna false.
    //  * 
    //  * Perfis limitados por regional são "Atendente" (8) e "Gerente Seccionais" (21).
    //  */
    // protected function limitaPorRegional() 
    // {
    //     return Auth::user()->perfil->idperfil == 8 || Auth::user()->perfil->idperfil == 21;
    // }

    // public function status($status, $id, $usuario = null)
    // {
    //     // Caso o usário seja do perfil "Atendente" (id=8) ele poderá apenas filtrar com sua respectiva regional
    //     if(IlluminateRequest::has('regional') && Auth::user()->perfil->idperfil === 8) {
    //         if(IlluminateRequest::input('regional') !== Auth::user()->idregional) {
    //             abort(401);
    //         }
    //     }
    //     switch ($status) {
    //         case Agendamento::STATUS_CANCELADO:
    //             $btn = "<strong>" . Agendamento::STATUS_CANCELADO . "</strong>";
    //             if(auth()->user()->can('updateOther', auth()->user())) {
    //                 $btn .= "&nbsp;&nbsp;<a href='/admin/agendamentos/editar/" . $id . "' class='btn btn-sm btn-default'>Editar</a>";
    //             }
                    
    //             return $btn;
    //         break;

    //         case Agendamento::STATUS_COMPARECEU:
    //             $string = "<p class='d-inline'><i class='fas fa-check checkIcone'></i>&nbsp;&nbsp;" . Agendamento::STATUS_COMPARECEU . "&nbsp;&nbsp;</p>";
    //             if(auth()->user()->can('updateOther', auth()->user())) {
    //                 $string .= "<a href='/admin/agendamentos/editar/" . $id . "' class='btn btn-sm btn-default'>Editar</a>";
    //             }
    //             if(isset($usuario)) {
    //                 $string .= "<small class='d-block'>Atendido por: <strong>" . $usuario . "</strong></small>";
    //             }

    //             return $string;
    //         break;

    //         case Agendamento::STATUS_NAO_COMPARECEU:
    //             $btn = "<strong>" . Agendamento::STATUS_NAO_COMPARECEU . "</strong>";
    //             if(auth()->user()->can('updateOther', auth()->user())) {
    //                 $btn .= "&nbsp;&nbsp;<a href='/admin/agendamentos/editar/" . $id . "' class='btn btn-sm btn-default'>Editar</a>";
    //             }

    //             return $btn;
    //         break;

    //         default:
    //             $acoes = '<form method="POST" id="statusAgendamento" action="/admin/agendamentos/status" class="d-inline">';
    //             $acoes .= '<input type="hidden" name="_token" id="tokenStatusAgendamento" value="' . csrf_token() . '" />';
    //             $acoes .= '<input type="hidden" name="_method" value="PUT" id="method" />';
    //             $acoes .= '<input type="hidden" name="idagendamento" value="' . $id . '" />';
    //             $acoes .= '<button type="submit" name="status" id="btnSubmit" class="btn btn-sm btn-primary" value="' . Agendamento::STATUS_COMPARECEU . '">Confirmar</button>';
    //             $acoes .= '<button type="submit" name="status" id="btnSubmit" class="btn btn-sm btn-danger ml-1" value="' . Agendamento::STATUS_NAO_COMPARECEU . '">' . Agendamento::STATUS_NAO_COMPARECEU . '</button>';
    //             $acoes .= '</form>';

    //             if(auth()->user()->can('updateOther', auth()->user())) {
    //                 $acoes .= " <a href='/admin/agendamentos/editar/" . $id . "' class='btn btn-sm btn-default'>Editar</a>";
    //             }

    //             return $acoes;
    //         break;
    //     }
    // }

    // public function tabelaCompleta($resultados)
    // {
    //     // Opções de cabeçalho da tabela
    //     $headers = [
    //         'Protocolo',
    //         'Nome/CPF',
    //         'Horário/Dia',
    //         'Serviço',
    //         'Status'
    //     ];
    //     // Opções de conteúdo da tabela
    //     $contents = [];
    //     foreach($resultados as $resultado) {
    //         // Ações possíveis com cada resultado
    //         if(isset($resultado->user->nome)) {
    //             $nomeusuario = $resultado->user->nome;
    //         }  
    //         else {
    //             $nomeusuario = null;
    //         }
                
    //         $acoes = $this->status($resultado->status, $resultado->idagendamento, $nomeusuario);
    //         // Mostra dados na tabela
    //         $conteudo = [
    //             $resultado->protocolo.'<br><small>Código: ' . $resultado->idagendamento . '</small>',
    //             $resultado->nome . '<br>' . $resultado->cpf,
    //             $resultado->hora . '<br><small><strong>' . onlyDate($resultado->dia) . '</strong></small>',
    //             $resultado->tiposervico . '<br><small>(' . $resultado->regional->regional . ')',
    //             $acoes
    //         ];
    //         array_push($contents, $conteudo);
    //     }
    //     // Classes da tabela
    //     $classes = [
    //         'table',
    //         'table-bordered',
    //         'table-striped'
    //     ];
    //     $tabela = $this->montaTabela($headers, $contents, $classes);
        
    //     return $tabela;
    // }
}