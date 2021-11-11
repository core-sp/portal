@extends('site.prerepresentante.app')

@section('content-prerepresentante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Pré Registro</h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">
                <div class="d-block mt-2 mb-3">
                    <a href="{{ route('prerepresentante.inserir.preregistro.view') }}" class="btn btn-primary link-nostyle branco">Iniciar o pré registro</a>
                </div>      
            </div>
    </div>
</div>

@endsection