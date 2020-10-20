@extends('site.representante.app')

@section('content-representante')

<div class="representante-content">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">{!! $titulo !!}</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2">
            {!! $mensagem !!}
        </p>
        @if($reuso)
        <form method="POST" action="{{ route('representante.emitirCertidao', ['tipo' => $codigo]) }}" class="d-inline">
            @csrf
            <input type="submit" value="Baixar" class="emitirCertidaoBtn btn btn-sm btn-info" />
        </form>
        @endif

        @if($emitir)
        <form method="POST" class="d-inline">
            @csrf
            <input type="submit" value="Emitir" class="emitirCertidaoBtn btn btn-sm btn-info" />
        </form>
        @endif        
    </div>
</div>

@endsection