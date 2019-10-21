@extends('site.layout.app', ['title' => 'Cadastro'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/news-interna.png') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Cadastro
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
                        <h2 class="stronger">Cadastro de Representante Comercial</h2>
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
                    <p class="alert alert-danger">{{ Session::get('message') }}</p>
                @endif
                <p>Preencha as informações abaixo para realizar o cadastro e então acessar a área do Representante Comercial.</p>
                <p>Nela, você conseguirá atualizar dados cadastrais e emitir boletos de pagamento de anuidade.</p>
                <h4>Cadastro</h4>
                <form action="{{ route('representante.cadastro.submit') }}" method="POST" class="cadastroRepresentante">
                    @csrf
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="cpfCnpj">CPF ou CNPJ *</label>
                            <input
                                type="text"
                                name="cpfCnpj"
                                class="form-control cpfOuCnpj {{ $errors->has('cpfCnpj') ? 'is-invalid' : '' }}"
                                id="cpfCnpj"
                                value="{{ old('cpfCnpj') }}"
                                placeholder="CPF ou CNPJ"
                            >
                            @if($errors->has('cpfCnpj'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('cpfCnpj') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="registro_core">Registro no Core-SP *</label>
                            <input
                                type="text"
                                name="registro_core"
                                class="form-control {{ $errors->has('registro_core') ? 'is-invalid' : '' }}"
                                id="registro_core"
                                value="{{ old('registro_core') }}"
                                placeholder="?99999/9999"
                            >
                            @if($errors->has('registro_core'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('registro_core') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label for="nome">Nome Completo *</label>
                        <input
                            type="text"
                            name="nome"
                            class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                            id="nome"
                            value="{{ old('nome') }}"
                            placeholder="Nome"
                        >
                        @if($errors->has('nome'))
                            <div class="invalid-feedback">
                                {{ $errors->first('nome') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group mt-2">
                        <label for="email">Email *</label>
                        <input
                            type="text"
                            name="email"
                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="Email"
                        >
                        @if($errors->has('email'))
                            <div class="invalid-feedback">
                                {{ $errors->first('email') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-sm mb-2-576">
                            <label for="password">Senha *</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                id="password"
                                placeholder="Senha"
                            >
                            @if($errors->has('password'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('password') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="password_confirmation">Confirmação de senha</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                id="password_confirmation"
                                placeholder="Confirme a senha"
                            >
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Cadastrar
                        </button>
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