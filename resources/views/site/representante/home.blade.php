@extends('site.representante.app')

@section('content-representante')

<div class="representante-content">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Home</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2">Seja bem-vindo à nova <strong>Área do Representante Comercial</strong> do <strong>Core-SP.</strong> Este é o seu espaço para conferir, atualizar e regularizar todas as suas pendências junto ao Conselho.</p>
        <p class="pb-0">Estamos trabalhando para tornar este espaço cada vez mais completo e com mais funcionalidades. Fique por dentro das <a href="/noticias">notícias</a> do Core-SP para saber todas as novidades.</p>
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;STATUS</h5>
        <p class="pb-0">{!! statusBold(Auth::guard('representante')->user()->status()) !!}</p>
    </div>
</div>

@endsection