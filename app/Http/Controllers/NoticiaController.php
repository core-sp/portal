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
    // Variáveis extras da página
    public $variaveis = [
        'singular' => 'noticia',
        'singulariza' => 'a notícia',
        'plural' => 'noticias',
        'pluraliza' => 'notícias',
        'titulo_criar' => 'Publicar notícia',
        'btn_criar' => '<a href="/admin/noticias/criar" class="btn btn-primary mr-1">Nova Notícia</a>',
        'btn_lixeira' => '<a href="/admin/noticias/lixeira" class="btn btn-warning">Notícias Deletadas</a>',
        'btn_lista' => '<a href="/admin/noticias" class="btn btn-primary">Lista de Notícias</a>',
        'titulo' => 'Notícias Deletadas'
    ];

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
    }

    public function resultados()
    {
        $resultados = Noticia::orderBy('idnoticia','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Título',
            'Regional',
            'Última alteração',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/noticia/'.$resultado->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            $acoes .= '<a href="/admin/noticias/editar/'.$resultado->idnoticia.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/noticias/apagar/'.$resultado->idnoticia.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a notícia?\')" />';
            $acoes .= '</form>';
            if(isset($resultado->idregional))
                $regional = $resultado->regional->regional;
            else
                $regional = "Todas";
            if($resultado->publicada == 'Sim')
                $publicada = 'Publicada';
            else
                $publicada = 'Rascunho';
            $conteudo = [
                $resultado->idnoticia,
                $resultado->titulo.'<br><small><em>'.$publicada.'</em></small>',
                $regional,
                Helper::formataData($resultado->updated_at).'<br><small>Por: '.$resultado->user->nome.'</small>',
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Editor', 'Gestão de Atendimento']);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Editor', 'Gestão de Atendimento']);
        $regionais = Regional::orderBy('regional', 'ASC')->get();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }

    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin', 'Editor', 'Gestão de Atendimento']);
        $regras = [
            'titulo' => 'required',
            'conteudo' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Checa o usuário
        if($request->user()->hasRole('Gestão de Atendimento'))
            $publicada = 'Não';
        else
            $publicada = 'Sim';
        // Inputa dados no BD
        $noticia = new Noticia();
        $noticia->titulo = $request->input('titulo');
        $noticia->slug = Str::slug($request->input('titulo'), '-');
        $noticia->img = $request->input('img');
        $noticia->conteudo = $request->input('conteudo');
        $noticia->publicada = $publicada;
        $noticia->idregional = $request->input('regionais');
        $noticia->idcurso = $request->input('curso');
        $noticia->idusuario = $request->input('idusuario');
        $save = $noticia->save();
        if(!$save)
            abort(500);
        return redirect('/admin/noticias')
            ->with('message', '<i class="icon fa fa-check"></i>Notícia criada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['Admin', 'Editor', 'Gestão de Atendimento']);
        $resultado = Noticia::find($id);
        $regionais = Regional::orderBy('regional', 'ASC')->get();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regionais'));
    }

    public function update(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['Admin', 'Editor', 'Gestão de Atendimento']);
        $regras = [
            'titulo' => 'required',
            'conteudo' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Checa o usuário
        if($request->user()->hasRole('Gestão de Atendimento'))
            $publicada = 'Não';
        else
            $publicada = 'Sim';   
        // Inputa dados no BD
        $noticia = Noticia::find($id);
        $noticia->titulo = $request->input('titulo');
        $noticia->slug = Str::slug($request->input('titulo'), '-');
        $noticia->img = $request->input('img');
        $noticia->conteudo = $request->input('conteudo');
        $noticia->publicada = $publicada;
        $noticia->idregional = $request->input('regionais');
        $noticia->idcurso = $request->input('curso');
        $noticia->idusuario = $request->input('idusuario');
        $update = $noticia->update();
        if(!$update)
            abort(500);
        return redirect('/admin/noticias')
            ->with('message', '<i class="icon fa fa-check"></i>Notícia editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['Admin', 'Editor', 'Gestão de Atendimento']);
        $noticia = Noticia::find($id);
        $delete = $noticia->delete();
        if(!$delete)
            abort(500);
        return redirect('/admin/noticias')
            ->with('message', '<i class="icon fa fa-ban"></i>Notícia deletada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['Admin']);
        $resultados = Noticia::onlyTrashed()->paginate(10);
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Título',
            'Deletada em:',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/noticias/restore/'.$resultado->idnoticia.'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idnoticia,
                $resultado->titulo,
                Helper::formataData($resultado->deleted_at),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $variaveis = (object) $this->variaveis;
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $noticia = Noticia::onlyTrashed()->find($id);
        $noticia->restore();
        return redirect('/admin/noticias')
            ->with('message', '<i class="icon fa fa-check"></i>Notícia restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Noticia::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
