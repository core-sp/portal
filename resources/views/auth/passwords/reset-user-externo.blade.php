@extends('site.layout.app', ['title' => 'Solicitar alteração de senha no Login Externo'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-reset-senha-representante.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Recuperar senha do Login Externo
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
        <div class="row mt-2">
          <div class="col-lg-8 conteudo-txt">
            <p>Preencha as informações abaixo para reconfigurar sua senha no Login Externo.</p>
            <form method="POST" action="{{ route('externo.password.update') }}" class="cadastroRepresentante" autocomplete="off">
              @csrf
              <input type="hidden" name="token" value="{{ $token }}">
              <div class="form-group">
                <label for="cpf_cnpj">CPF ou CNPJ *</label>
                <input
                  id="cpf_cnpj"
                  type="text"
                  class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? ' is-invalid' : '' }}"
                  name="cpf_cnpj"
                  value="{{ apenasNumeros(old('cpf_cnpj')) }}"
                  placeholder="CPF ou CNPJ"
                  required
                >
                @if($errors->has('cpf_cnpj'))
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('cpf_cnpj') }}</strong>
                  </span>
                @endif
              </div>
              <div class="form-row mt-2">
                <div class="col-sm mt-2-576">
                  <label for="password">Senha</label>
                  <input
                    id="password"
                    type="password"
                    class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                    name="password"
                    placeholder="Nova senha"
                    minlength="8"
                    maxlength="191"
                    required
                  >
                  @if($errors->has('password'))
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $errors->first('password') }}</strong>
                    </span>
                  @endif
                </div>
                <div class="col-sm mt-2-576">
                  <label for="password_confirmation">Confirmação de senha</label>
                  <input
                    id="password_confirmation"
                    type="password"
                    class="form-control {{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                    name="password_confirmation"
                    placeholder="Confirmar senha"
                    minlength="8"
                    maxlength="191"
                    required
                  >
                  @if($errors->has('password_confirmation'))
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $errors->first('password_confirmation') }}</strong>
                    </span>
                  @endif
                </div>
              </div>
              <small class="form-text text-muted">
                <em>A senha deve conter no mínimo: 8 caracteres, uma letra maiúscula, uma letra minúscula e um número</em><br />
              </small>

              <div class="mt-2 mb-2">
                @component('components.verifica_forca_senha')
                @endcomponent
              </div>

              <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">Alterar senha</button>
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