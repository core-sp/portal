<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    protected $primaryKey = 'idpermissao';
    protected $table = 'permissoes';
    protected $guarded = [];

    const G_CONTEUDO = 'CONTEÚDO';
    const G_ATENDIMENTO = 'ATENDIMENTO';
    const G_JURIDICO = 'JURÍDICO';
    const G_FISCAL = 'FISCALIZAÇÃO';

    public function perfis()
    {
        return $this->belongsToMany('App\Perfil', 'perfil_permissao', 'permissao_id', 'perfil_id');
    }
}
