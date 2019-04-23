@extends('admin.layout.app')

@section('content')

@php
use App\Http\Controllers\NewsletterController;
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
  	  	<div class="card">
  	  	  <div class="card-header">
  	  	  	<h3 class="card-title">
							Conectado como:
							<strong>
								{{ Auth::user()->perfil[0]->nome }}
							</strong>
	  	  		</h3>
  	  	  </div>
  	  	  <div class="card-body">
  	  	  	Seja bem-vindo ao novo Portal do CORE-SP!
  	  	  </div>
  	  	  <div class="card-footer">
  	  	  	CORE-SP
  	  	  </div>
  	  	</div>
  	  </div>
			<div class="col">
				<div class="card">
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
  	</div>
		@if(Auth::user()->hasAnyRole(['admin', 'editor']))
		<div class="row">
		 	<div class="col">
			  <div class="card">
					<div class="card-header">
						<div class="card-title">
							<h3 class="card-title">Newsletter</h3>
						</div>
					</div>
					<div class="card-body row">
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
							<div class="info-box">
								<span class="info-box-icon bg-info">
									<i class="fas fa-calendar-alt"></i>
								</span>
								<div class="info-box-content">
									<span class="info-box-text">Registros na última semana</span>
									<span class="info-box-number">{{ NewsletterController::countNewsletterLastWeek() }}</span>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="info-box">
								<span class="info-box-icon bg-info">
									<i class="far fa-file-excel"></i>
								</span>
								<div class="info-box-content">
									<span class="info-box-text">Planilha (em breve)</span>
									<span class="info-box-number">Gerar planilha de registrados</span>
								</div>
							</div>
						</div>
					</div>
				</div> 
			</div>
		</div>
		@endif
  </div>	
</section>

@endsection