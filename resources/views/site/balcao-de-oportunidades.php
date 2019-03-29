@extends('layout.app', ['title' => 'Licitações'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/balcao-de-oportunidades.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Balcão de Oportunidades
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-conteudo">
  <div class="container">
    <div class="row">
      <div class="col">
        <form method="GET" role="form" action="/balcao-de-oportunidades/busca">
          <div class="form-row mb-3">
          	<div class="col">
          	  <label for="modalidade">Modalidade</label>
          	  <select name="modalidade" class="form-control" id="modalidade">
          	  	<option value="">Todas</option>
	         	    @foreach($modalidades as $modalidade)
	         	    <option value="{{ $modalidade }}">{{ $modalidade }}</option>
	          	  @endforeach
	            </select>
          	</div>
          	<div class="col">
          	  <label for="nrprocesso">Nº do processo</label>
          	  <input type="text" name="nrprocesso" class="form-control" placeholder="Nº do processo" id="nrprocesso">
          	</div>
          	<div class="col">
          	  <label for="nrlicitacao">Nº da Licitação</label>
          	  <input type="text" name="nrlicitacao" class="form-control" placeholder="Nº da licitação" id="nrlicitacao">
          	</div>
          </div>
          <div class="form-row mb-3">
          	<div class="col">
          	  <label for="situacao">Situação</label>
          	  <select name="situacao" class="form-control" id="situacao">
          	  	<option value="">Qualquer</option>
    	         	@foreach($situacoes as $situacao)
    	         	<option value="{{ $situacao }}">{{ $situacao }}</option>
	          	  @endforeach
	            </select>
          	</div>
          	<div class="col">
          	  <label for="datarealizacao">Date de Realização</label>
          	  <input type="date" class="form-control" name="datarealizacao">
          	</div>
          </div>
          <div class="form-row">
          	<div class="col">
          	  <button type="submit" class="btn btn-primary">Pesquisar</button>
          	</div>
          </div>
        </form>
      </div>
    </div>
    @if(isset($busca))
    <div class="row mt-4">
      <div class="col">
      	@if(isset($licitacoes))
      	<table class="table table-hover">
      	  <thead>
      	  	<tr>
      	  	  <th>Modalidade</th>
      	  	  <th>Situação</th>
      	  	  <th>Data de Realização</th>
      	  	  <th>Nº da Licitação</th>
      	  	  <th>Nº do Processo</th>
      	  	  <th></th>
      	  	</tr>
      	  </thead>
      	  <tbody>
      	  	@foreach($licitacoes as $licitacao)
      	  	<tr>
      	  	  <td>{{ $licitacao->modalidade }}</td>
      	  	  <td>{{ $licitacao->situacao }}</td>
      	  	  <td>{{ LicitacaoHelper::onlyDate($licitacao->datarealizacao) }}</td>
      	  	  <td>{{ $licitacao->nrlicitacao }}</td>
      	  	  <td>{{ $licitacao->nrprocesso }}</td>
      	  	  <td>
      	  	  	<a href="/licitacao/{{ $licitacao->idlicitacao }}" class="btn btn-sm btn-primary">Info</a>
      	  	  </td>
      	  	</tr>
      	  	@endforeach
      	  </tbody>
      	</table>
      	@else
      	<p>Nenhuma licitação encontrada!</p>
      	@endif
      </div>
    </div>
    @endif
  </div>
</section>

@endsection