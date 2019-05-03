@extends('site.layout.app', ['title' => 'Regional'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-seccionais.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          @if(isset($resultado))
          {{ $resultado->regional }}
          @else
          erro
          @endif
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    @if(isset($resultado))
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h4 class="stronger">{{ $resultado->regional }}</h4>
          </div>
          <div class="align-self-center">
            <a href="/seccionais" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      <div class="col-sm-8 pr-4">
        <div class="conteudo-txt">
          <p class="light"><strong>Endereço:</strong> {{ $resultado->endereco }}, {{ $resultado->numero }} - {{ $resultado->complemento }}</p>
          <p class="light"><strong>Bairro: </strong>{{ $resultado->bairro }}</p>
          <p class="light"><strong>CEP: </strong>{{ $resultado->cep }}</p>
          <p class="light"><strong>Telefone:</strong> {{ $resultado->telefone }}</p>
          <p class="light"><strong>Email:</strong> {{ $resultado->email }}</p>
          <p class="light"><strong>Horário de funcionamento: </strong>{{ $resultado->funcionamento }}</p>
          @if(isset($resultado->responsavel))
            <p class="light"><strong>Responsável:</strong> {{ $resultado->responsavel }}</p>
          @endif
          <div class="linha-lg"></div>
          <p><strong>Descrição: </strong></p>
          <div class="descricaoRegional">
            {!! $resultado->descricao !!}
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
    @else
      @include('site.inc.content-error')
    @endif
  </div>
</section>

@endsection