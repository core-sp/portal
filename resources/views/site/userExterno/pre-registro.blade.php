@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Solicitação de Registro</h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">
                <div class="d-block mt-2 mb-3">
                    <p>Algum texto explicando sobre o formulário, e onde pode visualizar a atual situação da solicitação</p>
                    <a href="{{ route('externo.inserir.preregistro.view') }}" class="btn btn-secondary link-nostyle branco mt-3">
                        {{ isset($resultado->id) ? 'Continuar' : 'Iniciar' }} a solicitação do registro
                    </a>
                </div>      
            </div>
    </div>
</div>

@endsection