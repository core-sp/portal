@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
<div class="d-block w-100">
    <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
</div>
@endif

<div class="representante-content">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Home</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2">Seja bem-vindo à <strong>Área Restrita</strong> do <strong>Core-SP.</strong>.</p>
    </div>
</div>

@endsection