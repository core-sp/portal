<?php

namespace App\Repositories;

use App\AgendamentoBloqueio;

class AgendamentoBloqueioRepository 
{
    // public function getByRegionalAndDay($idregional, $day)
    // {
    //     return AgendamentoBloqueio::where('idregional', $idregional)
    //         ->whereDate('diainicio', '<=', $day)
    //         ->whereDate('diatermino', '>=', $day)
    //         ->get();
    // }

    // public function getByRegional($idregional)
    // {
    //     return AgendamentoBloqueio::where('idregional', $idregional)
    //         ->where('diatermino','>=', date('Y-m-d'))
    //         ->get();
    // }

    // public function getAll()
    // {
    //     return AgendamentoBloqueio::orderBy('idagendamentobloqueio', 'DESC')
    //         ->where('diatermino','>=', date('Y-m-d'))
    //         ->paginate(10);
    // }

    // public function getById($id) 
    // {
    //     return AgendamentoBloqueio::findOrFail($id);
    // }

    // public function getBusca($criterio)
    // {
    //     return AgendamentoBloqueio::whereHas('regional', function($q) use($criterio){
    //         $q->where('regional', 'LIKE', '%' . $criterio . '%');
    //     })->get();
    // }

    // public function store($agendamentoBloqueio)
    // {
    //     return AgendamentoBloqueio::create($agendamentoBloqueio);
    // }

    // public function update($id, $agendamentoBloqueio)
    // {
    //     return AgendamentoBloqueio::findOrFail($id)->update($agendamentoBloqueio);
    // }

    // public function delete($id)
    // {
    //     return AgendamentoBloqueio::findOrFail($id)->delete();
    // }
}