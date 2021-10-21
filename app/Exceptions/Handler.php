<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Log;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        if($exception instanceof AuthenticationException){
            $guard = Arr::get($exception->guards(), 0);
            switch($guard){
                case 'representante':
                    return redirect(route('representante.login'));
                break;
                case 'pre_representante':
                    return redirect(route('prerepresentante.login'));
                break;
                default:
                    return redirect(route('login'));
                break;
            }
        }

        return parent::render($request, $exception);
    }
}
