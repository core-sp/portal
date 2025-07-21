<?php

namespace App\Services;

use App\Post;
use App\Contracts\PostServiceInterface;
use App\Events\CrudEvent;
use Illuminate\Support\Str;
use App\Traits\ImagensLazyLoad;

class PostService implements PostServiceInterface {

    use ImagensLazyLoad;

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'post',
            'singulariza' => 'o post',
            'plural' => 'posts',
            'pluraliza' => 'posts',
            'titulo_criar' => 'Cadastrar post',
            'btn_criar' => '<a href="'.route('posts.create').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Post</a>'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Autor',
            'Título',
            'Subtítulo',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        $userPodeExcluir = auth()->user()->can('delete', auth()->user());
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('site.blog.post', $resultado->slug).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($userPodeEditar)
                $acoes .= '<a href="'.route('posts.edit', $resultado->id).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir)
            {
                $acoes .= '<form method="POST" action="'.route('posts.destroy', $resultado->id).'" class="d-inline acaoTabelaAdmin">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="hidden" class="cor-danger txtTabelaAdmin" value="Tem certeza que deseja excluir o post <i>' . $resultado->titulo . '</i>?" />';
                $acoes .= '<button type="button" class="btn btn-sm btn-danger" value="' . $resultado->id . '">Apagar</button>';
                $acoes .= '</form>';
            }
            $autor = isset($resultado->user) ? $resultado->user->nome : 'Usuário Deletado';
            $conteudo = [
                $resultado->id,
                $autor.'<br>'.formataData($resultado->created_at),
                $resultado->titulo,
                $resultado->subtitulo,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function listar()
    {
        $resultados = Post::with('user')->orderBy('id','DESC')->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id = null)
    {
        if(isset($id))
            return [
                'post' => Post::findOrFail($id),
                'variaveis' => (object) $this->variaveis
            ];

        return [
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($request, $user, $id = null)
    {
        $request['slug'] = Str::slug($request['titulo'], '-');
        $request['conteudoBusca'] = converterParaTextoCru($request['conteudo']);
        $request['idusuario'] = $user->idusuario;
        $txt = isset($id) ? 'editou' : 'criou';

        $img = $this->gerarPreImagemLFM($request['img']);
        if($img)
            $request['img'] = $img;

        if(isset($id))
            Post::findOrFail($id)->update($request);
        else  
            $id = Post::create($request)->id;
            
        event(new CrudEvent('post', $txt, $id));
    }

    public function destroy($id)
    {
        $apagado = Post::findOrFail($id)->delete();
        if($apagado)
            event(new CrudEvent('post', 'apagou', $id));
    }

    public function buscar($busca)
    {
        $resultados = Post::with('user')->where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function viewSite($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $next = Post::select('titulo', 'slug')->where('id', '>', $post->id)->first();
        $previous = Post::select('titulo', 'slug')->where('id', '<', $post->id)->orderBy('id', 'DESC')->first();

        return [
            'post' => $post,
            'next' => $next,
            'previous' => $previous,
        ];
    }

    public function siteGrid()
    {
        return Post::orderBy('created_at', 'DESC')->paginate(9);
    }

    public function buscaSite($buscaArray)
    {
        return Post::selectRaw("'Post' as tipo, titulo, subtitulo, slug, created_at, conteudo")
            ->where(function($query) use ($buscaArray) {
                foreach($buscaArray as $b) {
                    $query->where(function($q) use ($b) {
                        $q->where('titulo','LIKE','%'.$b.'%')
                            ->orWhere('subtitulo','LIKE','%'.$b.'%')
                            ->orWhere('conteudoBusca','LIKE','%'.$b.'%');
                    });
                }
            })->orderBy('created_at', 'DESC')
            ->limit(10);
    }

    public function latest()
    {
        return Post::orderBy('created_at', 'DESC')->limit(3)->get();
    }
}