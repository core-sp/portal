<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Str;

class UserPolicy
{
    use HandlesAuthorization;

    /*
    * class_basename(\Route::current()->controller) = permite avaliar a permissão em qualquer rota atual requisitada, 
    * pois é o mesmo método, então assim facilita para evitar criar policies iguais para todos.
    * Reutilizando os métodos
    */

    public function viewAny(User $user)
    {
        return perfisPermitidos(class_basename(\Route::current()->controller), 'index');
    }
    
    public function create(User $user)
    {
        return perfisPermitidos(class_basename(\Route::current()->controller), 'create');
    }

    public function updateOther(User $user)
    {
        return perfisPermitidos(class_basename(\Route::current()->controller), 'edit');
    }

    public function updateShow(User $user)
    {
        return perfisPermitidos(class_basename(\Route::current()->controller), 'show');
    }

    public function delete(User $user)
    {
        return perfisPermitidos(class_basename(\Route::current()->controller), 'destroy');
    }

    public function updateOwn(User $user)
    {
        return $user->id == auth()->id();
    }

    public function viewOwn(User $user)
    {
        return $user->id == auth()->id();
    }

    public function onlyAdmin(User $user)
    {
        return $user->isAdmin();
    }

    public function atendenteOrGerSeccionais(User $user)
    {
        return $user->idperfil == 8 || $user->idperfil == 21;
    }

    public function gerarTextoView(User $user)
    {
        $tipo = ucfirst(Str::camel(request()->tipo_doc));

        if(class_basename(\Route::current()->controller) == 'GerarTextoController')
            return perfisPermitidos($tipo, 'index');
    }

    public function gerarTextoCreate(User $user)
    {
        $tipo = ucfirst(Str::camel(request()->tipo_doc));

        if(class_basename(\Route::current()->controller) == 'GerarTextoController')
            return perfisPermitidos($tipo, 'create');
    }

    public function gerarTextoUpdate(User $user)
    {
        $tipo = ucfirst(Str::camel(request()->tipo_doc));

        if(class_basename(\Route::current()->controller) == 'GerarTextoController')
            return perfisPermitidos($tipo, 'edit');
    }
}
