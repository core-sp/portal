@extends('admin.layout.app')

@section('content')

<section class="content-header">
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
  	  <div class="col-sm-12">
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
  	</div>
  </div>	
</section>

@endsection