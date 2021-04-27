@extends('site.representante.app')

@section('content-representante')

<div class="representante-content">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">{!! $titulo !!}</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2">
            {!! $mensagem !!}
        </p>
        @if($emitir)
        <form method="POST" class="d-inline">
            @csrf
            <input type="submit" value="Emitir certidão" class="emitirCertidaoBtn btn btn-primary link-nostyle branco" />
        </form>
        @endif     
        </div>
    </div>
</div>

@endsection