@extends('site.layout.app', ['title' => 'Busca'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-busca.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Busca
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
            <p class="light">Busca por: <strong>{{ $busca }}</strong>
            @if($resultados->count())
            <small><i>- {{ $resultados->count() === 1 ? $resultados->count() . ' resultado' : $resultados->count() . ' resultados' }}</i></small></p>
            @endif
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      <div class="col-sm-8">
        @if($resultados->isEmpty())
          Sua busca não retornou <strong>nenhum resultado!</strong>
        @else
          @foreach($resultados as $resultado)
          <div class="box-resultado">
            @if($resultado->tipo === "Notícia")
            <a href="/noticia/{{ $resultado->slug }}"">
              <h5 class="normal"><i>{{ $resultado->tipo }} -</i> <strong>{{ $resultado->titulo }}</strong></h5>
            </a>
            <h6 class="cinza mb-2">Publicado em: {{ onlyDate($resultado->created_at) }}</h6>
            {!! resumo($resultado->conteudo) !!}
            <div>
              <a href="/noticia/{{ $resultado->slug }}" class="btn-curso-grid mt-3">Confira</a>
            </div>
            @elseif($resultado->tipo == 'Página')
            <a href="/{{ $resultado->slug }}">
              <h5 class="normal mb-2"><i>{{ $resultado->tipo }} -</i> <strong>{{ $resultado->titulo }}</strong></h5>
            </a>
            {!! resumo($resultado->conteudo) !!}
            <div>
              <a href="/{{ $resultado->slug }}" class="btn-curso-grid mt-3">Confira</a>
            </div>
            @else
            <a href="{{ route('site.blog.post', $resultado->slug) }}"">
              <h5 class="normal"><i>{{ $resultado->tipo }} -</i> <strong>{{ $resultado->titulo }}</strong></h5>
            </a>
            <h6 class="cinza mb-2">Publicado em: {{ onlyDate($resultado->created_at) }}</h6>
            {!! resumo($resultado->conteudo) !!}
            <div>
              <a href="{{ route('site.blog.post', $resultado->slug) }}" class="btn-curso-grid mt-3">Confira</a>
            </div>
            @endif
          </div>
          @endforeach
        @endif
      </div>
      <div class="col-sm-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
  </div>
</section>



@endsection