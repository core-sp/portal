@extends('site.layout.app', ['title' => 'Concursos'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\Helpers\ConcursoHelper;
use Illuminate\Support\Facades\Input;
$modalidades = ConcursoHelper::modalidades();
$situacoes = ConcursoHelper::situacoes();
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/concursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Concursos
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-concursos">
  <div class="container">
    <div class="row pb-4" id="conteudo-principal">
      <div class="col">
        <form method="GET" role="form" action="/concursos/busca" class="pesquisaLicitacao">
          <div class="form-row text-center">
            <div class="m-auto">
              <h5 class="text-uppercase stronger marrom">Busca detalhada</h5>
            </div>
          </div>
          <div class="linha-lg-mini"></div>
          <div class="form-row mb-2">
          	<div class="col-md-6">
          	  <label for="modalidade">Modalidade</label>
          	  <select name="modalidade"
                class="form-control {{ !empty(Request::input('modalidade')) && in_array(Request::input('modalidade'), $modalidades) ? 'bg-focus border-info' : '' }}"
                id="modalidade">
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
          	<div class="col-md-6 mt-2-768">
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
          </div>
          <div class="form-row">
            <div class="col-md-4">
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
          	<div class="col-md-4 mt-2-768">
          	  <label for="datarealizacao">Data de Realização</label>
          	  <input type="text"
                class="form-control dataInput {{ !empty(Request::input('datarealizacao')) ? 'bg-focus border-info' : '' }}"
                name="datarealizacao"
                placeholder="dd/mm/aaaa"
                @if(!empty(Request::input('datarealizacao')))
                value="{{ Request::input('datarealizacao') }}"
                @endif
                />
          	</div>
            <div class="col-md-4 align-self-end pesquisaLicitacao-btn">
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
        @if(isset($concursos))
          @foreach($concursos as $concurso)
          <div class="licitacao-grid">
            <a href="/concurso/{{ $concurso->idconcurso }}">
              <div class="licitacao-grid-main">
                <h5 class="marrom">{{ $concurso->titulo }}</h5>
                <div class="linha-lg-mini"></div>
                <p>{!! Helper::resumo($concurso->objeto) !!}</p>
                <div class="mt-3 row bot-lg">
                  <div class="col-sm-4 d-flex">
                    <div class="mr-2">
                      <i class="far fa-file-alt"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Processo:</strong> {{ $concurso->nrprocesso }}
                      </h6>
                    </div>
                  </div>
                  <div class="col-sm-4 d-flex">
                    <div class="mr-2">
                      <i class="far fa-clock"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Divulgação:</strong> {{ Helper::onlyDate($concurso->created_at) }}<br />
                        <strong>Realizacao:</strong> {{ Helper::onlyDate($concurso->datarealizacao) }}
                      </h6>
                    </div>
                  </div>
                  <div class="col-sm-4 d-flex">
                    <div class="mr-2">
                      <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="flex-one align-self-center">
                      <h6 class="light">
                        <strong>Modalidade:</strong> {{ $concurso->modalidade }}<br />
                        <strong>Situação:</strong> {{ $concurso->situacao }}
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
              <div class="licitacao-grid-bottom">
                <div class="col">
                  <div class="text-right">
                    <h6 class="light marrom"><strong>Atualizado em:</strong> {{ Helper::onlyDate($concurso->updated_at) }}</h6>
                  </div>
                </div>
              </div>
            </a>
          </div>
          @endforeach
        @else
        <p>Nenhum concurso encontrado!</p>
        @endif
      </div>
    </div>
    @if(isset($concursos))
    <div class="row">
      <div class="col">
        <div class="float-right">
          {{ $concursos->links() }}
        </div>
      </div>
    </div>
    @endif
  </div>
</section>

@endsection