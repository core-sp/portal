<?php

namespace Tests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PermissoesTableSeeder;
use Illuminate\Http\UploadedFile;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $this->clearCache();
        $this->gerenciarPastasLazyLoad();
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

    protected function signIn($user = null)
    {
        $this->seed(PermissoesTableSeeder::class);
        factory('App\Perfil')->create([
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
        $perfilDeAdmin = factory('App\Perfil')->create([
            'nome' => 'Admin'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfilDeAdmin->idperfil,
            'email' => isset($email) ? $email : 'email_fake_admin@core-sp.org.br'
        ]);

        $this->actingAs($user)
        ->withoutMiddleware(\Illuminate\Session\Middleware\AuthenticateSession::class);

        return $user;
    }

    protected function gerenciarPastasLazyLoad($img = null)
    {
        $raiz = public_path('/imagens/fake');

        if(is_null($img))
            return \File::exists($raiz) ? exec('rm -R ' . $raiz) : null;

        $raiz_img = $raiz . '/' . date('Y-m') . '/';
        $path = $raiz_img . '.blur';

        if(!\File::exists($path) || (!\File::exists($path) && ($img == '')))
            mkdir($path, 0755, true);

        if(strlen($img) > 3){
            $file = UploadedFile::fake()->image($img, 600, 400);
            \File::put($raiz_img . $file->getClientOriginalName(), $file->get());

            return hash('sha256', $file->get()) . '.' . pathinfo($file->getClientOriginalName())['extension'];
        }
    }

    protected function trocarNomeImgLazyLoad($img, $hash)
    {
        return str_replace(pathinfo($img)['basename'], $hash, $img);
    }
}
