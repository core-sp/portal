<?php

namespace App\Repositories;

use App\BdoOportunidade;
use App\BdoEmpresa;

class BdoEmpresaRepository {
    
    public function getToTable()
    {
        return BdoEmpresa::orderBy('idempresa', 'DESC')->paginate(10);
    }

    public function findOrFail($id) 
    {
        return BdoEmpresa::findOrFail($id);
    }

    public function store($dados)
    {
        return BdoEmpresa::create($dados);
    }

    public function update($id, $dados)
    {
        return BdoEmpresa::findOrFail($id)->update($dados);
    }

    public function destroy($id) 
    {
        return BdoEmpresa::findOrFail($id)->delete();
    }

    public function getOportunidadesbyEmpresa($id) 
    {
        return BdoEmpresa::withCount('oportunidade')->findOrFail($id);

    }

    public function getOportunidadesAbertasbyEmpresa($id) 
    {
        return BdoEmpresa::withCount([
            'oportunidade' => function ($query){
                $query->whereIn('status', [BdoOportunidade::$status_sob_analise, BdoOportunidade::$status_em_andamento]);
            }])->findOrFail($id);
    }

    public function getToApi($cnpj) 
    {
        return BdoEmpresa::select('idempresa', 'cnpj', 'razaosocial', 'fantasia', 'telefone', 'segmento', 'endereco', 'site', 'email')
            ->where('cnpj', '=', $cnpj)
            ->withCount([
                'oportunidade' => function ($query){
                    $query->whereIn('status', [BdoOportunidade::$status_sob_analise, BdoOportunidade::$status_em_andamento]);
            }])->first();
    }

    public function busca($criterio) 
    {
        return BdoEmpresa::where('segmento','LIKE','%'.$criterio.'%')
            ->orWhere('razaosocial','LIKE','%'.$criterio.'%')
            ->orWhere('cnpj','LIKE','%'.$criterio.'%')
            ->paginate(10);
    }

    public function getToOportunidade($id) 
    {
        return BdoEmpresa::findOrFail($id, ['idempresa', 'razaosocial', 'segmento']);
    }


}