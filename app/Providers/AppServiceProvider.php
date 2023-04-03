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
            $this->app->singleton(GerentiRepositoryInterface::class, GerentiRepositoryMock::class);
        }
        else {
            $this->app->singleton(GerentiRepositoryInterface::class, GerentiRepository::class);
        }
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
