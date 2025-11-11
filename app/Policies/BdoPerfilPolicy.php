<?php

namespace App\Policies;

use App\User;
use App\BdoRepresentante;
use Illuminate\Auth\Access\HandlesAuthorization;

class BdoPerfilPolicy
{
    use HandlesAuthorization;

    public function podeAcessarPerfil(User $user, BdoRepresentante $bdoRC)
    {
        $id_perfil = $user->idperfil;

        switch ($id_perfil) {
            case 3:
                return $bdoRC->statusEtapaFinal();
                    break;
            case 6:
            case 8:
                return $bdoRC->statusContemAtendimento();
                    break;
            case 16:
                return $bdoRC->statusContemFinanceiro();
                break;
            default:
                return $user->isAdmin();
        }
    }
}
