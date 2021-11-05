@extends('site.layout.app', ['title' => 'Login no Pré Registro'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/banner-login-representante.jpg') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Login no Pré Registro
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
                        <h2 class="stronger">Login - Área do Pré Registro</h2>
                    </div>
                    <div class="align-self-center">
                        <a href="/" class="btn-voltar">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha-lg"></div>
        <div class="row mt-2">
            <div class="col-lg-8 conteudo-txt">
                @if(Session::has('message'))
                    <p class="alert {{ Session::get('class') }}">
                        {{ Session::get('message') }}
                    </p>
                @endif
                <p>Caso já tenha se cadastrado, preencha as informações abaixo para <strong>acessar a área restrita do Pré Registro.</strong></p>
                <p>Ou então, <a href="{{ route('prerepresentante.cadastro') }}">realize o cadastro</a> e depois efetue o login.</p>
                <form action="{{ route('prerepresentante.login.submit') }}" method="POST" class="cadastroRepresentante" id="login-pre-registro">
                    @csrf
                    <div class="form-group">
                        <label for="login">CPF ou CNPJ</label>
                        <input id="login"
                            type="text"
                            class="form-control cpfOuCnpj {{ $errors->has('login') ? ' is-invalid' : '' }}"
                            name="login"
                            value="{{ old('login') }}"
                            placeholder="CPF ou CNPJ"
                            required
                        >
                        @if($errors->has('login'))
                        <div class="invalid-feedback">
                            {{ $errors->first('login') }}
                        </div>
                        @endif
                    </div>
                    <div class="form-group mt-2">
                        <label for="password">Senha</label>
                        <input
                            id="password"
                            type="password"
                            class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                            name="password"
                            placeholder="Senha"
                            required
                        >
                        @if($errors->has('password'))
                        <div class="invalid-feedback">
                            {{ $errors->first('password') }}
                        </div>
                        @endif
                    </div>
                    <div class="form-group mt-2">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                    <div class="form-group mt-2">
                        <p>
                            Esqueceu sua senha? <a href="{{ route('prerepresentante.password.request') }}">Clique aqui</a> para alterá-la.
                        </p>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                @include('site.inc.content-sidebar')
            </div>
        </div>
    </div>
</section>

@endsection