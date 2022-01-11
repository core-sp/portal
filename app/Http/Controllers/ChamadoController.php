<?php

namespace App\Http\Controllers;

use App\Chamado;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChamadoRequest;
use App\Repositories\ChamadoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class ChamadoController extends Controller
{
    use TabelaAdmin;

    private $chamadoRepository;

    // Variáveis extras de chamado
    public $variaveis = [
        'singular' => 'chamado',
        'singulariza' => 'o chamado',
        'plural' => 'chamados',
        'pluraliza' => 'chamados',
        'titulo_criar' => 'Registrar chamado',
        'btn_lista' => '<a href="/admin/chamados" class="btn btn-primary">Lista de Chamados</a>',
        'btn_lixeira' => '<a href="/admin/chamados/concluidos" class="btn btn-warning">Chamados Concluídos</a>',
        'titulo' => 'Chamados concluídos'
    ];

    public function __construct(ChamadoRepository $chamadoRepository)
    {
        $this->middleware('auth');
        $this->chamadoRepository = $chamadoRepository;
    }

    public function index()
    {
        $this->authorize('onlyAdmin', auth()->user());

        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados, "lista");
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $tipos = Chamado::tipos();
        $prioridades = Chamado::prioridades();

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.criar', compact('variaveis', 'tipos', 'prioridades'));
    }

    public function store(ChamadoRequest $request)
    {
        $save = $this->chamadoRepository->store(request(['tipo', 'prioridade', 'mensagem', 'img', 'idusuario']));
        
        if(!$save) {
            abort(500);
        }
            
        event(new CrudEvent('chamado', 'criou', $save->idchamado));

        return redirect('/admin')
            ->with('message', '<i class="icon fa fa-check"></i>Chamado registrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $resultado = $this->chamadoRepository->getById($id);

        if(!isset($resultado)) {
            abort(401);
        } 
        else {
            if(Auth::id() === $resultado->idusuario) {
                $variaveis = $this->variaveis;

                if(auth()->user()->cannot('onlyAdmin', auth()->user())) {
                    $variaveis['btn_lista'] = '';
                }

                $tipos = Chamado::tipos();
                $prioridades = Chamado::prioridades();
                $variaveis = (object) $variaveis;
                
                return view('admin.crud.editar', compact('resultado', 'variaveis', 'tipos', 'prioridades'));
            } 
            else {
                abort(401);
            }
        }
    }

    public function update(ChamadoRequest $request, $id)
    {
        $update = $this->chamadoRepository->update($id, request(['tipo', 'prioridade', 'mensagem', 'img', 'idusuario']));

        if(!$update) {
            abort(500);
        }
            
        event(new CrudEvent('chamado', 'editou', $id));
        
        return redirect('/admin')
            ->with('message', '<i class="icon fa fa-check"></i>Chamado editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($id)
    {
        $resultado = $this->chamadoRepository->getByIdWithTrashed($id);

        if(!isset($resultado)) {
            abort(401);
        } 
        else {
            if(Auth::id() === $resultado->idusuario || auth()->user()->idperfil === 1) {
                $variaveis = $this->variaveis;

                if(auth()->user()->cannot('onlyAdmin', auth()->user())) {
                    $variaveis['btn_lista'] = '';
                }
                    
                $variaveis = (object) $variaveis;

                return view('admin.crud.mostra', compact('resultado', 'variaveis'));
            } 
            else {
                abort(401);
            }
        }
    }

    public function destroy($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $delete = $this->chamadoRepository->delete($id);

        if(!$delete) {
            abort(500);
        }
            
        event(new CrudEvent('chamado', 'deu baixa', $id));

        return redirect('/admin/chamados')
            ->with('message', '<i class="icon fa fa-check"></i>Chamado concluído com sucesso!')
            ->with('class', 'alert-success');
    }

    public function lixeira()
    {
        $this->authorize('onlyAdmin', auth()->user());

        $resultados = $this->chamadoRepository->getAllTrashedChamados();
        $variaveis = (object) $this->variaveis;
        $tabela = $this->tabelaCompleta($resultados, "lixeira");

        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $restore = $this->chamadoRepository->restore($id);

        if(!$restore) {
            abort(500);
        }
            
        event(new CrudEvent('chamado', 'reabriu', $id));

        return redirect('/admin/chamados')
            ->with('message', '<i class="icon fa fa-check"></i>Chamado reaberto!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->authorize('onlyAdmin', auth()->user());

        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->chamadoRepository->busca($busca);
        $tabela = $this->tabelaCompleta($resultados, "lista");

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function resposta(Request $request, $id)
    {
        $resposta = "<i>(" . date('d\/m\/Y, \à\s H:i') . "):</i> " . $request->input('resposta');
        
        $update = $this->chamadoRepository->updateResposta($id, $resposta);

        if(!$update) {
            abort(500);
        }
            
        event(new CrudEvent('chamado', 'respondeu', $id));

        return redirect('/admin/chamados/ver/'.$id)
            ->with('message', '<i class="icon fa fa-check"></i>Resposta emitida com sucesso!')
            ->with('class', 'alert-success');
    }

    public function resultados()
    {
        $resultados = $this->chamadoRepository->getAllChamados();

        return $resultados;
    }

    public function tabelaCompleta($resultados, $tipoDisplay)
    {
        // Opções de cabeçalho da tabela
        if($tipoDisplay == "lista") {
            $headers = [
                'Código',
                'Tipo / Mensagem',
                'Prioridade',
                'Usuário',
                'Ações'
            ];
        }
        else {
            $headers = [
                'Código',
                'Tipo',
                'Usuário',
                'Concluído em:',
                'Ações'
            ];
        }

        // Opções de conteúdo da tabela
        $contents = [];

        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/chamados/ver/'.$resultado->idchamado.'" class="btn btn-sm btn-default">Ver</a> ';
            
            if($tipoDisplay == "lista") {
                $acoes .= '<form method="POST" action="/admin/chamados/apagar/'.$resultado->idchamado.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-success" value="Dar baixa" onclick="return confirm(\'Tem certeza que deseja dar baixa no chamado?\')" />';
                $acoes .= '</form>';

                if(isset($resultado->resposta)) {
                    $prioridade = $resultado->prioridade."<br><small>(Respondido)</small>";
                }
                else {
                    $prioridade = $resultado->prioridade;
                }

                $conteudo = [
                    $resultado->idchamado,
                    $resultado->tipo.'<br><small>' . resumoTamanho($resultado->mensagem, 75) . '</small>',
                    $prioridade,
                    $resultado->user->nome,
                    $acoes
                ];
            }
            else {
                $acoes = '<a href="/admin/chamados/ver/'.$resultado->idchamado.'" class="btn btn-sm btn-default">Ver</a> ';
                $acoes .= '<a href="/admin/chamados/restore/'.$resultado->idchamado.'" class="btn btn-sm btn-primary">Reabrir</a>';
                $conteudo = [
                    $resultado->idchamado,
                    $resultado->tipo,
                    $resultado->user->nome,
                    formataData($resultado->deleted_at),
                    $acoes
                ];
            }
                 
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $tabela = $this->montaTabela($headers, $contents, $classes);

        return $tabela;
    }
}