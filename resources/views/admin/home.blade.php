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
	  @include('admin.inc.clipshome')
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