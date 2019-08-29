<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function signIn($user = null)
    {
        $user = $user ?: factory('App\User')->create();
        $this->actingAs($user);
        return $user;
    }

    protected function signInAsAdmin()
    {
        $perfilDeAdmin = factory('App\Perfil')->create([
            'nome' => 'Admin'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfilDeAdmin->idperfil
        ]);

        $this->actingAs($user);
        return $user;
    }

    protected function signInAsEditor()
    {
        $perfilDeEditor = factory('App\Perfil')->create([
            'nome' => 'Editor'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfilDeEditor->idperfil
        ]);

        $this->actingAs($user);
        return $user;
    }

    protected function signInAsAtendimento()
    {
        $perfilDeAtendimento = factory('App\Perfil')->create([
            'nome' => 'Atendimento'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfilDeAtendimento->idperfil
        ]);

        $this->actingAs($user);
        return $user;
    }
}
