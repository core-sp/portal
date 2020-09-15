@extends('site.representante.app')

@section('content-representante')

<div class="representante-content">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">{!! $titulo !!}</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2">
            {!! $mensagem !!}
            @if(isset($erro))
            {!! $erro !!}
            @endif
        </p>
        @if(!isset($erro))
        <form method="POST">
            @csrf
            <input type="submit" value="Verificar e Emitir" class="btn btn-sm btn-info" />
        </form>
        @endif
    </div>
</div>

@endsection