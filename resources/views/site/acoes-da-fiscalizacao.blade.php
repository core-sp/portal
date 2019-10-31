@extends('site.layout.app', ['title' => 'Fiscalização'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-fiscalizacao.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Fiscalização
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
          <div class="flex-one pr-3 align-self-center">
            <h2 class="stronger">Ações da Fiscalização do Core-SP</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      @forelse($noticias as $noticia)
        @include('site.inc.noticia-grid')
      @empty
        <div class="col">
          <h4>Em breve!</h4>          
        </div>
      @endforelse
    </div>
    <div class="row mb-2">
      <div class="col">
        @if(isset($noticias))
          <div class="mt-4 float-right">
            {{ $noticias->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection