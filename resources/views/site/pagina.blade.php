@extends('layout.app', ['title' => $pagina->titulo])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
	<div class="container-fluid text-center nopadding position-relative">
		<img src="{{asset($pagina->img)}}" />
		<div class="row position-absolute pagina-titulo">
			<div class="container text-center">
				<h1 class="branco text-uppercase">
					{{ $pagina->titulo }}
				</h1>
			</div>
		</div>
	</div>
</section>

<section id="pagina-conteudo" class="mt-4">
	<div class="container">
		{!! $pagina->conteudo !!}
	</div>
</section>

@endsection