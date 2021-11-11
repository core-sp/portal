@extends('site.prerepresentante.app')

@section('content-prerepresentante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Dados do Cadastro</h4>
        <div class="linha-lg-mini"></div>
        <form action="{{ route('prerepresentante.editar') }}" method="POST" class="cadastroRepresentante">
            @csrf
            @method('PUT')
            @if(!isset($alterarSenha))
            <div class="form-row">
                <div class="col-sm mb-2-576">
                    <label for="nome">Nome Completo *</label>
                    <input
                        type="text"
                        name="nome"
                        class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                        value="{{ isset($resultado->nome) ? $resultado->nome : old('nome') }}"
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
                    <label for="cpf_cnpj">CPF ou CNPJ *</label>
                    <input
                        type="text"
                        class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
                        id="cpf_cnpj"
                        value="{{ isset($resultado->cpf_cnpj) ? $resultado->cpf_cnpj : '' }}"
                        placeholder="CPF ou CNPJ"
                        required
                        readonly
                    >
                </div>
                <div class="col-sm mb-2-576">
                    <label for="email">Email *</label>
                    <input
                        type="text"
                        name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        id="email"
                        value="{{ isset($resultado->email) ? $resultado->email : old('email') }}"
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
                <a href="{{ route('prerepresentante.editar.senha.view') }}" class="btn btn-danger text-decoration-none text-white">
                    Alterar Senha
                </a>
            </div>
            @else
            <div class="form-row">
                <div class="col-sm mb-2-576">
                    <label for="password_login">Senha atual *</label>
                    <input
                        type="password"
                        name="password_login"
                        class="form-control {{ $errors->has('password_login') ? 'is-invalid' : '' }}"
                        placeholder="Senha Atual"
                        required
                    >
                    @if($errors->has('password_login'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password_login') }}
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
            @endif
            <div class="form-group mt-3 float-right">
                <a
                    href="{{ route('prerepresentante.dashboard') }}"
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