@extends('site.layout.app', ['title' => 'Seccionais'])

@section('description')
  <meta name="description" content="O Core-SP possui escritórios seccionais espalhados por regiões estratégicas em todo o estado de São Paulo. Encontre o escritório mais próximo de você.">
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative">
    <img src="{{ asset('img/banner-interno-seccionais.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Seccionais
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
            <h2 class="stronger">Lista de Seccionais</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
      <div class="col-lg-8">
        @foreach($regionais as $regional)
        <div class="box-resultado">
          <h2 class="pb-1">{{ $regional->prefixo }} - {{ $regional->regional }}</h2>
          <p class="light"><strong>Representante Comercial</strong></p>
          <p class="light"><strong>Endereço:</strong> {{ $regional->endereco }}, {{ $regional->numero }} - {{ $regional->complemento }}</p>
          <p class="light"><strong>Telefone:</strong> {{ $regional->telefone }}</p>
          <p class="light"><strong>Email:</strong> {{ $regional->email }}</p>
          <p class="light mb-2"><strong>Horário de funcionamento: </strong>{{ $regional->funcionamento }}</p>
          <a href="{{ route('regionais.show', $regional->idregional) }}" class="btn-curso-grid">Detalhes</a>
        </div>
        @endforeach
      </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
  </div>
</section>



@endsection