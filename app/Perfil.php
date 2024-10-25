<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Permissao;

class Perfil extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idperfil';
    protected $table = 'perfis';
    protected $guarded = [];

    public function user()
    {
        return $this->hasMany('App\User', 'idperfil');
    }

    public function permissoes()
    {
        return $this->belongsToMany('App\Permissao', 'perfil_permissao', 'perfil_id', 'permissao_id');
    }

    public function perfilAdmin()
    {
        return $this->idperfil == 1;
    }

    public function temPermissao($controller, $metodo)
    {
        return $this->perfilAdmin() || $this->permissoes->where('controller', $controller)->where('metodo', $metodo)->isNotEmpty();
    }

    public function podeAcessarMenuConteudo()
    {
        return $this->perfilAdmin() || $this->permissoes->where('grupo_menu', Permissao::G_CONTEUDO)->isNotEmpty();
    }

    public function podeAcessarMenuAtendimento()
    {
        return $this->perfilAdmin() || $this->permissoes->where('grupo_menu', Permissao::G_ATENDIMENTO)->isNotEmpty();
    }

    public function podeAcessarMenuJuridico()
    {
        return $this->perfilAdmin() || $this->permissoes->where('grupo_menu', Permissao::G_JURIDICO)->isNotEmpty();
    }

    public function podeAcessarMenuFiscal()
    {
        return $this->perfilAdmin() || $this->permissoes->where('grupo_menu', Permissao::G_FISCAL)->isNotEmpty();
    }

    public function podeAcessarSubMenuBalcao()
    {
        return $this->perfilAdmin() || $this->temPermissao('BdoEmpresaController', 'index') || $this->temPermissao('BdoOportunidadeController', 'index');
    }

    public function podeAcessarSubMenuAgendamento()
    {
        return $this->perfilAdmin() || $this->temPermissao('AgendamentoController', 'index') || $this->temPermissao('AgendamentoBloqueioController', 'index');
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
