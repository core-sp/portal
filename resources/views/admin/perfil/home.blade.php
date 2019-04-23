@extends('admin.layout.app')

@section('content')

@php
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
  	  	  	<div class="mb-2">
  	  	  	  <strong>Nome:</strong> {{ Auth::user()->nome }}
  	  	  	</div>
  	  	  	<div class="mb-3">
  	  	  	  <strong>Email:</strong> {{ Auth::user()->email }}
  	  	  	</div>
  	  	  	<a href="/admin/perfil/senha" class="btn btn-danger">Alterar senha</a>
  	  	  	@endif
  	  	  </div>
  	  	  <div class="card-footer">
  	  	  	CORE-SP
  	  	  </div>
  	  	</div>
  	  </div>
  	</div>
		@if($chamados->count())
		<div class="row mt-2">
			<div class="col">
				<div class="card card-info">
					<div class="card-header">
						<h3 class="card-title">Meus Chamados</h3>
					</div>
					<div class="card-body">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Código</th>
									<th>Tipo</th>
									<th>Prioridade</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								@foreach($chamados as $chamado)
								<tr>
									<td>{{ $chamado->idchamado }}</td>
									<td>{{ $chamado->tipo }}</td>
									<td>{{ $chamado->prioridade }}</td>
									<td>
									@if(isset($chamado->deleted_at))
										Concluído
									@else
										Registrado
									@endif
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<div class="card-footer">
						<div class="row">
              <div class="col-sm-5 align-self-center">
              @if($chamados instanceof \Illuminate\Pagination\LengthAwarePaginator)
                @if($chamados->count() > 1)
                  Exibindo {{ $chamados->firstItem() }} a {{ $chamados->lastItem() }} chamados de {{ $chamados->total() }} resultados.
                @endif
              @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  @if($chamados instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $chamados->links() }}
                  @endif
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