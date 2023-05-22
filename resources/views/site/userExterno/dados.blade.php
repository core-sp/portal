@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Dados Cadastrais</h4>
        <div class="linha-lg-mini"></div>
        <form action="{{ route('externo.editar') }}" method="POST" class="cadastroRepresentante">
            @csrf
            @method('PUT')
            @if(!isset($alterarSenha))
            <div class="form-row">
                <div class="col-sm mb-2-576">
                    <label for="nome">Nome Completo <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="nome"
                        class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }} upperCase"
                        value="{{ empty(old('nome')) && isset($resultado->nome) ? $resultado->nome : old('nome') }}"
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
                    <label for="cpf_cnpj">{{ auth()->guard('contabil')->check() ? 'CNPJ' : 'CPF ou CNPJ' }} <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control cpfOuCnpj"
                        id="cpf_cnpj"
                        value="{{ auth()->guard('contabil')->check() ? $resultado->cnpj : $resultado->cpf_cnpj }}"
                        placeholder="CPF ou CNPJ"
                        readonly
                        disabled
                    >
                </div>
                <div class="col-sm mb-2-576">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input
                        type="email"
                        name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        id="email"
                        value="{{ empty(old('email')) && isset($resultado->email) ? $resultado->email : old('email') }}"
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

            @if(auth()->guard('contabil')->check())
            <div class="form-row mt-2">
                <div class="col-sm mb-2-576">
                    <label for="nome_contato">Nome para contato</label>
                    <input
                        name="nome_contato"
                        type="text"
                        class="form-control {{ $errors->has('nome_contato') ? 'is-invalid' : '' }} upperCase"
                        value="{{ empty(old('nome_contato')) && isset($resultado->nome_contato) ? $resultado->nome_contato : old('nome_contato') }}"
                        maxlength="191"
                        minlength="5"
                    >
                    @if($errors->has('nome_contato'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nome_contato') }}
                    </div>
                    @endif
                </div>
                <div class="col-sm-4 mb-2-576">
                    <label for="telefone">Telefone para contato</label>
                    <input
                        type="text"
                        name="telefone"
                        class="form-control telefoneInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
                        id="email"
                        value="{{ empty(old('telefone')) && isset($resultado->telefone) ? $resultado->telefone : old('telefone') }}"
                    >
                    @if($errors->has('telefone'))
                    <div class="invalid-feedback">
                        {{ $errors->first('telefone') }}
                    </div>
                    @endif
                </div>
            </div>
            @endif
            <div class="form-group mt-3">
                <a href="{{ route('externo.editar.senha.view') }}" class="btn btn-danger text-decoration-none text-white">
                    Alterar Senha
                </a>
            </div>
            @else
            <div class="form-row">
                <input type="hidden" id="cpf_cnpj" />
                <div class="col-sm mb-2-576">
                    <label for="password_atual">Senha atual <span class="text-danger">*</span></label>
                    <input
                        type="password"
                        name="password_atual"
                        class="form-control {{ $errors->has('password_atual') ? 'is-invalid' : '' }}"
                        placeholder="Senha Atual"
                        required
                    >
                    @if($errors->has('password_atual'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password_atual') }}
                    </div>
                    @endif
                </div>
            </div>
            <div class="form-row mt-2">
                <div class="col-sm mb-2-576">
                    <label for="password">Nova senha <span class="text-danger">*</span></label>
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
                    <label for="password_confirmation">Confirmação da nova senha <span class="text-danger">*</span></label>
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
            <small class="form-text text-muted mb-2">
                <em>A senha deve conter no mínimo: 8 caracteres, uma letra maiúscula, uma letra minúscula e um número</em><br />
            </small>

            @component('components.verifica_forca_senha')
            @endcomponent

            @endif
            <div class="form-group mt-3 float-right">
                <a
                    href="{{ route('externo.dashboard') }}"
                    class="btn btn-default text-dark text-decoration-none mr-2"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($alterarSenha))
<script type="text/javascript" src="{{ asset('/js/zxcvbn.js?'.time()) }}"></script>
<script type="text/javascript" src="{{ asset('/js/security.js?'.time()) }}"></script>
@endif

@endsection