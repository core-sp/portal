@extends('site.layout.app', ['title' => 'Recuperar Senha'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-reset-senha-representante.jpg') }}" />
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
        <div class="row" id="conteudo-principal">
            <div class="col">
                <div class="row nomargin">
                    <div class="flex-one pr-4 align-self-center">
                        <h2 class="stronger">Reconfigurar senha</h2>
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
              <p class="alert {{ Session::get('class') }}" data-clarity-mask="True">{{ Session::get('message') }}</p>
            @endif
            @if (session('status'))
              <div class="alert alert-success" role="alert" data-clarity-mask="True">
                {!! session('status') !!}
              </div>
            @endif
            <p>Digite o CPF ou CNPJ abaixo para reconfigurar sua senha.</p>
            <form action="{{ route('representante.password.email') }}" method="POST">
              @csrf
              <div class="form-group has-feedback {{ $errors->has('cpf_cnpj') ? 'has-error' : '' }}">
                <input 
                  type="text"
                  name="cpf_cnpj"
                  class="form-control cpfOuCnpj"
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
              <div class="form-group mt-2">
                <button type="submit"
                  class="btn btn-primary"
                >Enviar</button>
              </div>
            </form>
            <hr>
            <p><i>* Caso não tenha acesso ao email cadastrado inicialmente ou este não seja mais válido, acesse <a href="{{ route('representante.email.reset.view') }}">este link para alterá-lo</a>.</i></p>
          </div>
          <div class="col-lg-4">
              @include('site.inc.content-sidebar')
          </div>
        </div>
    </div>
</section>

@endsection