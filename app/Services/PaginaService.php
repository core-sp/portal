<?php

namespace App\Services;

use App\Pagina;
use App\Contracts\PaginaServiceInterface;
use App\Events\CrudEvent;
use Illuminate\Support\Str;

class PaginaService implements PaginaServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'pagina',
            'singulariza' => 'a página',
            'plural' => 'paginas',
            'pluraliza' => 'páginas',
            'titulo_criar' => 'Criar página',
            'btn_criar' => '<a href="'.route('paginas.create').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Nova Página</a>',
            'btn_lixeira' => '<a href="'.route('paginas.trashed').'" class="btn btn-warning"><i class="fas fa-trash"></i> Páginas Deletadas</a>',
            'btn_lista' => '<a href="'.route('paginas.index').'" class="btn btn-primary"><i class="fas fa-list"></i> Lista de Páginas</a>',
            'titulo' => 'Páginas Deletadas'
        ];
    }

    private function tabelaCompleta($user, $resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Título',
            'Última alteração',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = $user->can('updateOther', $user);
        $userPodeExcluir = $user->can('delete', $user);
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('paginas.site', $resultado->slug).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($userPodeEditar)
                $acoes .= '<a href="'.route('paginas.edit', $resultado->idpagina).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir)
            {
                $acoes .= '<form method="POST" action="'.route('paginas.destroy', $resultado->idpagina).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a página?\')" />';
                $acoes .= '</form>';
            }
            $autor = isset($resultado->user) ? $resultado->user->nome : 'Usuário Deletado';
            $conteudo = [
                $resultado->idpagina,
                $resultado->titulo,
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
            $acoes = '<a href="'.route('paginas.restore', $resultado->idpagina).'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idpagina,
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

    public function listar($user)
    {
        $resultados = Pagina::with(['user'])->orderBy('idpagina', 'DESC')->paginate(10);

        if($user->cannot('create', $user))
            unset($this->variaveis['btn_criar']);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($user, $resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id = null)
    {
        $dados = [
            'variaveis' => (object) $this->variaveis,
        ];

        if(isset($id))
            $dados['resultado'] = Pagina::findOrFail($id);

        return $dados;
    }

    public function save($request, $user, $id = null)
    {
        $request['slug'] = Str::slug($request['titulo'], '-');
        $request['conteudoBusca'] = converterParaTextoCru($request['conteudo']);
        $request['idusuario'] = $user->idusuario;
        $txt = isset($id) ? 'editou' : 'criou';

        if(isset($id))
            Pagina::findOrFail($id)->update($request);
        else  
            $id = Pagina::create($request)->idpagina;
            
        event(new CrudEvent('página', $txt, $id));
    }

    public function destroy($id)
    {
        $apagado = Pagina::findOrFail($id)->delete();
        if($apagado)
            event(new CrudEvent('página', 'apagou', $id));
    }

    public function lixeira()
    {
        $resultados = Pagina::onlyTrashed()->orderBy('idpagina', 'DESC')->paginate(10);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompletaLixeira($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function restore($id)
    {
        $restaurado = Pagina::onlyTrashed()->findOrFail($id)->restore();
        if($restaurado)
            event(new CrudEvent('página', 'restaurou', $id));
    }

    public function buscar($user, $busca)
    {
        $resultados = Pagina::with(['user'])->where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($user, $resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function show($slug)
    {
        $pagina = Pagina::select('titulo','slug','img','subtitulo','conteudo')->where('slug', $slug)->firstOrFail();

        return [
            'pagina' => $pagina,
        ];
    }

    public function buscaSite($buscaArray)
    {
        return Pagina::selectRaw("'Página' as tipo, titulo, subtitulo, slug, created_at, conteudo")
            ->where(function($query) use ($buscaArray) {
                foreach($buscaArray as $b) {
                    $query->where(function($q) use ($b) {
                        $q->where('titulo','LIKE','%'.$b.'%')
                            ->orWhere('subtitulo','LIKE','%'.$b.'%')
                            ->orWhere('conteudoBusca','LIKE','%'.$b.'%');
                    });
                }
            })->limit(10);
    }
}