@extends('site.layout.app', ['title' => 'Blog'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/news-interna.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Blog
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
            <h4 class="stronger">Todas os posts</h4>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      @forelse ($posts as $post)
        @include('site.inc.post-grid')
      @empty
        <p>Nenhum post encontrado!</p>
      @endforelse
    </div>
    <div class="row mb-2">
      <div class="col">
        @if(isset($posts))
          <div class="mt-4 float-right">
            {{ $posts->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection