<div class="row nomargin mb-3 login-header">
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
    @elseif(Auth::guard('user_externo')->check())
        <p class="cinza-claro p-restrita m-auto-992">
            <small>
                <a href="{{ route('externo.dashboard') }}">
                    <i class="fas fa-user"></i>&nbsp;
                   {{ limitRepresentanteName(Auth::guard('user_externo')->user()->nome) }}
                </a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a id="logout-externo" href="#">
                    Logout
                </a>
            </small>
        </p>
    @else
        <a href="{{ route('paginas.site', 'areas-restritas') }}">
            <p class="cinza-claro p-restrita m-auto-992">
                <small>
                    <a href="{{ route('paginas.site', 'areas-restritas') }}">
                        <i class="fas fa-lock"></i>&nbsp;
                        Acessar áreas restritas ou cadastre-se
                    </a>
                    <br>
                    <a href="{{ route('externo.login') }}">
                        <i class="fas fa-lock"></i>&nbsp;
                        Temporário link Login User Externo
                    </a>
                </small>
            </p>
        </a>
    @endif
</div>