<div class="row nomargin mb-3 login-header">
    @if(Auth::guard('representante')->check())
        <p class="cinza-claro p-restrita">
            <small>
                <a href="{{ route('representante.dashboard') }}">
                    <i class="fas fa-user"></i>&nbsp;
                   {{ limitRepresentanteName(Auth::guard('representante')->user()->nome) }}
                </a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a href="{{ route('representante.logout') }}">
                    Logout
                </a>
            </small>
        </p>
    @else
        <a href="{{ route('representante.login') }}">
            <p class="cinza-claro p-restrita">
                <small>
                    <a href="{{ route('representante.login') }}">
                        <i class="fas fa-lock"></i>&nbsp;
                        Área do Representante
                    </a>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="{{ route('representante.cadastro') }}">
                        Cadastro
                    </a>
                </small>
            </p>
        </a>
    @endif
</div>