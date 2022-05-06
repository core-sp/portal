<?php

namespace App\Policies;

use App\User;
use App\Agendamento;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgendamentoPolicy
{
    use HandlesAuthorization;

    public function sameRegional(User $user, Agendamento $agendamento)
    {
        return $user->idregional == $agendamento->idregional;
    }
}
