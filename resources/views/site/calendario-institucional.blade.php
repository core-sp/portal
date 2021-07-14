@extends('site.layout.app', ['title' => 'Calendário Institucional'])

@section('description')
  <meta name="description" content="Calendário Institucional ooficial contendo a agenda do CORE-SP." />
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
        <h1 class="branco text-uppercase">
          Calendário Institucional
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-bdo">
  <div class="form-row justify-content-center">
    <div id="calendario-institucional"></div> 
  </div>

  <div class="container">
    <div class="row mt-4">
      <div class="col">
        @if(isset($oportunidades))
          @foreach($oportunidades as $oportunidade)
          <div class="licitacao-grid">
            <div class="licitacao-grid-main">
              <h5 class="marrom mb-1">Título do compromisso</h5>
              <h6 class="light">
                <i class="fas fa-clock"></i>&nbsp; 00h00 - 00h00
                &nbsp;&nbsp;&nbsp;
                <i class="fas fa-map-marker-alt"></i>&nbsp; Local
              </h6>
              <div class="linha-lg-mini"></div>
            
              <div class="bdo-info">
                <div class="row mt-3 bot-lg">
                  <div class="col d-flex">
                    <div class="flex-one">
                      <p>Descrição do compromisso</p>
                    </div>
                  </div>
                </div>
              </div>
              <button class="saiba-mais mt-3"><i class="fas fa-angle-double-down"></i>&nbsp;&nbsp;Mais Detalhes</button>
            </div>
          </div>
          @endforeach
        @else
        <p style="text-align: center;">Sem compromisso oficial</p>
        @endif
      </div>
    </div>
  </div>  

</section>

@endsection