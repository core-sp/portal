<?php

namespace Tests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PermissoesTableSeeder;

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

    protected function signIn($user = null)
    {
        $this->seed(PermissoesTableSeeder::class);
        factory('App\Perfil')->create([
            'nome' => 'Admin'
        ]);

        $user = $user ?: factory('App\User')->create();

        $this->actingAs($user);

        return $user;
    }

    protected function signInAsAdmin($email = null)
    {
        $this->seed(PermissoesTableSeeder::class);
        $perfilDeAdmin = factory('App\Perfil')->create([
            'nome' => 'Admin'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfilDeAdmin->idperfil,
            'email' => isset($email) ? $email : 'email_fake_admin@core-sp.org.br'
        ]);

        $this->actingAs($user);

        return $user;
    }
}
