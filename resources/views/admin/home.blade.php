@extends('admin.layout.app')

@section('content')

@php
	use App\Http\Controllers\NewsletterController;
	use App\Http\Controllers\ControleController;
	use App\Http\Controllers\Helper;
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
  	  	  	<h3 class="card-title">
							Conectado como:
							<strong>
								{{ session('perfil') }}
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
									<th scope="col">Código</th>
									<th>Tipo / Mensagem</th>
									<th>Prioridade</th>
									<th>Status</th>
									<th>Ações</th>
								</tr>
							</thead>
							<tbody>
								@foreach($chamados as $chamado)
								<tr>
									<td>{{ $chamado->idchamado }}</td>
									<td>{{ $chamado->tipo }}<br /><small>{{ Helper::resumoTamanho($chamado->mensagem, 75) }}</small></td>
									<td>{{ $chamado->prioridade }}</td>
									<td>
									@if(isset($chamado->deleted_at))
										Concluído
									@else
										Registrado
									@endif
									</td>
									<td>
										<a href="/admin/chamados/editar/{{ $chamado->idchamado }}" class="btn btn-sm btn-primary">Editar</a>
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