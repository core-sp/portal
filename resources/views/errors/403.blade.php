@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
  	<div class="row mb-2">
  	  <div class="col-12">
  	  	<h1>Não autorizado!</h1>
  	  </div>
  	</div>
  </div>
</section>

<section class="content">
  <div class="error-page">
  	<h2 class="headline text-danger">403</h2>
  	<div class="error-content align-middle">
  	  <h3>
  	  	<i class="fa fa-warning text-danger"></i>
  	  	Acesso não autorizado!
  	  </h3>
  	  <p>Seu usuário não tem permissão para acessar este conteúdo. Caso haja algum erro, entre em contato com o CTI.</p>
  	  <p><a href="/admin">Volte à página principal</a></p>
  	</div>
  </div>
</section>

@endsection