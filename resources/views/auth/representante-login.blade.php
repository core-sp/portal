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
            <form action="{{ route('representante.login.submit') }}" method="POST">
                @csrf
                <div class="input-group mb-3">
                    <input id="login"
                        type="text"
                        class="form-control {{ $errors->has('cpf_cnpj') || $errors->has('cpf_cnpj') ? ' is-invalid' : '' }}"
                        name="cpf_cnpj"
                        value="{{ old('cpf_cnpj') ?: old('cpf_cnpj') }}"
                        placeholder="CPF ou CNPJ"
                        required autofocus
                    >
                    <div class="input-group-append login-icon">
                        <span class="fas fa-user input-group-text" style="line-height:1.5;"></span>
                    </div>
                    @if ($errors->has('cpf_cnpj') || $errors->has('cpf_cnpj'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('cpf_cnpj') ?: $errors->first('cpf_cnpj') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="input-group mb-3">         
                    <input
                        id="password"
                        type="password"
                        class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                        name="password"
                        placeholder="Senha"
                        required
                    >
                    <div class="input-group-append login-icon">
                        <span class="fa fa-lock input-group-text" style="line-height:1.5;"></span>
                    </div>
                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="row">
                    <div class="col-8">
                        <label>
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}> Lembrar senha
                        </label>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Entrar</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        
        <div class="row">
            <p class="mb-1">
                @if (Route::has('password.request'))
                    <a class="btn btn-link" href="{{ route('representante.password.request') }}">
                        {{ __('Esqueceu sua senha?') }}
                    </a>
                @endif
            </p>
        </div>
    </div>
</section>

@endsection