<?php

namespace App\Http\Middleware;

use Closure;

class PasswordDefault
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(auth()->guard('web')->check() && auth()->user()->password_default)
            return redirect()->route('admin.info')
                ->with('message', '<strong>Troque a senha padrão fornecida pelo setor de TI para voltar a ter acesso aos serviços!</strong>')
                ->with('class', 'alert-warning');

        return $next($request);
    }
}
