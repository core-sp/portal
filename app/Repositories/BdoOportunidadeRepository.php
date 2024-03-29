<?php

namespace App\Repositories;

use App\BdoOportunidade;

class BdoOportunidadeRepository {
    
    public function getToTable()
    {
        return BdoOportunidade::orderBy('idoportunidade', 'DESC')->paginate(10);
    }

    public function findOrFail($id) 
    {
        return BdoOportunidade::findOrFail($id);
    }

    public function store($dados)
    {
        return BdoOportunidade::create($dados);
    }

    public function update($id, $dados)
    {
        return BdoOportunidade::findOrFail($id)->update($dados);
    }

    public function destroy($id) 
    {
        return BdoOportunidade::findOrFail($id)->delete();
    }

    public function busca($criterio) 
    {
        return BdoOportunidade::where('descricao','LIKE','%'.$criterio.'%')
            ->orWhere('status','LIKE','%'.$criterio.'%')
            ->paginate(10);
    }

    public function getToBalcaoSite() 
    {
        return BdoOportunidade::orderBy('datainicio','DESC')
            ->orderBy('idoportunidade', 'DESC')
            ->whereNotIn('status', [BdoOportunidade::STATUS_SOB_ANALISE, BdoOportunidade::STATUS_CONCLUIDO, BdoOportunidade::STATUS_RECUSADO])
            ->paginate(10);
    }

    public function buscagetToBalcaoSite($buscaSegmento, $buscaRegional, $buscaPalavraChave)  
    {
        $oportunidades = BdoOportunidade::whereNotIn('status', [BdoOportunidade::STATUS_SOB_ANALISE, BdoOportunidade::STATUS_CONCLUIDO, BdoOportunidade::STATUS_RECUSADO]);

        if(!empty($buscaSegmento)) {
            $oportunidades->where('segmento',$buscaSegmento);
        }

        if(!empty($buscaRegional)) {
            $oportunidades->where('regiaoatuacao','LIKE','%'.$buscaRegional.'%');
        }
            
        if(!empty($buscaPalavraChave)) {
            $oportunidades->where(function($query) use ($buscaPalavraChave){
                        $query->where('descricao','LIKE','%'.$buscaPalavraChave.'%')
                            ->orWhere('titulo','LIKE','%'.$buscaPalavraChave.'%');
            });
        }

        return $oportunidades->orderBy('datainicio','DESC')->orderBy('idoportunidade', 'DESC')->paginate(10);
    }

    public function buscaBySegmentoEmAndamento($segmento, $regional)
    {
        return BdoOportunidade::where('segmento', $segmento)
            ->where('status', BdoOportunidade::STATUS_EM_ANDAMENTO)
            ->where('regiaoatuacao','LIKE','%,'.$regional.',%')
            ->orderBy('idoportunidade', 'DESC')
            ->get();
    }
}