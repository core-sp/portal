@extends('admin.layout.app')

@section('content')

<section class="content-header pb-1">
  @if(\Session::has('message'))
    <div class="container-fluid mb-2">
      <div class="row">
        <div class="col">
          <div class="alert alert-dismissible {{ \Session::get('class') }}">
            {!! \Session::get('message') !!}
          </div>
        </div>
      </div>
    </div>
  @endif
  @if(!empty($alertas))
  	@include('admin.inc.alertashome')
  @endif
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
      	<h1>Painel de Administrador</h1>
      </div>
    </div>
	<div class="row mt-3">	  
		<div class="col">
			<div class="info-box">
				<span class="info-box-icon bg-danger">
					<i class="fas fa-user-clock"></i>
				</span>
				<div class="info-box-content">
					<span class="info-box-text">Agendamentos</span>
					<span class="info-box-number">{{ $totalAgendamentos }}</span>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="info-box">
				<span class="info-box-icon bg-warning">
					<i class="fas fa-chalkboard-teacher text-white"></i>
				</span>
				<div class="info-box-content">
					<span class="info-box-text">Inscrições em Cursos</span>
					<span class="info-box-number">{{ $totalInscritos }}</span>
				</div>
			</div>
		</div>
		<div class="col">
		@if(auth()->user()->idperfil == 1 || auth()->user()->idperfil == 3)
		<a href="/admin/newsletter/download" class="inherit">
		@endif
		<div class="info-box">
			<span class="info-box-icon bg-info">
				<i class="fas fa-newspaper"></i>
			</span>
			<div class="info-box-content">
				<span class="info-box-text inherit">Inscrições na Newsletter</span>
				<span class="info-box-number inherit d-inline">{{ $totalNewsletter }}</span>
				@if(auth()->user()->idperfil == 1 || auth()->user()->idperfil == 3)
				&nbsp;<span class="linkDownload d-inline">(Baixar CSV)</span>
				@endif
			</div>
		</div>
		@if(auth()->user()->idperfil == 1 || auth()->user()->idperfil == 3)
		</a>
		@endif
		</div>	 	
		@if(auth()->user()->idperfil == 1 || auth()->user()->idperfil == 3)
		<div class="col">
			<a href="{{route('termo.consentimento.download')}}" class="inherit">
				<div class="info-box">
					<span class="info-box-icon bg-info">
						<i class="fas fa-newspaper"></i>
					</span>
					<div class="info-box-content">
						<span class="info-box-text inherit">E-mails de aceite do Termo de Consentimento</span>
						<!-- <span class="info-box-number inherit d-inline">{{ $totalNewsletter }}</span> -->
						&nbsp;<span class="linkDownload d-inline">(Baixar CSV)</span>
					</div>
				</div>
			</a>
		</div>
		@endif
	</div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
  	<div class="row">

	  	@if(auth()->user()->isAdmin())
		<div class="col">
			<div class="card card-info">
				<div class="card-header">
					<h3 class="card-title">
						<i class="fas fa-hdd mr-1"></i> Storage em {{ ambiente() }}
						<small>
							<i class="fas fa-info-circle float-right mt-1" 
							data-toggle="popover" data-placement="top" data-trigger="hover click" data-html="true" 
							data-content="<b>Clique nos nomes da legenda para filtrar</b>">
							</i>
						</small>
					</h3>
					<p class="mb-0"><span id="total_storage"></span></p>
				</div>
				<div class="card-body text-center">
					<canvas class="grafico-storage spinner-grow spinner-grow-sm text-primary"></canvas>
				</div>
			</div>
			<script type="module" src="{{ asset('/js/interno/modulos/suporte.js?'.hashScriptJs()) }}" data-modulo-id="suporte" data-modulo-acao="visualizar"></script>
		</div>
		@endif

  	  <div class="col">
  	  	<div class="card card-info">
  	  	  <div class="card-header">
  	  	  	<h3 class="card-title">Conectado como: <strong>{{ auth()->user()->perfil->nome }}</strong></h3>
  	  	  </div>
  	  	  <div class="card-body">
			<p>Este é o painel de administrador do Portal do CORE-SP!</p>
			<a href="/" class="btn btn-success" target="_blank">VISITAR SITE</a>
			<a href="/admin/logout" class="btn btn-default ml-1">DESCONECTAR</a>
  	  	  </div>
		  <div class="card-footer">
		  	CORE-SP
		  </div>
  	  	</div>
  	  </div>
		<div class="col">
		<div class="card card-info">
			<div class="card-header">
				<div class="card-title">
					<h3 class="card-title">Informações úteis</h3>
				</div>
			</div>
			<div class="card-body">
				<p>- Para alterar sua senha, clique em seu nome de usuário no menu da esquerda e depois selecione "Alterar Senha";</p>
				{{-- <p class="mb-0">- Para dúvidas, sugestões, reclamações ou solicitações, envie sua mensagem para o CTI através <a href="/admin/chamados/criar">deste link</a>;</p> --}}
			</div>
		</div>
	  </div>
	  @if(in_array(auth()->user()->idperfil, [1, 6, 8, 10, 12, 13, 18, 21]))
	  <div class="col">
		<div class="card card-info">
		  <div class="card-header">
		  	<h3 class="card-title">Atendimentos realizados em <strong>{{ auth()->user()->regional->regional }}</strong></h3>
		  </div>
		  <div class="card-body">
		  	<div class="row">
			  <div class="col">
				{!! $contagem !!}
			  </div>
			</div>
		  </div>
		</div>
	  </div>
	  @endif
  	</div>
	  @if($chamados->count())
		@include('admin.inc.chamadohome')
	  @endif
  </div>	
</section>

@endsection