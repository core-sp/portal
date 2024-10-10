<?php

namespace App;

use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Permissao;

class Perfil extends Model
{
    use SoftDeletes, TabelaAdmin;

    protected $primaryKey = 'idperfil';
    protected $table = 'perfis';
    protected $guarded = [];

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

    public function permissoes()
    {
        return $this->belongsToMany('App\Permissao', 'perfil_permissao', 'perfil_id', 'permissao_id');
    }

    public function temPermissao($controller, $metodo)
    {
        return !is_null($this->permissoes->where('controller', $controller)->where('metodo', $metodo)->first());
    }

    public function podeAcessarMenuConteudo()
    {
        return !is_null($this->permissoes->where('grupo_menu', Permissao::G_CONTEUDO)->first());
    }

    public function podeAcessarMenuAtendimento()
    {
        return !is_null($this->permissoes->where('grupo_menu', Permissao::G_ATENDIMENTO)->first());
    }

    public function podeAcessarMenuJuridico()
    {
        return !is_null($this->permissoes->where('grupo_menu', Permissao::G_JURIDICO)->first());
    }

    public function podeAcessarMenuFiscal()
    {
        return !is_null($this->permissoes->where('grupo_menu', Permissao::G_FISCAL)->first());
    }

    public function podeAcessarSubMenuBalcao()
    {
        return $this->temPermissao('BdoEmpresaController', 'index') || $this->temPermissao('BdoOportunidadeController', 'index');
    }

    public function podeAcessarSubMenuAgendamento()
    {
        return $this->temPermissao('AgendamentoController', 'index') || $this->temPermissao('AgendamentoBloqueioController', 'index');
    }

    public function podeAcessarSubMenuRepresentante()
    {
        return $this->temPermissao('RepresentanteController', 'index') || $this->temPermissao('RepresentanteEnderecoController', 'index') || 
        $this->temPermissao('SolicitaCedulaController', 'index');
    }

    public function podeAcessarSubMenuSalaReuniao()
    {
        return $this->podeAcessarSubMenuAgendamento() || $this->temPermissao('SalaReuniaoController', 'index') || 
        $this->temPermissao('SuspensaoExcecaoController', 'index');
    }

    public function podeAcessarSubMenuPlantao()
    {
        return $this->temPermissao('PlantaoJuridicoController', 'index') || $this->temPermissao('PlantaoJuridicoBloqueioController', 'index');
    }
}
