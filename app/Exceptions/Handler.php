<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Log;
use Illuminate\Session\TokenMismatchException;
use App\Exceptions\PagamentoException;

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
        if(($exception instanceof AuthenticationException) && (\Route::is('pagamento.*')))
            return redirect(route('site.home'));

        if($exception instanceof AuthenticationException){
            $guard = Arr::get($exception->guards(), 0);
            switch($guard){
                case 'representante':
                    return redirect(route('representante.login'));
                break;
                default:
                    return Str::endsWith($exception->getFile(), 'AuthenticateSession.php') ? 
                        redirect(route('site.home')) : redirect(route('login'));
                break;
            }
        }

        if(($exception instanceof TokenMismatchException) && \Route::is('pagamento.*'))
        {
            $e = new PagamentoException($exception->getMessage(), 419);
            return $e->render(request(), 'CODE_419');
        }

        return parent::render($request, $exception);
    }
}
