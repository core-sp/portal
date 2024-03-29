<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class MediadorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('App\Contracts\MediadorServiceInterface', 'App\Services\MediadorService');
        $this->app->singleton('App\Contracts\SuporteServiceInterface', 'App\Services\SuporteService');
        $this->app->singleton('App\Contracts\PlantaoJuridicoServiceInterface', 'App\Services\PlantaoJuridicoService');
        $this->app->singleton('App\Contracts\RegionalServiceInterface', 'App\Services\RegionalService');
        $this->app->singleton('App\Contracts\TermoConsentimentoServiceInterface', 'App\Services\TermoConsentimentoService');
        $this->app->singleton('App\Contracts\AgendamentoServiceInterface', 'App\Services\AgendamentoService');
        $this->app->singleton('App\Contracts\LicitacaoServiceInterface', 'App\Services\LicitacaoService');
        $this->app->singleton('App\Contracts\FiscalizacaoServiceInterface', 'App\Services\FiscalizacaoService');
        $this->app->singleton('App\Contracts\PostServiceInterface', 'App\Services\PostService');
        $this->app->singleton('App\Contracts\NoticiaServiceInterface', 'App\Services\NoticiaService');
        $this->app->singleton('App\Contracts\CedulaServiceInterface', 'App\Services\CedulaService');
        $this->app->singleton('App\Contracts\RepresentanteServiceInterface', 'App\Services\RepresentanteService');
        $this->app->singleton('App\Contracts\SalaReuniaoServiceInterface', 'App\Services\SalaReuniaoService');
        $this->app->singleton('App\Contracts\SalaReuniaoSiteSubServiceInterface', 'App\Services\SalaReuniaoSiteSubService');
        $this->app->singleton('App\Contracts\AgendamentoSalaSubServiceInterface', 'App\Services\AgendamentoSalaSubService');
        $this->app->singleton('App\Contracts\SalaReuniaoBloqSubServiceInterface', 'App\Services\SalaReuniaoBloqSubService');
        $this->app->singleton('App\Contracts\SuspensaoExcecaoSubServiceInterface', 'App\Services\SuspensaoExcecaoSubService');
        $this->app->singleton('App\Contracts\AvisoServiceInterface', 'App\Services\AvisoService');
        $this->app->singleton('App\Contracts\CursoServiceInterface', 'App\Services\CursoService');
        $this->app->singleton('App\Contracts\CursoSubServiceInterface', 'App\Services\CursoSubService');
        $this->app->singleton('App\Contracts\HomeImagemServiceInterface', 'App\Services\HomeImagemService');
        $this->app->singleton('App\Contracts\GerarTextoServiceInterface', 'App\Services\GerarTextoService');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'App\Contracts\MediadorServiceInterface',
            'App\Contracts\SuporteServiceInterface',
            'App\Contracts\PlantaoJuridicoServiceInterface',
            'App\Contracts\RegionalServiceInterface',
            'App\Contracts\TermoConsentimentoServiceInterface',
            'App\Contracts\AgendamentoServiceInterface',
            'App\Contracts\LicitacaoServiceInterface',
            'App\Contracts\FiscalizacaoServiceInterface',
            'App\Contracts\PostServiceInterface',
            'App\Contracts\NoticiaServiceInterface',
            'App\Contracts\CedulaServiceInterface',
            'App\Contracts\RepresentanteServiceInterface',
            'App\Contracts\SalaReuniaoServiceInterface',
            'App\Contracts\SalaReuniaoSiteSubServiceInterface',
            'App\Contracts\AgendamentoSalaSubServiceInterface',
            'App\Contracts\SalaReuniaoBloqSubServiceInterface',
            'App\Contracts\SuspensaoExcecaoSubServiceInterface',
            'App\Contracts\AvisoServiceInterface',
            'App\Contracts\CursoServiceInterface',
            'App\Contracts\CursoSubServiceInterface',
            'App\Contracts\HomeImagemServiceInterface',
            'App\Contracts\GerarTextoServiceInterface',
        ];
    }
}
