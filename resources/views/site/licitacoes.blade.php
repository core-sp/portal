@extends('site.layout.app', ['title' => 'Licitações'])

@section('content')

@php
use \App\Http\Controllers\Helpers\LicitacaoHelper;
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\LicitacaoSiteController;
$modalidades = LicitacaoHelper::modalidades();
$situacoes = LicitacaoHelper::situacoes();
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/licitacoes.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Licitações
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacoes">
  <div class="container">
    <div class="row pb-4" id="conteudo-principal">
      <div class="col">
        <form method="GET" role="form" action="/licitacoes/busca" class="pesquisaLicitacao">
          <div class="form-row text-center">
            <div class="m-auto">
              <h5 class="text-uppercase stronger marrom">Busca detalhada</h5>
            </div>
          </div>
          <div class="linha-lg-mini"></div>
          <div class="form-row mb-2">
          	<div class="col-md-6">
          	  <label for="modalidade">Modalidade</label>
          	  <select name="modalidade" class="form-control" id="modalidade">
          	  	<option value="">Todas</option>
	         	    @foreach($modalidades as $modalidade)
	         	    <option value="{{ $modalidade }}">{{ $modalidade }}</option>
	          	  @endforeach
	            </select>
          	</div>
          	<div class="col-md-3">
          	  <label for="nrprocesso">Nº do processo</label>
          	  <input type="text" name="nrprocesso" class="form-control nrprocessoInput" placeholder="Nº do processo" id="nrprocesso">
          	</div>
          	<div class="col-md-3">
          	  <label for="nrlicitacao">Nº da Licitação</label>
          	  <input type="text" name="nrlicitacao" class="form-control nrlicitacaoInput" placeholder="Nº da licitação" id="nrlicitacao">
          	</div>
          </div>
          <div class="form-row">
          	<div class="col-lg-5 col-md-6">
          	  <label for="situacao">Situação</label>
          	  <select name="situacao" class="form-control" id="situacao">
          	  	<option value="">Qualquer</option>
	         	    @foreach($situacoes as $situacao)
	         	    <option value="{{ $situacao }}">{{ $situacao }}</option>
	          	  @endforeach
	            </select>
          	</div>
          	<div class="col-lg-3 col-md-6">
          	  <label for="datarealizacao">Data de Realização</label>
          	  <input type="text" class="form-control dataInput" placeholder="dd/mm/aaaa" name="datarealizacao">
          	</div>
            <div class="col-lg-4 col-md-12 align-self-end pesquisaLicitacao-btn">
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
        @if(isset($licitacoes))
          @foreach($licitacoes as $licitacao)
          <div class="licitacao-grid">
            <a href="/licitacao/{{ $licitacao->idlicitacao }}">
              <div class="licitacao-grid-main">
                <h5 class="marrom">{{ $licitacao->titulo }}</h5>
                <div class="linha-lg-mini"></div>
                <p>{!! Helper::resumo($licitacao->objeto) !!}</p>
                <div class="mt-3 row bot-lg">
                  <div class="col-sm-4 d-flex">
                    <div class="mr-2">
                      <i class="far fa-file-alt"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Número:</strong> {{ $licitacao->nrprocesso }}<br />
                        <strong>Processo:</strong> {{ $licitacao->nrlicitacao }}
                      </h6>
                    </div>
                  </div>
                  <div class="col-sm-4 d-flex">
                    <div class="mr-2">
                      <i class="far fa-clock"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Divulgação:</strong> {{ LicitacaoHelper::onlyDate($licitacao->created_at) }}<br />
                        <strong>Realizacao:</strong> {{ LicitacaoHelper::onlyDate($licitacao->datarealizacao) }}
                      </h6>
                    </div>
                  </div>
                  <div class="col-sm-4 d-flex">
                    <div class="mr-2">
                      <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Modalidade:</strong> {{ $licitacao->modalidade }}<br />
                        <strong>Situação:</strong> {{ Helper::btnSituacao($licitacao->situacao) }}
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
              <div class="licitacao-grid-bottom">
                <div class="col">
                  <div class="text-right">
                    <h6 class="light marrom"><strong>Atualizado em:</strong> {{ LicitacaoHelper::onlyDate($licitacao->updated_at) }}</h6>
                  </div>
                </div>
              </div>
            </a>
          </div>
          @endforeach
        @else
        <p>Nenhuma licitação encontrada!</p>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection