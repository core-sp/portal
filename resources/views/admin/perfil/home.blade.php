@extends('admin.layout.app')

@section('content')

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
      	<h1>Perfil</h1>
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
  	  	  	<h3 class="card-title">
	  	  	  Informações do usuário
	  	  	</h3>
  	  	  </div>
  	  	  <div class="card-body">
			@if(Auth::check())
			@if(isset(Auth::user()->nome))	  
			<p class="mb-1"><strong>Nome Completo:</strong> {{ Auth::user()->nome }}</p>
			@endif
			@if(isset(Auth::user()->username))	
			<p class="mb-1"><strong>Nome de Usuário:</strong> {{ Auth::user()->username }}</p>
			@endif
			@if(isset(Auth::user()->email))	
			<p class="mb-1"><strong>Email:</strong> {{ Auth::user()->email }}</p>
			@endif
			@if(isset(Auth::user()->perfil->nome))	
			<p class="mb-1"><strong>Perfil:</strong> {{ Auth::user()->perfil->nome }}</p>
			@endif
			@if(isset(Auth::user()->regional->regional))	
			<p class="mb-1"><strong>Seccional:</strong> {{ Auth::user()->regional->regional }}</p>
			@endif
  	  	  	<a href="/admin/perfil/senha" class="btn btn-danger mt-3">Alterar senha</a>
  	  	  	@endif
  	  	  </div>
  	  	  <div class="card-footer">
  	  	  	CORE-SP
  	  	  </div>
  	  	</div>
  	  </div>
  	</div>
  </div>	
</section>

@endsection