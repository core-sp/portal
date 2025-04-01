@extends('site.layout.app', ['title' => 'Agenda Institucional'])

@section('description')
  <meta name="description" content="Agenda Institucional Oficial contendo a agenda do CORE-SP." />
@endsection

@section('content')

@php
use \App\BdoOportunidade;
@endphp

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/bdo.png') }}" />
    <div class="row position-absolute pagina-titulo" id="bdo-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">Agenda Institucional</h1>
        <h2 class="branco text-uppercase" id="data">{{ $data }}</h2>
      </div>
    </div>
  </div>
</section>

<section id="pagina-bdo">
  <div class="form-row justify-content-center">
    <div id="agenda-institucional"></div> 
  </div>

  <div class="container">
    <div class="row mt-4">
      <div class="col">
        @if(isset($resultados) && $resultados->count() > 0)
          @foreach($resultados as $compromisso)
          <div class="licitacao-grid">
            <div class="licitacao-grid-main">
              <h5 class="marrom mb-1">{{ $compromisso->titulo }}</h5>
              <h6 class="light">
                <i class="fas fa-clock"></i>&nbsp; {{ onlyHour($compromisso->horarioinicio) }} - {{ onlyHour($compromisso->horariotermino) }}
                &nbsp;&nbsp;&nbsp;
                <i class="fas fa-map-marker-alt"></i>&nbsp; {{ $compromisso->local }}
              </h6>
              <div class="saiba-mais-info">
                <div class="row mt-3 bot-lg">
                  <div class="col d-flex">
                    <div class="flex-one">
                      <p>{{ $compromisso->descricao }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <button class="saiba-mais mt-3"><i class="fas fa-angle-double-down"></i>&nbsp;&nbsp;Mais Detalhes</button>
            </div>
          </div>
          @endforeach
        @else
        <p class="text-center">Sem compromisso oficial</p>
        @endif
      </div>
    </div>
  </div>  
</section>

<script type="module" src="{{ asset('/js/externo/modulos/agenda-institucional.js?'.hashScriptJs()) }}" id="modulo-agenda-institucional" class="modulo-visualizar"></script>

@endsection