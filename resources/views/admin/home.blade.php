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
		@if(session('idperfil') === 1 || session('idperfil') === 3)
		<a href="/admin/newsletter/download" class="inherit">
		@endif
		<div class="info-box">
			<span class="info-box-icon bg-info">
				<i class="fas fa-newspaper"></i>
			</span>
			<div class="info-box-content">
				<span class="info-box-text inherit">Inscrições na Newsletter</span>
				<span class="info-box-number inherit d-inline">{{ $totalNewsletter }}</span>
				@if(session('idperfil') === 1 || session('idperfil') === 3)
				&nbsp;<span class="linkDownload d-inline">(Baixar CSV)</span>
				@endif
			</div>
		</div>
		@if(session('idperfil') === 1 || session('idperfil') === 3)
		</a>
		@endif
		</div>	  
	</div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
  	<div class="row">
  	  <div class="col">
  	  	<div class="card card-info">
  	  	  <div class="card-header">
  	  	  	<h3 class="card-title">Conectado como: <strong>{{ session('perfil') }}</strong></h3>
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
	  @if(session('idperfil') === 6 || session('idperfil') === 1 || session('idperfil') === 12)
	  <div class="col">
		<div class="card card-info">
		  <div class="card-header">
		  	<h3 class="card-title">Atendimentos realizados</h3>
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
	  @else
	  <div class="col">
		<div class="card card-info">
			<div class="card-header">
				<div class="card-title">
					<h3 class="card-title">Informações úteis</h3>
				</div>
			</div>
			<div class="card-body">
				<p>- Para alterar sua senha, clique em seu nome de usuário no menu da esquerda e depois selecione "Alterar Senha";</p>
				<p class="mb-0">- Para dúvidas, sugestões, reclamações ou solicitações, envie sua mensagem para o CTI através <a href="/admin/chamados/criar">deste link</a>;</p>
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