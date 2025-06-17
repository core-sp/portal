<?php

namespace App\Services;

use App\Noticia;
use App\Contracts\NoticiaServiceInterface;
use App\Events\CrudEvent;
use Illuminate\Support\Str;
use App\Contracts\MediadorServiceInterface;
use Exception;
use App\Traits\ImagensLazyLoad;

class NoticiaService implements NoticiaServiceInterface {

    use ImagensLazyLoad;

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'noticia',
            'singulariza' => 'a notícia',
            'plural' => 'noticias',
            'pluraliza' => 'notícias',
            'titulo_criar' => 'Publicar notícia',
            'btn_criar' => '<a href="'.route("noticias.create").'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Nova Notícia</a>',
            'btn_lixeira' => '<a href="'.route("noticias.trashed").'" class="btn btn-warning"><i class="fas fa-trash"></i> Notícias Deletadas</a>',
            'btn_lista' => '<a href="'.route("noticias.index").'" class="btn btn-primary"><i class="fas fa-list"></i> Lista de Notícias</a>',
            'titulo' => 'Notícias Deletadas'
        ];
    }

    private function tabelaCompleta($resultados)
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
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        $userPodeExcluir = auth()->user()->can('delete', auth()->user());
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('noticias.show', $resultado->slug).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($userPodeEditar)
                $acoes .= '<a href="'.route('noticias.edit', $resultado->idnoticia).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir)
            {
                $acoes .= '<form method="POST" action="'.route('noticias.destroy', $resultado->idnoticia).'" class="d-inline acaoTabelaAdmin">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="hidden" class="cor-danger txtTabelaAdmin" value="Tem certeza que deseja excluir a notícia <i>' . $resultado->titulo . '</i>?" />';
                $acoes .= '<button type="button" class="btn btn-sm btn-danger" value="' . $resultado->idnoticia . '">Apagar</button>';
                $acoes .= '</form>';
            }
            $autor = isset($resultado->user) ? $resultado->user->nome : 'Usuário Deletado';
            $publicada = $resultado->publicada == 'Sim' ? 'Publicada' : 'Rascunho';
            $conteudo = [
                $resultado->idnoticia,
                $resultado->titulo.'<br><small><em>'.$publicada.'</em></small>',
                isset($resultado->idregional) ? $resultado->regional->regional : 'Todas',
                formataData($resultado->updated_at).'<br><small>Por: '.$autor.'</small>',
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

    private function tabelaCompletaLixeira($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código', 
            'Título', 
            'Deletada em:', 
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('noticias.restore', $resultado->idnoticia).'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idnoticia,
                $resultado->titulo,
                formataData($resultado->deleted_at),
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

    public function getCategorias()
    {
        return Noticia::categorias();
    }

    public function listar()
    {
        $resultados = Noticia::with(['user', 'regional'])->orderBy('idnoticia', 'DESC')->paginate(10);

        if(auth()->user()->cannot('create', auth()->user()))
            unset($this->variaveis['btn_criar']);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view(MediadorServiceInterface $service, $id = null)
    {
        $dados = [
            'variaveis' => (object) $this->variaveis,
            'regionais' => $service->getService('Regional')->all()->sortBy('regional'),
            'categorias' => $this->getCategorias(),
        ];

        if(isset($id))
            $dados['resultado'] = Noticia::findOrFail($id);

        return $dados;
    }

    public function save($request, $user, $id = null)
    {
        $request['slug'] = Str::slug($request['titulo'], '-');
        $request['conteudoBusca'] = converterParaTextoCru($request['conteudo']);
        $request['idusuario'] = $user->idusuario;
        $request['publicada'] = $user->perfil == 'Estagiário' ? 'Não' : 'Sim';
        $txt = isset($id) ? 'editou' : 'criou';

        if(isset($id))
            Noticia::findOrFail($id)->update($request);
        else  
            $id = Noticia::create($request)->idnoticia;
            
        $this->gerarPreImagemLFM($request['img']);

        event(new CrudEvent('notícia', $txt, $id));
    }

    public function destroy($id)
    {
        $apagado = Noticia::findOrFail($id)->delete();
        if($apagado)
            event(new CrudEvent('notícia', 'apagou', $id));
    }

    public function lixeira()
    {
        $resultados = Noticia::onlyTrashed()->orderBy('idnoticia', 'DESC')->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompletaLixeira($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function restore($id)
    {
        $restaurado = Noticia::onlyTrashed()->findOrFail($id)->restore();
        if($restaurado)
            event(new CrudEvent('notícia', 'restaurou', $id));
    }

    public function buscar($busca)
    {
        $resultados = Noticia::with(['user', 'regional'])->where('titulo','LIKE','%'.$busca.'%')
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
        $noticia = Noticia::where('slug', $slug)->firstOrFail();
        $tres = Noticia::latest()
            ->take(3)
            ->orderBy('created_at', 'DESC')
            ->where('idnoticia', '!=', $noticia->idnoticia)
            ->whereNull('idregional')
            ->get();

        return [
            'noticia' => $noticia,
            'tres' => $tres,
        ];
    }

    public function siteGrid()
    {
        return Noticia::select('img', 'slug', 'titulo', 'created_at', 'conteudo')
            ->orderBy('created_at', 'DESC')
            ->where('publicada', 'Sim')
            ->paginate(9);
    }

    public function buscaSite($buscaArray)
    {
        return Noticia::selectRaw("'Notícia' as tipo, titulo, null as subtitulo, slug, created_at, conteudo")
            ->where(function($query) use ($buscaArray) {
                foreach($buscaArray as $b) {
                    $query->where(function($q) use ($b) {
                        $q->where('titulo','LIKE','%'.$b.'%')
                            ->orWhere('conteudoBusca','LIKE','%'.$b.'%');
                    });
                }
            })->orderBy('created_at', 'DESC')
            ->limit(10);
    }

    public function latest()
    {
        $noticias = Noticia::where('publicada','Sim')
            ->whereNull('idregional')
            ->whereNull('categoria')
            ->orderBy('created_at','DESC')
            ->limit(6)
            ->get();
        $cotidianos = Noticia::where('publicada','Sim')
            ->where('categoria','Cotidiano')
            ->orderBy('created_at','DESC')
            ->limit(4)
            ->get();
        
        return [
            'noticias' => $noticias,
            'cotidianos' => $cotidianos,
        ];
    }

    public function latestByCategoria($categoria)
    {
        if(in_array($categoria, $this->getCategorias()))
            return Noticia::select('img','slug','titulo','created_at','conteudo')
                ->orderBy('created_at', 'DESC')
                ->where('publicada','Sim')
                ->where('categoria',$categoria)
                ->paginate(9);
        else
            throw new Exception('Categoria em Notícias não encontrada', 500);
    }
}