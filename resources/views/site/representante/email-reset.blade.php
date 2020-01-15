@extends('site.layout.app', ['title' => 'Recuperar Email'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-reset-senha-representante.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Recuperar Email
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
                        <h2 class="stronger">Reconfigurar email</h2>
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
              <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
            @endif
            <p class="pb-0">Preencha os campos abaixo para reconfigurar o email.</p>
            <p>Caso todas as informações correspondam às cadastradas no sistema, o <strong>email será atualizado com o valor informado no campo "Novo email".</strong></p>
            <form action="{{ route('representante.email.reset') }}" method="POST" class="cadastroRepresentante">
              @csrf
              <div class="form-group">
                <label for="cpf_cnpj">CPF ou CNPJ</label>
                <input 
                  type="text"
                  name="cpf_cnpj"
                  class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
                  value="{{ isset($cpf_cnpj) ? $cpf_cnpj : old('cpf_cnpj') }}"
                  placeholder="CPF / CNPJ"
                >
                @if ($errors->has('cpf_cnpj'))
                    <div class="invalid-feedback">
                      {{ $errors->first('cpf_cnpj') }}
                    </div>
                @endif
              </div>
              <div class="form-group">
                <label for="registro_core">Registro no Core-SP</label>
                <input 
                  type="text"
                  name="registro_core"
                  class="form-control {{ $errors->has('registro_core') ? 'is-invalid' : '' }}"
                  id="registro_core"
                  value="{{ isset($registro_core) ? $registro_core : old('registro_core') }}"
                  placeholder="Nº do registro no Core-SP"
                >
                @if ($errors->has('registro_core'))
                  <div class="invalid-feedback">
                    {{ $errors->first('registro_core') }}
                  </div>
                @endif
              </div>
              <div class="form-group">
                <label for="email_antigo">Email antigo</label>
                <input 
                  type="text"
                  name="email_antigo"
                  class="form-control {{ $errors->has('email_antigo') ? 'is-invalid' : '' }}"
                  value="{{ isset($email_antigo) ? $email_antigo : old('email_antigo') }}"
                  placeholder="Email antigo"
                >
                @if ($errors->has('email_antigo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('email_antigo') }}
                  </div>
                @endif
              </div>
              <div class="form-group">
                <label for="email_novo">Novo email</label>
                <input 
                  type="text"
                  name="email_novo"
                  class="form-control {{ $errors->has('email_novo') ? 'is-invalid' : '' }}"
                  value="{{ isset($email_novo) ? $email_novo : old('email_novo') }}"
                  placeholder="Novo email"
                >
                @if ($errors->has('email_novo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('email_novo') }}
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