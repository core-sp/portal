@extends('site.layout.app', ['title' => 'Cadastro no Pré-registro'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/banner-cadastro-representante.jpg') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Cadastro no Pré-registro
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
                        <h2 class="stronger">Cadastro para o Pré-registro</h2>
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
                <p>Seja bem-vindo(a).</p>
                <p>A Área Restrita do Pré-registro é destinada exclusivamente aos que pretendem se tornar Representantes Comerciais.</p>
                <p>Preencha as informações abaixo e realize o seu cadastro.</p>
                <hr>
                <form action="{{ route('prerepresentante.cadastro.submit') }}" method="POST" class="cadastroRepresentante">
                    @csrf
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="nome">Nome Completo *</label>
                            <input
                                type="text"
                                name="nome"
                                class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                                value="{{ old('nome') }}"
                                placeholder="Nome Completo"
                                required
                            >
                            @if($errors->has('nome'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('nome') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-sm-4 mb-2-576">
                            <label for="cpf_cnpj_cad">CPF ou CNPJ *</label>
                            <input
                                type="text"
                                name="cpf_cnpj_cad"
                                class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj_cad') ? 'is-invalid' : '' }}"
                                id="cpf_cnpj_cad"
                                value="{{ old('cpf_cnpj_cad') }}"
                                placeholder="CPF ou CNPJ"
                                required
                            >
                            @if($errors->has('cpf_cnpj_cad'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('cpf_cnpj_cad') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="email">Email *</label>
                            <input
                                type="text"
                                name="email"
                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                id="email"
                                value="{{ old('email') }}"
                                placeholder="Email"
                                required
                            >
                            @if($errors->has('email'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>
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
                                required
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
                                required
                            >
                            @if($errors->has('password_confirmation'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('password_confirmation') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        <em>A senha deve conter no mínimo: 8 caracteres, uma letra maiúscula, uma letra minúscula e um número</em><br />
                    </small>
                    <div class="form-check mt-3">
                        <input class="form-check-input position-static {{ $errors->has('checkbox-tdu') ? 'is-invalid' : '' }}"
                            name="checkbox-tdu"
                            type="checkbox"
                            id="checkbox-termo-de-uso"
                            {{ old('checkbox-tdu') === 'on' ? 'checked' : '' }}
                            required
                        />
                        <p class="d-inline ml-1 lh-28">
                            <small class="light lh-28">
                                Li e concordo com os <a class="text-primary" href="/arquivos/Termo_de_Uso_e_Consentimento_Area_Restrita_rev.pdf" target="_blank">Termos de Uso</a> da Área Restrita do Pré-registro do Core-SP.
                            </small>
                        </p>
                        @if($errors->has('checkbox-tdu'))
                            <div class="invalid-feedback">
                                {{ $errors->first('checkbox-tdu') }}
                            </div>
                        @endif
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