@extends('site.layout.app', ['title' => 'Inscrição'])

@section('content')

@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\CursoInscritoController;
$datarealizacao = Helper::onlyDate($curso->datarealizacao);
$datatermino = Helper::onlyDate($curso->datatermino);
$now = now();
@endphp

<section id="pagina-cabecalho">
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
		@if(isset($curso))
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h2 class="stronger">{{ $curso->tipo }} - {{ $curso->tema }} ({{ $curso->idcurso }})</h2>
          </div>
          <div class="align-self-center">
            <a href="/curso/{{ $curso->idcurso }}" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
			<div class="row mb-4">
				<div class="col conteudo-txt">
				@if($curso->datatermino >= $now && $curso->publicado == 'Sim')
					<p>
						Inscrever-se em <strong>{{ $curso->tipo }} - {{ $curso->tema }}, </strong> turma <strong>{{ $curso->idcurso }},</strong> que acontecerá no dia <strong>{{ Helper::onlyDate($curso->datarealizacao) }}</strong>	às <strong>{{ Helper::onlyHour($curso->datarealizacao) }}</strong> 
						e terminará no dia <strong>{{ Helper::onlyDate($curso->datatermino) }}</strong>	às <strong>{{ Helper::onlyHour($curso->datatermino) }}</strong>.
					</p>
					<div class="mt-2">
						<form method="POST" role="form" class="inscricaoCurso">
						@csrf
						<input type="hidden" name="idcurso" value="{{ $curso->idcurso }}" />
						<div class="form-row">
							<div class="col-md-6">
								<label for="nome">Nome *</label>
								<input type="text"
									class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
									name="nome"
									value="{{ old('nome') }}"
									placeholder="Nome" />
								@if($errors->has('nome'))
									<div class="invalid-feedback">
										{{ $errors->first('nome') }}
									</div>
								@endif
							</div>
							<div class="col-md-6 mt-2-768">
								<label for="email">Email *</label>
								<input type="text"
									class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}""
									name="email"
									value="{{ old('email') }}"
									placeholder="Email" />
								@if($errors->has('email'))
									<div class="invalid-feedback">
										{{ $errors->first('email') }}
								</div>
								@endif
							</div>
						</div>
						<div class="form-row mt-2 mb-4">
							<div class="col-md-4">	
								<label for="cpf">CPF *</label>
								<input type="text"
									class="form-control cpfInput {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
									name="cpf"
									value="{{ old('cpf') }}"
									placeholder="CPF" />
								@if($errors->has('cpf'))
									<div class="invalid-feedback">
										{{ $errors->first('cpf') }}
									</div>
								@endif
							</div>
							<div class="col-md-4 mt-2-768">
								<label for="telefone">Telefone *</label>
								<input type="text"
									class="form-control celularInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
									name="telefone"
									value="{{ old('telefone') }}"
									placeholder="Telefone" />
								@if($errors->has('telefone'))
									<div class="invalid-feedback">
										{{ $errors->first('telefone') }}
									</div>
								@endif
							</div>
							<div class="col-md-4 mt-2-768">
								<label for="registrocore">Nº de registro no CORE</label>
								<input type="text"
									class="form-control {{ $errors->has('registrocore') ? 'is-invalid' : '' }}"
									name="registrocore"
									value="{{ old('registrocore') }}"
									placeholder="Registro no CORE (opcional)" />
								@if($errors->has('registrocore'))
									<div class="invalid-feedback">
										{{ $errors->first('registrocore') }}
									</div>
								@endif
							</div>
						</div>
						<div class="form-check mt-3">
						<input type="checkbox"
							name="termo"
							class="form-check-input {{ $errors->has('termo') ? 'is-invalid' : '' }}"
							id="termo"
							{{ !empty(old('termo')) ? 'checked' : '' }}
							required
						/> 
						<label for="termo" class="textoTermo text-justify">
							Li e concordo com o <a href="{{ route('termo.consentimento.pdf') }}" target="_blank"><u>Termo de Consentimento</u></a>  de uso de dados, e estou ciente de que os meus dados serão utilizados apenas para notificações por e-mail a respeito da inscrição do curso.
						</label>
						@if($errors->has('termo'))
						<div class="invalid-feedback">
							{{ $errors->first('termo') }}
						</div>
						@endif
						</div>
						<div class="float-right">
							<a href="/curso/{{ $curso->idcurso }}" class="btn btn-default">Cancelar</a>
							<button type="submit" class="btn btn-primary">Inscrever-se</button>
						</div>
					</form>
				</div>
				@else
				<p>Houve algum problema.</p>
				@endif
			</div>
    </div>
		@else
			@include('site.inc.content-error')
		@endif
  </div>
</section>

@endsection