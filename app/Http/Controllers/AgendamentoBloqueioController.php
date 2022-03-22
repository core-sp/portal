<?php

namespace App\Http\Controllers;

// use DateTime;
// use App\Regional;
// use App\Events\CrudEvent;
// use App\Traits\TabelaAdmin;
// use App\AgendamentoBloqueio;
use Illuminate\Http\Request;
use App\Http\Requests\AgendamentoBloqueioRequest;
// use App\Repositories\AgendamentoBloqueioRepository;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class AgendamentoBloqueioController extends Controller
{
    // use TabelaAdmin;

    // private $class = 'AgendamentoBloqueioController';
    private $service;
    // private $agendamentoBloqueioRepository;

    // Variáveis extras da página
    // public $variaveis = [
    //     'singular' => 'bloqueio',
    //     'singulariza' => 'o bloqueio',
    //     'plural' => 'bloqueios de agendamento',
    //     'pluraliza' => 'bloqueios',
    //     'form' => 'agendamentobloqueio',
    //     'cancelar' => 'agendamentos/bloqueios',
    //     'titulo_criar' => 'Cadastrar novo bloqueio',
    //     'btn_criar' => '<a href="/admin/agendamentos/bloqueios/criar" class="btn btn-primary mr-1">Novo Bloqueio</a>',
    //     'busca' => 'agendamentos/bloqueios',
    // ];

    public function __construct(MediadorServiceInterface $service/*, AgendamentoBloqueioRepository $agendamentoBloqueioRepository*/)
    {
        $this->middleware('auth');
        $this->service = $service;
        // $this->agendamentoBloqueioRepository = $agendamentoBloqueioRepository;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->listarBloqueio();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os bloqueios dos agendamentos.");
        }

        // $resultados = $this->resultados();
        // $tabela = $this->tabelaCompleta($resultados);

        // if(auth()->user()->cannot('create', auth()->user())) {
        //     unset($this->variaveis['btn_criar']);
        // }
            
        // $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->viewBloqueio(null, $this->service);
            $variaveis = $dados['variaveis'];
            $regionais = $dados['regionais'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para criar o bloqueio do agendamento.");
        }

        // $variaveis = (object) $this->variaveis;
        // $regionais = $this->service->getService('Regional')->getRegionaisAgendamento();

        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }

    public function store(AgendamentoBloqueioRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('Agendamento')->saveBloqueio($validated);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao criar o bloqueio do agendamento.");
        }

        // $diainicio = empty($request->input('diainicio')) ? new DateTime("2000-01-01") : retornaDate($request->input("diainicio"));
        // $diatermino = empty($request->input('diatermino')) ? new DateTime("2100-01-01") : retornaDate($request->input("diatermino"));

        // $request->merge(["diainicio" => $diainicio, "diatermino" => $diatermino]);

        // $save = $this->agendamentoBloqueioRepository->store($request->all());

        // if(!$save) {
        //     abort(500);
        // }
            
        // event(new CrudEvent('bloqueio de agendamento', 'criou', $save->idagendamentobloqueio));

        return redirect(route('agendamentobloqueios.lista'))->with([
            'message' => '<i class="icon fa fa-check"></i>Bloqueio cadastrado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->viewBloqueio($id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para atualizar o bloqueio do agendamento.");
        }

        // $resultado = $this->agendamentoBloqueioRepository->getById($id);
        // $variaveis = (object) $this->variaveis;
        // $regionais = $this->service->getService('Regional')->getRegionaisAgendamento();

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(AgendamentoBloqueioRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('Agendamento')->saveBloqueio($validated, $id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao atualizar o bloqueio do agendamento.");
        }

        // $diainicio = empty($request->input('diainicio')) ? new DateTime("2000-01-01") : retornaDate($request->input("diainicio"));
        // $diatermino = empty($request->input('diatermino')) ? new DateTime("2100-01-01") : retornaDate($request->input("diatermino"));

        // $request->merge(["diainicio" => $diainicio, "diatermino" => $diatermino]);

        // $update = $this->agendamentoBloqueioRepository->update($id, $request->all());

        // if(!$update) {
        //     abort(500);
        // }
            
        // event(new CrudEvent('bloqueio de agendamento', 'editou', $id));

        return redirect(route('agendamentobloqueios.lista'))->with([
            'message' => '<i class="icon fa fa-check"></i>Bloqueio com a ID: '.$id.' foi editado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());

        try{
            $this->service->getService('Agendamento')->delete($id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao excluir o bloqueio do agendamento.");
        }

        // $delete = $this->agendamentoBloqueioRepository->delete($id);

        // if(!$delete) {
        //     abort(500);
        // }
            
        // event(new CrudEvent('bloqueio de agendamento', 'cancelou', $id));

        return redirect(route('agendamentobloqueios.lista'))->with([
            'message' => '<i class="icon fa fa-check"></i>Bloqueio cancelado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Agendamento')->buscarBloqueio($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao buscar o texto em bloqueios.");
        }

        // $this->variaveis['slug'] = 'agendamentos/bloqueios';
        // $variaveis = (object) $this->variaveis;
        // $busca = IlluminateRequest::input('q');

        // $resultados = $this->agendamentoBloqueioRepository->getBusca($busca);
        
        // $tabela = $this->tabelaCompleta($resultados);

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    // public function resultados()
    // {
    //     $resultados = $this->agendamentoBloqueioRepository->getAll();

    //     return $resultados;
    // }

    // public function tabelaCompleta($resultados)
    // {
    //     // Opções de cabeçalho da tabela
    //     $headers = [
    //         'Código',
    //         'Regional',
    //         'Duração',
    //         'Horas Bloqueadas',
    //         'Ações',
    //     ];
    //     // Opções de conteúdo da tabela
    //     $contents = [];
    //     foreach($resultados as $resultado) {
    //         if($resultado->diainicio == '2000-01-01') {
    //             $duracao = 'Início: Indefinido<br />';
    //         } else {
    //             $duracao = 'Início: ' . onlyDate($resultado->diainicio) . '<br />';
    //         }

    //         if($resultado->diatermino == '2100-01-01') {
    //             $duracao .= 'Término: Indefinido';
    //         } else {
    //             $duracao .= 'Término: ' . onlyDate($resultado->diatermino);
    //         }

    //         if(auth()->user()->can('updateOther', auth()->user())) {
    //             $acoes = '<a href="/admin/agendamentos/bloqueios/editar/' . $resultado->idagendamentobloqueio . '" class="btn btn-sm btn-primary">Editar</a> ';
    //         }
                
    //         else {
    //             $acoes = '';
    //         }
               
    //         if(auth()->user()->can('delete', auth()->user())) {
    //             $acoes .= '<form method="POST" action="/admin/agendamentos/bloqueios/apagar/' . $resultado->idagendamentobloqueio . '" class="d-inline-block">';
    //             $acoes .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    //             $acoes .= '<input type="hidden" name="_method" value="delete" />';
    //             $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar" onclick="return confirm(\'Tem certeza que deseja cancelar o bloqueio?\')" />';
    //             $acoes .= '</form>';
    //         }

    //         if(empty($acoes)) {
    //             $acoes = '<i class="fas fa-lock text-muted"></i>';
    //         }
                
    //         $conteudo = [
    //             $resultado->idagendamentobloqueio,
    //             $resultado->regional->regional,
    //             $duracao,
    //             'Das ' . $resultado->horainicio . ' às ' . $resultado->horatermino,
    //             $acoes
    //         ];

    //         array_push($contents, $conteudo);
    //     }
        
    //     // Classes da tabela
    //     $classes = [
    //         'table',
    //         'table-hover'
    //     ];

    //     // Monta e retorna tabela        
    //     $tabela = $this->montaTabela($headers, $contents, $classes);

    //     return $tabela;
    // }

    public function getHorasAjax(Request $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $horarios = $this->service->getService('Regional')->getById($request->idregional)->horariosAge();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao buscar os horarios da regional.");
        }

        return response()->json($horarios);
    }
}
