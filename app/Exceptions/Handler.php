<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Log;
use Illuminate\Session\TokenMismatchException;

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
                default:
                    return redirect(route('login'));
                break;
            }
        }

        if(($exception instanceof TokenMismatchException) && \Route::is('pagamento.*')) {
            $msg = 'Tentativa de pagamento por uma sessão que não é mais válida. Deve refazer o fluxo de pagamento.';
            Log::error('[Erro: '.$exception->getMessage().'], [Mensagem: '.$msg.'], [Código: 419], [Rota: '.$request->fullUrl().'], [Arquivo: '.$exception->getFile().'], [Linha: '.$exception->getLine().']');
            abort(419, $msg);
        }

        return parent::render($request, $exception);
    }
}
