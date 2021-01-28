<?php

namespace Tests;

use App\Permissao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $this->clearCache();
        return $app;
    }

    protected function clearCache()
    {
        $commands = ['clear-compiled', 'cache:clear', 'view:clear', 'config:clear', 'route:clear'];
        foreach ($commands as $command) {
            \Illuminate\Support\Facades\Artisan::call($command);
        }
    }

    protected function pathLogInterno()
    {
        return 'logs/interno/'.date('Y').'/'.date('m').'/laravel-'.date('Y-m-d').'.log';
    }

    protected function permissoes()
    {
        $user = Auth::user();
        $idperfil = $user->perfil()->first()->idperfil;
        $permissao = Permissao::all();
        $array = $permissao->toArray();
        $permissoes = [];

        foreach($array as $a) {

            $perfis = explode(',', $a['perfis']);

            if(in_array($idperfil, $perfis)) {
                $cm = $a['controller'].'_'.$a['metodo'];
                $perfis = $a['perfis'];
                array_push($permissoes, $cm);
            }
        }
        
        return $permissoes;
    }

    protected function signIn($user = null)
    {
        factory('App\Perfil')->create([
            'nome' => 'Admin'
        ]);

        $user = $user ?: factory('App\User')->create();

        $this->actingAs($user);

        $this->withSession([
            'perfil' => Auth::user()->perfil()->first()->nome,
            'idperfil' => Auth::user()->perfil()->first()->idperfil,
            'idusuario' => Auth::user()->idusuario,
            'idregional' => Auth::user()->idregional,
            'email' => Auth::user()->email,
            'nome' => Auth::user()->nome,
            'permissoes' => $this->permissoes()
        ]);

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

        $this->withSession([
            'perfil' => Auth::user()->perfil()->first()->nome,
            'idperfil' => Auth::user()->perfil()->first()->idperfil,
            'idusuario' => Auth::user()->idusuario,
            'idregional' => Auth::user()->idregional,
            'email' => Auth::user()->email,
            'nome' => Auth::user()->nome,
            'permissoes' => $this->permissoes()
        ]);

        return $user;
    }
}
