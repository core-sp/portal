<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use App\Repositories\GerentiRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Repositories\GerentiRepositoryMock;
use App\Repositories\GerentiRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if(env("APP_ENV") == "local" || env("APP_ENV") == "testing") {
            $this->app->bind(GerentiRepositoryInterface::class, GerentiRepositoryMock::class);
        }
        else {
            $this->app->bind(GerentiRepositoryInterface::class, GerentiRepository::class);
        }
        
        $this->app->singleton('App\Contracts\MediadorServiceInterface', 'App\Services\MediadorService');
        $this->app->bind('App\Contracts\SuporteServiceInterface', 'App\Services\SuporteService');
        $this->app->bind('App\Contracts\PlantaoJuridicoServiceInterface', 'App\Services\PlantaoJuridicoService');
        $this->app->bind('App\Contracts\RegionalServiceInterface', 'App\Services\RegionalService');
        $this->app->bind('App\Contracts\TermoConsentimentoServiceInterface', 'App\Services\TermoConsentimentoService');
        $this->app->bind('App\Contracts\AgendamentoServiceInterface', 'App\Services\AgendamentoService');
        $this->app->bind('App\Contracts\LicitacaoServiceInterface', 'App\Services\LicitacaoService');
        $this->app->bind('App\Contracts\FiscalizacaoServiceInterface', 'App\Services\FiscalizacaoService');
        $this->app->bind('App\Contracts\PostServiceInterface', 'App\Services\PostService');
        $this->app->bind('App\Contracts\NoticiaServiceInterface', 'App\Services\NoticiaService');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Validator::extend('recaptcha', 'App\\Validators\\ReCaptcha@validate');
    }
}
