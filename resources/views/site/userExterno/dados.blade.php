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
                    <label for="nome">Nome Completo *</label>
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
                    <label for="cpf_cnpj">CPF ou CNPJ *</label>
                    <input
                        type="text"
                        class="form-control cpfOuCnpj"
                        id="cpf_cnpj"
                        value="{{ $resultado->cpf_cnpj }}"
                        placeholder="CPF ou CNPJ"
                        readonly
                        disabled
                    >
                </div>
                <div class="col-sm mb-2-576">
                    <label for="email">Email *</label>
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
            <div class="form-group mt-3">
                <a href="{{ route('externo.editar.senha.view') }}" class="btn btn-danger text-decoration-none text-white">
                    Alterar Senha
                </a>
            </div>
            @else
            <div class="form-row">
                <div class="col-sm mb-2-576">
                    <label for="password_atual">Senha atual *</label>
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
                    <label for="password">Nova senha *</label>
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
                    <label for="password_confirmation">Confirmação da nova senha *</label>
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
                <em>A senha deve conter no mínimo: 8 caracteres, uma letra maiúscula, uma letra minúscula e um número</em><br />
            </small>
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

@endsection