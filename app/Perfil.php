<?php

namespace App;

use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perfil extends Model
{
    use SoftDeletes, TabelaAdmin;

    protected $primaryKey = 'idperfil';
    protected $table = 'perfis';
    protected $fillable = ['nome'];

    public function user()
    {
        return $this->hasMany('App\User', 'idperfil');
    }

    public function variaveis() {
        return [
            'singular' => 'perfil',
            'singulariza' => 'o perfil',
            'plural' => 'perfis',
            'pluraliza' => 'perfis',
            'titulo_criar' => 'Cadastrar perfil',
            'btn_criar' => '<a href="/admin/usuarios/perfis/criar" class="btn btn-primary mr-1">Novo Perfil</a>'
        ];
    }

    protected function tabelaHeaders()
    {
        return [
            'Código',
            'Nome',
            'Nº de Usuários',
            'Ações'
        ];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="/admin/usuarios/perfis/editar/'.$row->idperfil.'" class="btn btn-sm btn-primary">Editar Permissões</a> ';
            $acoes .= '<form method="POST" action="/admin/usuarios/perfis/apagar/'.$row->idperfil.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'CUIDADO! Isto pode influenciar diretamente no funcionamento do Portal. Tem certeza que deseja excluir o perfil?\')" />';
            $acoes .= '</form>';
            
            return [
                $row->idperfil,
                $row->nome,
                $row->user_count,
                $acoes
            ];
        })->toArray();
    }

    public function tabelaCompleta($query)
    {
        return $this->montaTabela(
            $this->tabelaHeaders(), 
            $this->tabelaContents($query),
            [ 'table', 'table-hover' ]
        );
    }
}
