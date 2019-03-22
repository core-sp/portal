<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Pagina;
use App\User;
use App\PaginaCategoria;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;

class PaginaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'showSemCategoria']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $paginas = Pagina::paginate(10);
        return view('admin.paginas.home', compact('paginas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $categorias = PaginaCategoria::all();
        return view('admin.paginas.criar', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regras = [
            'titulo' => 'required',
            'conteudo' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);

        $pagina = new Pagina();
        $pagina->titulo = $request->input('titulo');
        $pagina->slug = Str::slug($request->input('titulo'), '-');
        $pagina->conteudo = $request->input('conteudo');
        $pagina->idcategoria = $request->input('categoria');
        $pagina->idusuario = $request->input('idusuario');
        $pagina->save();
        return redirect('/admin/paginas');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($categoria, $slug)
    {
        $pagina = Pagina::where('slug', $slug)->first();
        $slug = Str::slug($pagina->paginacategoria->nome, '-');
        if ($categoria == $slug) {
            return view('site.pagina', compact('pagina', 'categoria'));
        } else {
            abort(404);
        }   
    }

    public function showSemCategoria($slug)
    {
        $pagina = Pagina::where('slug', $slug)->first();
        if (!isset($pagina->paginacategoria->nome)) {
            return view('site.pagina', compact('pagina'));
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $pagina = Pagina::find($id);
        $categorias = PaginaCategoria::all();
        return view('admin.paginas.editar', compact('pagina', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regras = [
            'titulo' => 'required',
            'conteudo' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);
        $pagina = Pagina::find($id);
        $pagina->titulo = $request->input('titulo');
        $pagina->slug = Str::slug($request->input('titulo'), '-');
        $pagina->conteudo = $request->input('conteudo');
        $pagina->idcategoria = $request->input('categoria');
        $pagina->idusuario = $request->input('idusuario');
        $pagina->update();
        return redirect('/admin/paginas');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $pagina = Pagina::find($id);
        $pagina->delete();
        return redirect('/admin/paginas');
    }

    /**
     * Mostra a lixeira de páginas
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $paginas = Pagina::onlyTrashed()->paginate(10);
        return view('/admin/paginas/lixeira', compact('paginas'));
    }

    /**
     * Restaura página deletada
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $pagina = Pagina::onlyTrashed()->find($id);
        $pagina->restore();
        return redirect('/admin/paginas');
    }

    public function busca(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $busca = Input::get('q');
        $paginas = Pagina::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($paginas) > 0) 
            return view('admin.paginas.home', compact('paginas', 'busca'));
        else
            return view('admin.paginas.home')->withMessage('Nenhuma página encontrada');
    }

}
