@extends('site.layout.app', ['title' => 'Recuperar senha do Login Externo'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-reset-senha-representante.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Recuperar Senha do Login Externo
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
                        <h2 class="stronger">Reconfigurar senha no Login Externo</h2>
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
            <form action="{{ route('externo.password.email') }}" method="POST">
              @csrf
              <div class="form-group">
                <label for="tipo_conta">Tipo da conta <small>(opcional)</small></label>
                <select name="tipo_conta" class="form-control {{ $errors->has('tipo_conta') ? 'is-invalid' : '' }}" id="tipo_conta">
                  <option value="user_externo" selected>Especifique o tipo de conta, se necessário...</option>
                  <option value="contabil">Contabilidade</option>
                </select>
                @if($errors->has('tipo_conta'))
                <div class="invalid-feedback">
                  {{ $errors->first('tipo_conta') }}
                </div>
                @endif
              </div>
              <div class="form-group">
                <label for="cpf_cnpj">CPF ou CNPJ <small class="text-danger">*</small></label>
                <input 
                  type="text"
                  name="cpf_cnpj"
                  class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') || $errors->has('cnpj') ? ' is-invalid' : '' }}"
                  placeholder="CPF / CNPJ"
                  required
                  autocomplete="off"
                >
                @if($errors->has('cpf_cnpj') || $errors->has('cnpj'))
                <div class="invalid-feedback">
                  {{ $errors->has('cpf_cnpj') ? $errors->first('cpf_cnpj') : $errors->first('cnpj') }}
                </div>
                @endif
              </div>
              <div class="form-group mt-2">
                <button type="submit"
                  class="btn btn-primary"
                >Enviar</button>
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