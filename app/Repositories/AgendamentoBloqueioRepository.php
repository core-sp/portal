<?php

namespace App\Repositories;

use App\AgendamentoBloqueio;

class AgendamentoBloqueioRepository {
    public function getByRegionalAndDay($idregional, $day)
    {
        return AgendamentoBloqueio::where('idregional',$idregional)
            ->whereDate('diainicio','<=',$day)
            ->whereDate('diatermino','>=',$day)
            ->get();
    }
}