@extends('site.layout.app', ['title' => 'Notícias'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/news-interna.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Notícias
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
            <h4 class="stronger">Todas as notícias</h4>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      @foreach($noticias as $noticia)
        @include('site.inc.noticia-grid')
      @endforeach
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