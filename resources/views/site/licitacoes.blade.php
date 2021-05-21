@php
  use \App\Licitacao;

  $modalidades = Licitacao::modalidadesLicitacao();
  $situacoes = Licitacao::situacoesLicitacao();
@endphp

@extends('site.layout.app', ['title' => 'Licitações'])

@section('content')

<section id="pagina-cabecalho">
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
        <form method="GET" role="form" action="{{ route('licitacoes.siteBusca') }}" enctype="multipart/form-data" class="pesquisaLicitacao">
          <div class="form-row text-center">
            <div class="m-auto">
              <h5 class="text-uppercase stronger marrom">Busca detalhada</h5>
            </div>
          </div>
          <div class="linha-lg-mini"></div>
          <div class="form-row mb-2">
            <div class="col-md-4">
              <label for="palavrachave">Palavra-chave</label>
              <input type="text"
                name="palavrachave"
                class="form-control {{ !empty(Request::input('palavrachave')) ? 'bg-focus border-info' : '' }}"
                placeholder="Insira uma palavrachave"
                @if(!empty(Request::input('palavrachave')))
                value="{{ Request::input('palavrachave') }}"
                @endif
                />
            </div>
          	<div class="col-md-4">
          	  <label for="modalidade">Modalidade</label>
          	  <select name="modalidade" class="form-control {{ !empty(Request::input('modalidade')) && in_array(Request::input('modalidade'), $modalidades) ? 'bg-focus border-info' : '' }}" id="modalidade">
          	  	<option value="">Todas</option>
	         	    @foreach($modalidades as $modalidade)
                  @if($modalidade === Request::input('modalidade'))
                  <option value="{{ $modalidade }}" selected>{{ $modalidade }}</option>
                  @else
	         	      <option value="{{ $modalidade }}">{{ $modalidade }}</option>
                  @endif
	          	  @endforeach
	            </select>
          	</div>
          	<div class="col-md-2 col-sm-6 mt-2-768">
          	  <label for="nrprocesso">Nº do processo</label>
          	  <input type="text"
                name="nrprocesso"
                class="form-control nrprocessoInput {{ !empty(Request::input('nrprocesso')) ? 'bg-focus border-info' : '' }}"
                placeholder="Nº do processo"
                id="nrprocesso"
                @if(!empty(Request::input('nrprocesso')))
                value="{{ Request::input('nrprocesso') }}"
                @endif
                />
          	</div>
          	<div class="col-md-2 col-sm-6 mt-2-768">
          	  <label for="nrlicitacao">Nº da Licitação</label>
          	  <input type="text"
                name="nrlicitacao"
                class="form-control nrlicitacaoInput {{ !empty(Request::input('nrlicitacao')) ? 'bg-focus border-info' : '' }}"
                placeholder="Nº da licitação"
                id="nrlicitacao"
                @if(!empty(Request::input('nrlicitacao')))
                value="{{ Request::input('nrlicitacao') }}"
                @endif
                />
          	</div>
          </div>
          <div class="form-row">
          	<div class="col-lg-4 col-md-6">
          	  <label for="situacao">Situação</label>
          	  <select name="situacao" class="form-control {{ !empty(Request::input('situacao')) && in_array(Request::input('situacao'), $situacoes) ? 'bg-focus border-info' : '' }}" id="situacao">
          	  	<option value="">Qualquer</option>
	         	    @foreach($situacoes as $situacao)
                  @if($situacao === Request::input('situacao'))
                  <option value="{{ $situacao }}" selected>{{ $situacao }}</option>
                  @else
   	         	    <option value="{{ $situacao }}">{{ $situacao }}</option>
                  @endif
	          	  @endforeach
	            </select>
          	</div>
          	<div class="col-lg-4 col-md-6 mt-2-768">
          	  <label for="datarealizacao">Data de Realização</label>
          	  <input type="text"
                class="form-control dataInput {{ !empty(Request::input('datarealizacao')) ? 'bg-focus border-info' : '' }}"
                placeholder="dd/mm/aaaa"
                name="datarealizacao"
                @if(!empty(Request::input('datarealizacao')))
                value="{{ Request::input('datarealizacao') }}"
                @endif
                />
          	</div>
            <div class="col-lg-4 col-md-12 align-self-end pesquisaLicitacao-btn">
              <button type="submit" class="btn-buscaavancada"><i class="fas fa-search"></i>&nbsp;&nbsp;Pesquisar</button>
              <a href="/licitacoes" class="btn btn-limpar"><i class="fas fa-times"></i>&nbsp;&nbsp;Limpar</a>
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
            <a href="{{ route('licitacoes.show', $licitacao->idlicitacao) }}">
              <div class="licitacao-grid-main">
                <h5 class="marrom">{{ $licitacao->titulo }}</h5>
                <div class="linha-lg-mini"></div>
                <p>{!! resumo($licitacao->objeto) !!}</p>
                <div class="mt-3 row bot-lg">
                  <div class="col-sm-4 d-flex mb-2-576">
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
                  <div class="col-sm-4 d-flex mb-2-576">
                    <div class="mr-2">
                      <i class="far fa-clock"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Divulgação:</strong> {{ onlyDate($licitacao->created_at) }}<br />
                        <strong>Realizacao:</strong> {{ onlyDate($licitacao->datarealizacao) }}
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
                        <strong>Situação:</strong> {{ btnSituacao($licitacao->situacao) }}
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
              <div class="licitacao-grid-bottom">
                <div class="col">
                  <div class="text-right">
                    <h6 class="light marrom"><strong>Atualizado em:</strong> {{ onlyDate($licitacao->updated_at) }}</h6>
                  </div>
                </div>
              </div>
            </a>
          </div>
          @endforeach
        @else
        @if(isset($erro))
        <p>{{ $erro }}</p>  
        @else
        <p>Nenhuma licitação encontrada!</p>
        @endif
        @endif
      </div>
    </div>
    @if(isset($licitacoes))
    <div class="row">
      <div class="col">
        <div class="float-right">
          {{ $licitacoes->appends(request()->input())->links() }}
        </div>
      </div>
    </div>
    @endif
  </div>
</section>

@endsection