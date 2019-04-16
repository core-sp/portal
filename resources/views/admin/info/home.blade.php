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
      	<h1>Informações</h1>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
  	<div class="row">
  	  <div class="col-sm-12">
  	  	<div class="card card-info">
  	  	  <div class="card-header">
  	  	  	<h3 class="card-title">
	  	  	  Informações do usuário
	  	  	</h3>
  	  	  </div>
  	  	  <div class="card-body">
  	  	  	@if(Auth::check())
  	  	  	<div class="mb-2">
  	  	  	  <strong>Nome:</strong> {{ Auth::user()->nome }}
  	  	  	</div>
  	  	  	<div class="mb-3">
  	  	  	  <strong>Email:</strong> {{ Auth::user()->email }}
  	  	  	</div>
  	  	  	<a href="/admin/info/senha" class="btn btn-danger">Alterar senha</a>
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