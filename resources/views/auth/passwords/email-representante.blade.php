@extends('site.layout.app', ['title' => 'Recuperar Senha'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/news-interna.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Recuperar Senha
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
    <div class="container">
        <div class="row">
            <p class="login-box-msg">Digite o CPF ou CNPJ abaixo para reconfigurar sua senha.</p>
        </div>
        <div class="row nomargin">
            @if (session('status'))
              <div class="alert alert-success" role="alert">
                {{ session('status') }}
              </div>
            @endif
            <form action="{{ route('representante.password.email') }}" method="POST">
              {!! csrf_field() !!}
              <div class="form-group has-feedback {{ $errors->has('cpf_cnpj') ? 'has-error' : '' }}">
                <input 
                  type="text"
                  name="cpf_cnpj"
                  class="form-control"
                  value="{{ isset($cpf_cnpj) ? $cpf_cnpj : old('cpf_cnpj') }}"
                  placeholder="CPF / CNPJ"
                >
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                @if ($errors->has('cpf_cnpj'))
                    <span class="help-block">
                      <strong>{{ $errors->first('cpf_cnpj') }}</strong>
                    </span>
                @endif
              </div>
              <button type="submit"
                class="btn btn-primary btn-block btn-flat"
              >Enviar</button>
            </form>
        </div>
    </div>
</section>

@endsection