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
}