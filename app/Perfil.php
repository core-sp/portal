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
