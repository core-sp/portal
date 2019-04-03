@extends('layout.app', ['title' => 'Balcão de Oportunidades'])

@section('content')

@php
use \App\Http\Controllers\Helpers\BdoOportunidadeControllerHelper;
use \App\Http\Controllers\BdoSiteController;
$segmentos = BdoOportunidadeControllerHelper::segmentos();
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/bdo.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Balcão de Oportunidades
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-bdo">
  <div class="container">
    <div class="row pb-4">
      <div class="col">
        <form method="GET" role="form" action="/balcao-de-oportunidades/busca" class="pesquisaLicitacao">
          <div class="form-row text-center mb-2">
            <div class="m-auto">
              <h5 class="text-uppercase stronger marrom">Busca detalhada</h5>
            </div>
          </div>
          <div class="linha-lg"></div>
          <div class="form-row">
            <div class="col">
              <label for="palavrachave">Palavra-chave</label>
              <input type="text" name="palavrachave" class="form-control" placeholder="Palavra chave" id="palavrachave">
            </div>
            <div class="col">
              <label for="segmento">Segmento</label>
              <select name="segmento" class="form-control" id="segmento">
                <option value="">Todos</option>
                @foreach($segmentos as $segmento)
                <option value="{{ $segmento }}">{{ $segmento }}</option>
                @endforeach
              </select>
            </div>
            <div class="col align-self-end pesquisaLicitacao-btn">
              <button type="submit" class="btn-buscaavancada"><i class="fas fa-search"></i>&nbsp;&nbsp;Pesquisar</button>
              <button type="reset" class="btn-limpar"><i class="fas fa-times"></i>&nbsp;&nbsp;Limpar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <div class="linha-cinza"></div>
    </div>
  </div>
  <div class="container">
    <div class="row mt-4">
      <div class="col">
        @if(isset($oportunidades))
          @foreach($oportunidades as $oportunidade)
          <div class="licitacao-grid">
            <div class="licitacao-grid-main">
              <h5 class="marrom mb-1">{{ $oportunidade->titulo }}</h5>
              <h6 class="light">
                <i class="far fa-building"></i>&nbsp;&nbsp;{{ $oportunidade->empresa->razaosocial }}&nbsp;&nbsp;&nbsp;&nbsp;
                <i class="fas fa-map-marker-alt"></i>&nbsp;&nbsp;{{ $oportunidade->regiaoatuacao }}&nbsp;&nbsp;&nbsp;&nbsp;
                <i class="fas fa-suitcase"></i>&nbsp;&nbsp;{{ $oportunidade->vagasdisponiveis }} vagas
              </h6>
              <div class="linha-lg"></div>
              <p>{{ $oportunidade->descricao }}</p>
              <div class="bdo-info">
                <div class="row pt-2 pb-2">
                  <div class="col pad-rig-zero">
                    <div class="row nomargin br h-100">
                      <div class="linha-h"></div>
                      <div>
                        <i class="far fa-address-card"></i>
                      </div>
                      <div class="pt-1 flex-one">
                        <h4 class="text-uppercase pl-3 pr-3 pb-1">Contato</h4>
                        <div class="pl-3 pt-2 pr-3">
                          <p><strong>Nome:</strong> {{ $oportunidade->empresa->contatonome }}</p>
                          <p><strong>Email:</strong> {{ $oportunidade->empresa->contatoemail }}</p>
                          <p><strong>Telefone:</strong> {{ $oportunidade->empresa->contatotelefone }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col pad-lef-zero pad-rig-zero">
                    <div class="row nomargin bc h-100">
                      <div>
                        <i class="far fa-building"></i>
                      </div>
                      <div class="pt-1 flex-one">
                        <h4 class="text-uppercase pl-3 pr-3 pb-1">Empresa</h4>
                        <div class="pl-3 pt-2 pr-3">
                          <p><strong>Empresa:</strong> {{ $oportunidade->empresa->razaosocial }}</p>
                          <p><strong>Endereço:</strong> {{ $oportunidade->empresa->endereco }}</p>
                          <p><strong>Email:</strong> {{ $oportunidade->empresa->email }}</p>
                          <p><strong>Telefone:</strong> {{ $oportunidade->empresa->telefone }}</p>
                          <p><strong>Website:</strong> {{ $oportunidade->empresa->site }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col pad-lef-zero">
                    <div class="row nomargin bl h-100">
                      <div>
                        <i class="fas fa-briefcase"></i>
                      </div>
                      <div class="pt-1 flex-one">
                        <h4 class="text-uppercase pl-3 pr-3 pb-1">Oportunidade</h4>
                        <div class="pl-3 pt-2 pr-3">
                          <p><strong>Segmento:</strong> {{ $oportunidade->segmento }}</p>
                          <p><strong>Inclusão:</strong> {{ BdoOportunidadeControllerHelper::onlyDate($oportunidade->created_at) }}</p>
                          <p><strong>Status:</strong> {{ $oportunidade->status }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <button class="saiba-mais mt-3"><i class="fas fa-angle-double-down"></i>&nbsp;&nbsp;Mais Detalhes</button>
            </div>
            <div class="licitacao-grid-bottom">
              <div class="col nopadding">
                <div class="text-right">
                  <h6 class="light marrom"><strong>Atualizado em:</strong> {{ BdoOportunidadeControllerHelper::onlyDate($oportunidade->updated_at) }}</h6>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        @else
        <p>Nenhuma oportunidade encontrada!</p>
        @endif
      </div>
    </div>
    <div class="row mt-3">
      @if(isset($oportunidades))
      {{ $oportunidades->links() }}
      @endif
    </div>
  </div>
</section>

@endsection