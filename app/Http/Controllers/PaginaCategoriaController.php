<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PaginaCategoria;

class PaginaCategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $categorias = PaginaCategoria::paginate(10);
        return view('admin.paginas.categorias.home', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        return view('admin.paginas.categorias.criar');
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
            'nome' => 'required'
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório'
        ];
        $request->validate($regras, $mensagens);
        $categoria = new PaginaCategoria();
        $categoria->nome = $request->input('nome');
        $categoria->save();
        return redirect('/admin/paginas/categorias');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $paginas = PaginaCategoria::find($id)->pagina;
        $cat = PaginaCategoria::find($id);
        return view('admin.paginas.categorias.mostra', compact('cat', 'paginas'));
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
        $categoria = PaginaCategoria::find($id);
        return view('admin.paginas.categorias.editar', compact('categoria'));
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
            'nome' => 'required'
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório'
        ];
        $request->validate($regras, $mensagens);
        $categoria = PaginaCategoria::find($id);
        $categoria->nome = $request->input('nome');
        $categoria->update();
        return redirect('/admin/paginas/categorias');
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
        $pagina = PaginaCategoria::find($id);
        $pagina->delete();
        return redirect('/admin/paginas/categorias');
    }
}
