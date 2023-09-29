<?php

namespace App\Policies;

use App\User;
use App\AgendamentoSala;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgendamentoSalaPolicy
{
    use HandlesAuthorization;

    public function sameRegional(User $user, AgendamentoSala $agendamento)
    {
        return $user->idregional == $agendamento->sala_reuniao_id;
    }
}
