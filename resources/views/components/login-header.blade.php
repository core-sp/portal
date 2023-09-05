<div class="row nomargin mb-3 login-header" data-clarity-mask="True">
    @if(Auth::guard('representante')->check())
        <p class="cinza-claro p-restrita m-auto-992">
            <small>
                <a href="{{ route('representante.dashboard') }}">
                    <i class="fas fa-user"></i>&nbsp;
                   {{ limitRepresentanteName(Auth::guard('representante')->user()->nome) }}
                </a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a id="logout-representante" href="#">
                    Logout
                </a>
            </small>
        </p>
    @else
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