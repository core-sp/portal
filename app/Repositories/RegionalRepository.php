<?php

namespace App\Repositories;

use App\Noticia;
use App\Regional;

class RegionalRepository 
{
    public function all()
    {
        return Regional::all();
    }

    public function getAsc()
    {
        return Regional::orderBy('regional', 'ASC')->get();
    }

    /**
     * Método retorna regionais para atendimento, incluindo unidade da Alameda Santos. Ordenando por regionais e 
     * renomeando "São Paulo" para facilitar vizualização do Representante Comercial.
     */
    public function getRegionaisAgendamento()
    {
        $regionaisAtendimento = Regional::select('idregional', 'regional', 'prefixo')->orderByRaw('case prefixo WHEN "SEDE" THEN 0 ELSE 1 END, idregional ASC')->get();

        $regionaisAtendimento[0]->regional = 'São Paulo - Avenida Brigadeiro Luís Antônio';

        return $regionaisAtendimento;
    }

    /**
     * Método retorna apenas regionais. Retorna apenas SEDE, ES01 ~ ES12 (exclui Alameda Santos).
     */
    public function getRegionais()
    {
        $regionaisFiscalizacao = Regional::select('idregional', 'regional', 'prefixo')->where('idregional', '<=', 13)->get();

        return $regionaisFiscalizacao;
    }

    public function getById($id)
    {
        return Regional::findOrFail($id);
    }

    public function getBusca($busca)
    {
        return Regional::where('regional','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->paginate(10);
    }

    public function getRegionalNoticias($id)
    {
        return Noticia::select('slug','img','created_at','titulo','idregional')
            ->where('idregional','=',$id)
            ->orderBy('created_at','DESC')
            ->limit(3)
            ->get();
    }

    public function update($id, $request)
    {
        if($request->horariosage) {
            $request->merge(['horariosage' => implode(',', $request->horariosage)]);
        }
        return Regional::findOrFail($id)->update($request->all());
    }

    public function getHorariosAgendamento($id, $dia)
    {
        return Regional::find($id)->horariosDisponiveis($dia);
    }

    public function getToList()
    {
        return Regional::select('idregional', 'regional')->get();
    }

    public function getAgeporhorarioById($id)
    {
        return Regional::findOrFail($id)->ageporhorario;
    }

    public function getIdByRegional($regional){
        return Regional::select('idregional')->where('regional', '=', $regional)->first()->idregional;
    }
}