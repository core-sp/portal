@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Cursos</h4>
        <div class="linha-lg-mini mb-1"></div>
    @if(isset($cursos))
        <div class="contatos-table space-single bg-info mb-2">
            <p class="light pb-0 text-white" data-clarity-mask="True">
            </p>
        </div>
        <div class="contatos-table">
        </div>
    @else
        <div class="contatos-table space-single">
            <p class="light pb-0">No momento não há cursos restritos para o representante.</p>
        </div>
    @endif
    </div>
</div>

@endsection