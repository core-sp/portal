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

    public static function permissoesAgrupadasPorController($id = null)
    {
        $id = isset($id) ? (int) $id : 0;

        // Traz todas as permissões e na coluna "permitido" indica se a id do perfil possui a permissão
        return self::leftJoin('perfil_permissao', function ($join) use($id) {
            $join->on('permissoes.idpermissao', '=', 'perfil_permissao.permissao_id')
                ->where('perfil_permissao.perfil_id', '=', $id);
        })
        ->selectRaw('permissoes.idpermissao, permissoes.controller, permissoes.metodo, permissoes.nome, perfil_permissao.id AS id_intermediaria,
            CASE WHEN perfil_permissao.perfil_id = ' . $id . ' THEN 1
            ELSE 0
            END AS permitido
        ')
        ->orderBy('nome')
        ->get()
        ->groupBy('controller');
    }
}
