<div class="row nomargin mb-3 login-header">
    @if(auth()->guard('user_externo')->check())
        <p class="cinza-claro p-restrita m-auto-992">
            <small>
                <a href="{{ route('externo.dashboard') }}">
                    <i class="fas fa-user"></i>&nbsp;
                   {{ limitRepresentanteName(auth()->guard('user_externo')->user()->nome) }}
                </a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a id="logout-externo" href="#">
                    Logout
                </a>
            </small>
        </p>
    @else
        <a href="#">
            <p class="cinza-claro p-restrita m-auto-992">
                <small>
                    <a href="{{ route('externo.login') }}">
                        <i class="fas fa-lock"></i>&nbsp;
                        Área restrita do Login Externo
                    </a>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="{{ route('externo.cadastro') }}">
                        Cadastre-se
                    </a>
                </small>
            </p>
        </a>
    @endif
</div>