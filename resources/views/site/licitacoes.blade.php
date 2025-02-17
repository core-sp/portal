@extends('site.layout.app', ['title' => 'Licitações e Aquisições'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/licitacoes.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Licitações e Aquisições
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
                class="form-control {{ !empty(request()->query('palavrachave')) ? 'bg-focus border-info' : '' }}"
                placeholder="Insira uma palavra-chave"
                value="{{ !empty(request()->query('palavrachave')) ? request()->query('palavrachave') : '' }}"
              />
            </div>
          	<div class="col-md-4">
          	  <label for="modalidade">Modalidade</label>
          	  <select name="modalidade" 
                class="form-control {{ !empty(request()->query('modalidade')) && in_array(request()->query('modalidade'), $modalidades) ? 'bg-focus border-info' : '' }} {{ $errors->has('modalidade') ? 'is-invalid' : '' }}" 
                id="modalidade"
              >
          	  	<option value="">Todas</option>
	         	    @foreach($modalidades as $modalidade)
                  <option value="{{ $modalidade }}" {{ $modalidade == request()->query('modalidade') ? 'selected' : '' }}>{{ $modalidade }}</option>
	          	  @endforeach
	            </select>
              @if($errors->has('modalidade'))
              <div class="invalid-feedback">
                  {{ $errors->first('modalidade') }}
              </div>
              @endif
          	</div>
          	<div class="col-md-2 col-sm-6 mt-2-768">
          	  <label for="nrprocesso">Nº do Processo Adm.</label>
          	  <input type="text"
                name="nrprocesso"
                class="form-control nrprocessoInput {{ !empty(request()->query('nrprocesso')) ? 'bg-focus border-info' : '' }} {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}"
                placeholder="Nº do Processo Adm."
                id="nrprocesso"
                value="{{ !empty(request()->query('nrprocesso')) ? request()->query('nrprocesso') : '' }}"
              />
              @if($errors->has('nrprocesso'))
              <div class="invalid-feedback">
                {{ $errors->first('nrprocesso') }}
              </div>
              @endif
          	</div>
          	<div class="col-md-2 col-sm-6 mt-2-768">
          	  <label for="nrlicitacao">Nº da Licitação</label>
          	  <input type="text"
                name="nrlicitacao"
                class="form-control nrlicitacaoInput {{ !empty(request()->query('nrlicitacao')) ? 'bg-focus border-info' : '' }} {{ $errors->has('nrlicitacao') ? 'is-invalid' : '' }}"
                placeholder="Nº da Licitação"
                id="nrlicitacao"
                value="{{ !empty(request()->query('nrlicitacao')) ? request()->query('nrlicitacao') : '' }}"
              />
              @if($errors->has('nrlicitacao'))
              <div class="invalid-feedback">
                {{ $errors->first('nrlicitacao') }}
              </div>
              @endif
          	</div>
          </div>
          <div class="form-row">
          	<div class="col-lg-4 col-md-6">
          	  <label for="situacao">Situação</label>
          	  <select name="situacao" 
                class="form-control {{ !empty(request()->query('situacao')) && in_array(request()->query('situacao'), $situacoes) ? 'bg-focus border-info' : '' }} {{ $errors->has('situacao') ? 'is-invalid' : '' }}" 
                id="situacao"
              >
          	  	<option value="">Qualquer</option>
	         	    @foreach($situacoes as $situacao)
                 <option value="{{ $situacao }}" {{ $situacao == request()->query('situacao') ? 'selected' : '' }}>{{ $situacao }}</option>
	          	  @endforeach
	            </select>
              @if($errors->has('situacao'))
              <div class="invalid-feedback">
                {{ $errors->first('situacao') }}
              </div>
            @endif
          	</div>
          	<div class="col-lg-4 col-md-6 mt-2-768">
          	  <label for="datarealizacao">Data de Realização</label>
          	  <input type="date"
                class="form-control {{ !empty(request()->query('datarealizacao')) ? 'bg-focus border-info' : '' }} {{ $errors->has('datarealizacao') ? 'is-invalid' : '' }}"
                name="datarealizacao"
                value="{{ !empty(request()->query('datarealizacao')) ? request()->query('datarealizacao') : '' }}"
              />
              @if($errors->has('datarealizacao'))
              <div class="invalid-feedback">
                {{ $errors->first('datarealizacao') }}
              </div>
              @endif
          	</div>
            <div class="col-lg-4 col-md-12 align-self-end pesquisaLicitacao-btn">
              <button type="submit" class="btn-buscaavancada loadingPagina"><i class="fas fa-search"></i>&nbsp;&nbsp;Pesquisar</button>
              <a href="{{ route('licitacoes.siteGrid') }}" class="btn btn-limpar"><i class="fas fa-times"></i>&nbsp;&nbsp;Limpar</a>
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
        @if(isset($licitacoes) && ($licitacoes->total() > 0))
          @foreach($licitacoes as $licitacao)
          <div class="licitacao-grid">
            <a href="{{ route('licitacoes.show', $licitacao->idlicitacao) }}">
              <div class="licitacao-grid-main">
                <h5 class="marrom text-break">{{ $licitacao->titulo }}</h5>
                <div class="linha-lg-mini"></div>
                <p class="text-break">{!! resumo($licitacao->objeto) !!}</p>
                <div class="mt-3 row bot-lg">
                  <div class="col-sm-4 d-flex mb-2-576">
                    <div class="mr-2">
                      <i class="far fa-file-alt"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Nº do Processo Adm.:</strong> {{ $licitacao->nrprocesso }}<br />
                        <strong>Nº da Licitação:</strong> {{ $licitacao->nrlicitacao }}
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
        <p>Nenhuma licitação encontrada!</p>
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