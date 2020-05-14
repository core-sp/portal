<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pagina;
use Illuminate\Support\Str;
use App\Events\CrudEvent;
use App\Repositories\PaginaRepository;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PaginaController extends Controller
{
    use ControleAcesso;

    // Nome da classe
    private $class = 'PaginaController';
    private $paginaModel;
    private $variaveis;
    private $paginaRepository;

    public function __construct(Pagina $pagina, PaginaRepository $paginaRepository)
    {
        $this->middleware('auth', ['except' => ['show']]);
        $this->paginaModel = $pagina;
        $this->variaveis = $pagina->variaveis();
        $this->paginaRepository = $paginaRepository;
    }

    protected function regras()
    {
        return [
            'titulo' => 'required|max:191',
            'subtitulo' => 'max:191',
            'img' => 'max:191',
            'conteudo' => 'required'
        ];
    }

    protected function mensagens()
    {
        return [
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultados = $this->paginaRepository->getToTable();
        $tabela = $this->paginaModel->tabelaCompleta($resultados);
        if(!$this->mostra($this->class, 'create'))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        $this->autoriza($this->class, 'create');
        $request->validate($this->regras(), $this->mensagens());

        $slug = Str::slug($request->input('titulo'), '-');

        $countTitulo = $this->paginaRepository->countBySlug($slug);

        if($countTitulo >= 1) {
            return redirect(route('paginas.index'))
                ->with('message', '<i class="icon fa fa-ban"></i>Não foi possível criar a página. Já existe uma página com esse nome.')
                ->with('class', 'alert-danger');
        }

        $save = Pagina::create([
            'titulo' => request('titulo'),
            'subtitulo' => request('subtitulo'),
            'slug' => $slug,
            'img' => request('img'),
            'conteudo' => request('conteudo'),
            'idusuario' => request('idusuario')
        ]);
        
        if(!$save)
            abort(500);
        event(new CrudEvent('página', 'criou', $save->idpagina));
        return redirect(route('paginas.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Página criada com sucesso!')
            ->with('class', 'alert-success');
    }    

    public function edit($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultado = $this->paginaRepository->findById($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        $this->autoriza($this->class, 'edit');
        $erros = $request->validate($this->regras(), $this->mensagens());
        
        $slug = Str::slug($request->input('titulo'), '-');

        $update = Pagina::findOrFail($id)->update([
            'titulo' => request('titulo'),
            'subtitulo' => request('subtitulo'),
            'slug' => $slug,
            'img' => request('img'),
            'conteudo' => request('conteudo'),
            'idusuario' => request('idusuario')
        ]);

        if(!$update)
            abort(500);
        event(new CrudEvent('página', 'editou', $id));
        return redirect('/admin/paginas')
            ->with('message', '<i class="icon fa fa-check"></i>Página editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $pagina = $this->paginaRepository->findById($id);
        $delete = $pagina->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('página', 'apagou', $pagina->idpagina));
        return redirect('/admin/paginas')
            ->with('message', '<i class="icon fa fa-ban"></i>Página deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function lixeira()
    {
        $this->autorizaStatic(['1']);
        $resultados = $this->paginaRepository->getTrashed();
        $variaveis = (object) $this->variaveis;
        $tabela = $this->paginaModel->tabelaTrashed($resultados);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->autorizaStatic(['1']);
        $restore = $this->paginaRepository->getTrashedById($id)->restore();
        if(!$restore)
            abort(500);
        event(new CrudEvent('página', 'restaurou', $id));
        return redirect('/admin/paginas')
            ->with('message', '<i class="icon fa fa-check"></i>Página restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->paginaRepository->getBusca($busca);
        $tabela = $this->paginaModel->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'variaveis', 'tabela', 'busca'));
    }

}
