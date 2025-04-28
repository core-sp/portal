<?php

namespace Tests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PermissoesTableSeeder;
use App\Permissao;
use App\Perfil;

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

    protected function pathLogExterno()
    {
        return 'logs/externo/'.date('Y').'/'.date('m').'/laravel-'.date('Y-m-d').'.log';
    }

    protected function pathLogErros()
    {
        return 'logs/erros/laravel-'.date('Y-m-d').'.log';
    }

    private function perfilAdminExiste()
    {
        return Perfil::where('idperfil', 1)->exists();
    }

    protected function relacionarPerfil($perfil)
    {
        Permissao::orderBy('idpermissao')->get()->each(function ($item, $key) use($perfil) {
            $item->perfis()->sync([$perfil->idperfil]);
        });
    }

    protected function relacionarPerfilPermissao($perfil, $controller, $metodo)
    {
        $permissao = Permissao::where('controller', $controller)->where('metodo', $metodo)->first();

        if(isset($permissao))
            isset($perfil) ? $permissao->perfis()->syncWithoutDetaching([$perfil->idperfil]) : $permissao->perfis()->sync(array());
    }

    protected function signIn($user = null)
    {
        $this->seed(PermissoesTableSeeder::class);

        if(!$this->perfilAdminExiste())
            $perfilDeAdmin = factory('App\Perfil')->create([
                'idperfil' => 1,
                'nome' => 'Admin'
            ]);
            
        $user = $user ?: factory('App\User')->create();

        $this->actingAs($user)
        ->withoutMiddleware(\Illuminate\Session\Middleware\AuthenticateSession::class);

        return $user;
    }

    protected function signInAsAdmin($email = null)
    {
        $this->seed(PermissoesTableSeeder::class);

        if(!$this->perfilAdminExiste())
            factory('App\Perfil')->create([
                'idperfil' => 1,
                'nome' => 'Admin'
            ]);

        $user = factory('App\User')->create([
            'idperfil' => 1,
            'email' => isset($email) ? $email : 'email_fake_admin@core-sp.org.br'
        ]);

        $this->actingAs($user)
        ->withoutMiddleware(\Illuminate\Session\Middleware\AuthenticateSession::class);

        return $user;
    }
}
