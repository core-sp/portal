<div class="row nomargin mb-3 login-header">
    @if(auth()->guard('pre_representante')->check())
        <p class="cinza-claro p-restrita m-auto-992">
            <small>
                <a href="{{ route('prerepresentante.dashboard') }}">
                    <i class="fas fa-user"></i>&nbsp;
                   {{ limitRepresentanteName(auth()->guard('pre_representante')->user()->nome) }}
                </a>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <a id="logout" href="#">
                    Logout
                </a>
            </small>
        </p>
    @else
        <a href="#">
            <p class="cinza-claro p-restrita m-auto-992">
                <small>
                    <a href="{{ route('prerepresentante.login') }}">
                        <i class="fas fa-lock"></i>&nbsp;
                        Área restrita do Pré-registro
                    </a>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <a href="{{ route('prerepresentante.cadastro') }}">
                        Cadastre-se
                    </a>
                </small>
            </p>
        </a>
    @endif
</div>