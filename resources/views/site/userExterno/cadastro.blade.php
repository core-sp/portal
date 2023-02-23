@extends('site.layout.app', ['title' => 'Cadastro no Login Externo'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/banner-cadastro-representante.jpg') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Cadastro no Login Externo
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
                        <h2 class="stronger">Cadastro para o Login Externo</h2>
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
                <p>A Área Restrita do Login Externo é destinada a usuários que desejam utilizar os serviços que não necessitam do registro de <strong>Representante Comercial</strong>.</p>
                <p>Preencha as informações abaixo e realize o seu cadastro.</p>
                <p class="text-danger">É recomendável a criação de uma senha forte para a sua segurança.</p>
                <hr>
                <form action="{{ route('externo.cadastro.submit') }}" method="POST" class="cadastroRepresentante">
                    @csrf
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="nome">Nome Completo <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="nome"
                                class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }} upperCase"
                                value="{{ old('nome') }}"
                                placeholder="Nome Completo"
                                maxlength="191"
                                minlength="5"
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
                            <label for="cpf_cnpj">CPF ou CNPJ <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="cpf_cnpj"
                                class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
                                id="cpf_cnpj"
                                value="{{ apenasNumeros(old('cpf_cnpj')) }}"
                                placeholder="CPF ou CNPJ"
                                required
                            >
                            @if($errors->has('cpf_cnpj'))
                            <div class="invalid-feedback">
                                {{ $errors->first('cpf_cnpj') }}
                            </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                name="email"
                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                id="email"
                                value="{{ old('email') }}"
                                maxlength="191"
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
                            <label for="password">Senha <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                name="password"
                                class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                id="password"
                                placeholder="Senha"
                                minlength="8"
                                maxlength="191"
                                required
                            >
                            @if($errors->has('password'))
                            <div class="invalid-feedback">
                                {{ $errors->first('password') }}
                            </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="password_confirmation">Confirmação de senha <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                                id="password_confirmation"
                                placeholder="Confirme a senha"
                                minlength="8"
                                maxlength="191"
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
                        <em>A senha deve conter, no mínimo: 8 caracteres, uma letra maiúscula, uma letra minúscula e um número</em><br />
                    </small>

                    <div class="mt-2">
                        @component('components.verifica_forca_senha')
                        @endcomponent
                    </div>

                    <div class="form-group mt-3">
                    @if(env('GOOGLE_RECAPTCHA_KEY'))
                        <div class="g-recaptcha {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>
                        @if($errors->has('g-recaptcha-response'))
                            <div class="invalid-feedback" style="display:block;">
                            {{ $errors->first('g-recaptcha-response') }}
                            </div>
                        @endif
                    @endif
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input position-static {{ $errors->has('aceite') ? 'is-invalid' : '' }}"
                            name="aceite"
                            type="checkbox"
                            id="checkbox-termo-de-uso"
                            required
                        />
                        <label for="checkbox-termo-de-uso">
                            Li e concordo com os <a class="text-primary" href="{{ route('termo.consentimento.pdf') }}" target="_blank">Termos de Uso</a> da Área Restrita do Login Externo do Core-SP.
                        </label>
                        @if($errors->has('aceite'))
                        <div class="invalid-feedback">
                            {{ $errors->first('aceite') }}
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

<script type="text/javascript" src="{{ asset('/js/zxcvbn.js?'.time()) }}"></script>
<script type="text/javascript" src="{{ asset('/js/security.js?'.time()) }}"></script>

@endsection