@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
<div class="d-block w-100 alert alert-dismissible {{ Session::get('class') }}">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {!! Session::get('message') !!}
</div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Solicitação de Registro</h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">
                <div class="d-block mt-2 mb-3">
                    <p>Algum texto explicando sobre o formulário, e onde pode visualizar a atual situação da solicitação</p>
                    <br>
                    @if(isset($gerenti))
                    <p>Você já possui registro ativo no Core-SP: <strong>{{ formataRegistro($gerenti) }}</strong></p>
                    @else
                    <a href="{{ route('externo.inserir.preregistro.view') }}" class="btn {{ isset($resultado->id) ? 'btn-secondary' : 'btn-success' }} link-nostyle branco mt-3">
                        {{ isset($resultado->id) ? 'Continuar' : 'Iniciar' }} a solicitação do registro
                    </a>
                    @endif

                </div>      
            </div>
    </div>
</div>

@endsection