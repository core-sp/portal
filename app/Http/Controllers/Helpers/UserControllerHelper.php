<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserControllerHelper extends Controller
{
    public static function perfis()
    {
        $perfis = [
            'Admin',
            'Editor',
            'Atendimento',
            'Gestão de Atendimento',
            'Jurídico'
        ];
        return $perfis;
    }
}
