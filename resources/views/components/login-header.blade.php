<div class="row nomargin mb-3 login-header">
    @if(Auth::guard('representante')->check())
        <p class="cinza-claro p-restrita m-auto-992">
            <small>
                <a href="{{ route('representante.dashboard') }}" data-clarity-mask="True">
                    <i class="fas fa-user"></i>&nbsp;
                   {{ limitRepresentanteName(Auth::guard('representante')->user()->nome) }}
                </a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a id="logout-representante" href="#">
                    Logout
                </a>
            </small>
        </p>
    @elseif(auth()->guard('user_externo')->check() || auth()->guard('contabil')->check())
        <p class="cinza-claro p-restrita m-auto-992">
            <small>
                <a href="{{ route('externo.dashboard') }}">
                    <i class="fas fa-user"></i>&nbsp;
                   {{ limitRepresentanteName(auth()->guard(getGuardExterno(auth()))->user()->nome) }}
                </a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a id="logout-externo" href="#">
                    Logout
                </a>
            </small>
        </p>
    @else
    {{-- Após OK para uso geral
        <a href="{{ route('paginas.site', 'areas-restritas') }}">
            <p class="cinza-claro p-restrita m-auto-992">
                <small>
                    <a href="{{ route('paginas.site', 'areas-restritas') }}">
                        <i class="fas fa-lock"></i>&nbsp;
                        Acessar áreas restritas ou cadastre-se
                    </a>
                </small>
            </p>
        </a>
    --}}
        <a href="{{ route('representante.login') }}">
            <p class="cinza-claro p-restrita m-auto-992">
                <small>
                    <a href="{{ route('representante.login') }}">
                        <i class="fas fa-lock"></i>&nbsp;
                        Área restrita do Representante
                    </a>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="{{ route('representante.cadastro') }}">
                        Cadastre-se
                    </a>
                </small>
            </p>
        </a>
    @endif
</div>