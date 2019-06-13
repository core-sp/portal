@extends('admin.layout.app')

@section('content')

@php
	use App\Http\Controllers\NewsletterController;
	use App\Http\Controllers\ControleController;
	use App\Http\Controllers\Helper;
	$alertas = App\Http\Controllers\AdminController::alertas();
	$chamados = App\Http\Controllers\Helpers\ChamadoControllerHelper::getByUser(Auth::user()->idusuario);
@endphp

<section class="content-header">
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
  	<div class="container-fluid mb-2">
	  @if(isset($alertas['agendamentoCount']))
      <div class="row">
        <div class="col">
          <div class="alert alert-warning">
		  	@if($alertas['agendamentoCount'] === 1)
			  <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Existe <strong>1</strong> atendimento pendente de validação! (AGENDAMENTO)
			@else
				<i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Existem <strong>{{ $alertas['agendamentoCount'] }}</strong> atendimentos pendentes de validação! (AGENDAMENTO)
			@endif
          </div>
        </div>
      </div>
	  @endif
    </div>
  @endif
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
      	<h1>Home</h1>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
  	<div class="row">
  	  <div class="col-sm">
  	  	<div class="card card-info">
  	  	  <div class="card-header">
  	  	  	<h3 class="card-title">Conectado como: <strong>{{ session('perfil') }}</strong></h3>
  	  	  </div>
  	  	  <div class="card-body">
  	  	  	Seja bem-vindo ao novo Portal do CORE-SP!
  	  	  </div>
  	  	  <div class="card-footer">
  	  	  	CORE-SP
  	  	  </div>
  	  	</div>
  	  </div>
	  @if(ControleController::mostra('NewsletterController','index'))
		  <div class="col-6">
		  	<div class="card card-info">
			  <div class="card-header">
			  	<h3 class="card-title">Newsletter</h3>
			  </div>
			  <div class="card-body">
			    <div class="row">
				  <div class="col">
					<div class="info-box">
					  <span class="info-box-icon bg-info">
						<i class="fas fa-users"></i>
					  </span>
					  <div class="info-box-content">
						<span class="info-box-text">Total de registros</span>
						<span class="info-box-number">{{ NewsletterController::countNewsletter() }}</span>
					  </div>
					</div>
				  </div>
				  <div class="col">
				  	<a href="/admin/newsletter/download" class="nodecoration">
					  <div class="info-box">
						<span class="info-box-icon bg-info">
							<i class="far fa-file-excel"></i>
						</span>
						<div class="info-box-content">
							<span class="info-box-text">Planilha</span>
							<span class="info-box-number">Baixar CSV</span>
						</div>
						</div>
					</a>
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