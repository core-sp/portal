<?php

namespace App;

// use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes/*, TabelaAdmin*/;

    protected $guarded = [];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    // public function variaveis() {
    //     return [
    //         'singular' => 'post',
    //         'singulariza' => 'o post',
    //         'plural' => 'posts',
    //         'pluraliza' => 'posts',
    //         'titulo_criar' => 'Cadastrar post',
    //         'btn_criar' => '<a href="/admin/posts/create" class="btn btn-primary mr-1">Novo Post</a>'
    //     ];
    // }

    // public function path()
    // {
    //     return '/blog/' . $this->slug;
    // }

    // protected function tabelaHeaders()
    // {
    //     return [
    //         'Código',
    //         'Autor',
    //         'Título',
    //         'Subtítulo',
    //         'Ações'
    //     ];
    // }

    // protected function tabelaContents($query)
    // {
    //     return $query->map(function($row){
    //         $acoes = '<a href="/blog/'.$row->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';

    //         if(auth()->user()->can('updateOther', auth()->user())) {
    //             $acoes .= '<a href="'.route('posts.edit', $row->id).'" class="btn btn-sm btn-primary">Editar</a> ';
    //         }
                
    //         if(auth()->user()->can('delete', auth()->user())) {
    //             $acoes .= '<form method="POST" action="'.route('posts.destroy', $row->id).'" class="d-inline">';
    //             $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
    //             $acoes .= '<input type="hidden" name="_method" value="delete" />';
    //             $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Deletar" onclick="return confirm(\'Tem certeza que deseja deletar o post?\')" />';
    //             $acoes .= '</form>';
    //         }
            
    //         isset($row->user) ? $autor = $row->user->nome : $autor = 'Usuário Deletado';
    //         return [
    //             $row->id,
    //             $autor.'<br>'.formataData($row->created_at),
    //             $row->titulo,
    //             $row->subtitulo,
    //             $acoes
    //         ];
    //     })->toArray();
    // }

    // public function tabelaCompleta($query)
    // {
    //     return $this->montaTabela(
    //         $this->tabelaHeaders(), 
    //         $this->tabelaContents($query),
    //         [ 'table', 'table-hover' ]
    //     );
    // }

    // public function latestPosts()
    // {
    //     return $this->orderBy('created_at', 'DESC')->limit(3)->get();
    // }
}
