@extends('site.layout.app', ['title' => 'Login'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/news-interna.png') }}" />
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
        <div class="row">
            <p class="login-box-msg">Digite o email e confirme a nova senha.</p>
            <form method="POST" action="{{ route('representante.password.update') }}">
              @csrf
              <input type="hidden" name="token" value="{{ $token }}">
              <div class="input-group mb-2">
                <input
                  id="cpf_cnpj"
                  type="text"
                  class="form-control {{ $errors->has('cpf_cnpj') ? ' is-invalid' : '' }}"
                  name="cpf_cnpj"
                  value="{{ $cpf_cnpj ?? old('cpf_cnpj') }}"
                  placeholder="E-mail"
                  required autofocus
                >
                <div class="input-group-append">
                  <span class="fa fa-envelope input-group-text"></span>
                </div>
                @if ($errors->has('cpf_cnpj'))
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('cpf_cnpj') }}</strong>
                  </span>
                @endif
              </div>
              <div class="input-group mb-2">
                <input id="password" type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="Nova senha" required>
                <div class="input-group-append">
                  <span class="fa fa-lock input-group-text"></span>
                </div>
                @if ($errors->has('password'))
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('password') }}</strong>
                  </span>
                @endif
              </div>
              <div class="input-group mb-2">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirmar senha" required>
                <div class="input-group-append">
                  <span class="fa fa-lock input-group-text"></span>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">
                Resetar Senha
              </button>
            </form>
        </div>
    </div>
</section>

@endsection