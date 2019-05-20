<?php

namespace App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use App\Http\Controllers\ControleController;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            return in_array($user->email, [
                'desenvolvimento@core-sp.org.br',
                'edson@core-sp.org.br'
            ]);
        });
    }
}
