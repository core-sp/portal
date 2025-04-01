@extends('site.layout.app', ['title' => 'Login'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/banner-login-representante.jpg') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Login
                </h1>
            </div>
        </div>
    </div>
</section>

<section id="pagina-noticias">
    <div class="container">
        <div class="row" id="conteudo-principal">
            <div class="col">
                <div class="row nomargin">
                    <div class="flex-one pr-4 align-self-center">
                        <h2 class="stronger">Login - Área do Representante</h2>
                    </div>
                    <div class="align-self-center">
                        <a href="/" class="btn-voltar">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha-lg"></div>
        @if(!$errors->has('email_system'))
        <div class="row mt-2">
            <div class="col-lg-8 conteudo-txt">
                @if(Session::has('message'))
                    <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
                @endif
                <p>Caso já tenha se cadastrado, preencha as informações abaixo para <strong>acessar a área restrita do Representante Comercial.</strong></p>
                <p>Ou então, <a href="{{ route('representante.cadastro') }}">realize o cadastro</a> e depois efetue o login.</p>
                <form action="{{ route('representante.login.submit') }}" method="POST" class="cadastroRepresentante" autocomplete="off">
                    @csrf
                    <div class="form-group">
                        <label for="login">CPF ou CNPJ</label>
                        <input id="login"
                            type="text"
                            class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? ' is-invalid' : '' }}"
                            name="cpf_cnpj"
                            placeholder="CPF ou CNPJ"
                        >
                        @if ($errors->has('cpf_cnpj'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('cpf_cnpj') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="form-group mt-2">
                        <label for="password">Senha</label>
                        <input
                            id="password"
                            type="password"
                            class="form-control mb-2"
                            name="password"
                            placeholder="Senha"
                        />
                        @component('components.verifica_forca_senha')
                        @endcomponent
                    </div>
                    <div class="form-group mt-2">
                        {{--<input id="email_system" type="text" class="form-control" name="email_system" value="" tabindex="-1">--}}
                        <button type="submit" class="btn btn-primary loadingPagina">Entrar</button>
                    </div>
                    <div class="form-group mt-2">
                        <hr>
                        <p>
                            @if (Route::has('password.request'))
                                Esqueceu sua senha? <a href="{{ route('representante.password.request') }}">Clique aqui</a> para reconfigurá-la.
                            @endif
                        </p>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                @include('site.inc.content-sidebar')
            </div>
        </div>
        @endif
    </div>
</section>

<script type="module" src="{{ asset('/js/modulos/security.js?'.hashScriptJs()) }}" id="modulo-security" class="modulo-visualizar"></script>

@endsection