@extends('layout.app', ['title' => 'Inscrição'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
$datarealizacao = Helper::onlyDate($curso->datarealizacao);
$datatermino = Helper::onlyDate($curso->datatermino);
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Inscrição
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacao">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h4 class="stronger">{{ $curso->tipo }} - {{ $curso->tema }} ({{ $curso->idcurso }})</h4>
          </div>
          <div class="align-self-center">
            <a href="/curso/{{ $curso->idcurso }}" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-4 mb-4">
      <div class="col conteudo-txt">
		<p>Inscrever-se em <strong>{{ $curso->tipo }} - {{ $curso->tema }},
			</strong> turma <strong>{{ $curso->idcurso }},</strong>
			que acontecerá no dia <strong>{{ Helper::onlyDate($curso->datarealizacao) }}</strong>
			 às <strong>{{ Helper::onlyHour($curso->datarealizacao) }}</strong></p>
		<div class="mt-2">
		  <form method="POST" role="form" class="inscricaoCurso">
			@csrf
			<input type="hidden" name="idcurso" value="{{ $curso->idcurso }}" />
			<div class="form-row">
			  <div class="col">
				<label for="nome">Nome *</label>
				<input type="text"
					class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}""
					name="nome"
					placeholder="Nome" />
				@if($errors->has('nome'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                  </div>
                @endif
			  </div>
			  <div class="col">
				<label for="email">Email *</label>
				<input type="text"
					class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}""
					name="email"
					placeholder="Email" />
				@if($errors->has('email'))
                  <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                  </div>
                @endif
			  </div>
			</div>
			<div class="form-row mt-2 mb-4">
			  <div class="col">
				<label for="cpf">CPF *</label>
				<input type="text"
					class="form-control {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
					name="cpf"
					placeholder="CPF" />
				@if($errors->has('cpf'))
                  <div class="invalid-feedback">
                    {{ $errors->first('cpf') }}
                  </div>
                @endif
			  </div>
			  <div class="col">
				<label for="telefone">Telefone *</label>
				<input type="text"
					class="form-control {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
					name="telefone"
					placeholder="Telefone" />
				@if($errors->has('telefone'))
                  <div class="invalid-feedback">
                    {{ $errors->first('telefone') }}
                  </div>
                @endif
			  </div>
			  <div class="col">
				<label for="registrocore">Nº de registro no CORE</label>
				<input type="text"
					class="form-control {{ $errors->has('registrocore') ? 'is-invalid' : '' }}"
					name="registrocore"
					placeholder="Registro no CORE (opcional)" />
				@if($errors->has('registrocore'))
                  <div class="invalid-feedback">
                    {{ $errors->first('registrocore') }}
                  </div>
                @endif
			  </div>
			</div>
			<div class="float-right">
			  <a href="/curso/{{ $curso->idcurso }}" class="btn btn-default">Cancelar</a>
			  <button type="submit" class="btn btn-primary">Inscrever-se</button>
			</div>
		  </form>
		</div>
	  </div>
    </div>
  </div>
</section>

@endsection