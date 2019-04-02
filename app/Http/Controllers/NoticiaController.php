<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Noticia;
use App\Regional;
use App\Curso;
use Illuminate\Support\Str;

class NoticiaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $noticias = Noticia::paginate(10);
        return view('admin.noticias.home', compact('noticias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regionais = Regional::orderBy('regional', 'ASC')->get();
        $cursos = Curso::all();
        return view('admin.noticias.criar', compact('regionais', 'cursos'));
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

        $noticia = new Noticia();
        $noticia->titulo = $request->input('titulo');
        $noticia->slug = Str::slug($request->input('titulo'), '-');
        $noticia->img = $request->input('img');
        $noticia->conteudo = $request->input('conteudo');
        $noticia->idregional = $request->input('regionais');
        $noticia->idcurso = $request->input('curso');
        $noticia->idusuario = $request->input('idusuario');
        $noticia->save();
        return redirect('/admin/noticias');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $noticia = Noticia::where('slug', $slug)->first();
        return view('site.noticia', compact('noticia'));
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
        $noticia = Noticia::find($id);
        $cursos = Curso::all();
        $regionais = Regional::orderBy('regional', 'ASC')->get();
        return view('admin.noticias.editar', compact('noticia', 'regionais', 'cursos'));
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

        $noticia = Noticia::find($id);
        $noticia->titulo = $request->input('titulo');
        $noticia->slug = Str::slug($request->input('titulo'), '-');
        $noticia->img = $request->input('img');
        $noticia->conteudo = $request->input('conteudo');
        $noticia->idregional = $request->input('regionais');
        $noticia->idcurso = $request->input('curso');
        $noticia->idusuario = $request->input('idusuario');
        $noticia->update();
        return redirect('/admin/noticias');
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
        $noticia = Noticia::find($id);
        $noticia->delete();
        return redirect('/admin/noticias');
    }

    /**
     * Mostra a lixeira de notícias
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $noticias = Noticia::onlyTrashed()->paginate(10);
        return view('/admin/noticias/lixeira', compact('noticias'));
    }

    /**
     * Restaura notícia deletada
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $noticia = Noticia::onlyTrashed()->find($id);
        $noticia->restore();
        return redirect('/admin/noticias');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $noticias = Noticia::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($noticias) > 0) 
            return view('admin.noticias.home', compact('noticias', 'busca'));
        else
            return view('admin.noticias.home')->withMessage('Nenhuma notícia encontrada');
    }
}
