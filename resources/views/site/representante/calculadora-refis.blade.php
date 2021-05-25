@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Calculadora Refis</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Teste da calculadora Refis</p>
        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;VALORES</h5>
        @if (!empty($valores))
            <div class="contatos-table space-single">
                <p class="light pb-0">Total anuidade: {{$valores['totalAnuidade']}}</p>
                <p class="light pb-0">Total débitos: {{$valores['totalDebito']}}</p>
            </div>
        @else
            <div class="contatos-table space-single">
                <p class="light pb-0">Nada a mostrar aqui.</p>
            </div>
        @endif
    </div>            
</div>

@endsection