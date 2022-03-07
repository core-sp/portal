@extends('site.layout.app', ['title' => 'Aceitação do Termo de Consentimento'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-consulta.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Termo de Consentimento
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-busca">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h2 class="stronger">Termo de consentimento para recebimento de comunicações</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2" id="conteudo-principal">
      <div class="col-lg-8 consulta-linha">
        <div class="row nomargin mb-3">
          <p class="mb-2 light">
            Para garantir a transparência e a responsabilidade do <strong>CORE-SP</strong>, para que você continue  recebendo os boletins informativos e as oportunidades que só o <strong>CORE-SP</strong> oferece a você, precisamos do seu consentimento.
          </p>
          <p class="light">
            Li e estou de acordo com o <a href="{{ route('termo.consentimento.pdf') }}" target="_blank"><u><strong>TERMO DE CONSENTIMENTO</strong></u></a>
          </p>
        </div>
        <div class="row nomargin consulta">
          @if(session('message'))
          <div class="d-block w-100">
            <p class="alert {{ session('class') }}">{{ session('message') }}</p>
          </div>
          @else
          <form method="post" class="w-100 simulador">
            @csrf
            <div class="flex-one">
              <label for="email">Confirme seu e-mail:</label>
              <input
                type="email"
                id="email"
                name="email"
                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                placeholder="e-mail"
                required
              />
              @if($errors->has('email'))
                <div class="invalid-feedback">
                  {{ $errors->first('email') }}
                </div>
              @endif
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-primary">
                Prosseguir
              </button>
            </div>
          </form>
          @endif
        </div>
        <hr class="mt-4">
      </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
  </div>
</section>
@endsection