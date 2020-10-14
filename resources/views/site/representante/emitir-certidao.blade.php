@extends('site.representante.app')

@section('content-representante')

<div class="representante-content">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">{!! $titulo !!}</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2">
            @if(!isset($erro))
            {!! $mensagem !!}
            @else
            {!! $erro !!}
            @endif
        </p>
        @if(!isset($erro))
        <form method="POST">
            @csrf
            <input id="emitirCertidaoBtn" type="submit" value="Verificar e Baixar" class="btn btn-sm btn-info" />
        </form>
        @endif
    </div>
</div>

@endsection