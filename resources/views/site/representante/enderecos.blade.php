@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="row nomargin conteudo-txt">
        <h4 class="pt-1 pb-1">Endereços</h4>
        <div class="linha-lg-mini mb-3"></div>
        @forelse (Auth::guard('representante')->user()->enderecos() as $item)
            <div class="contato-single d-block {{ $loop->last ? '' : 'b-dashed' }}">
                <p class="pb-0">Endereço: <strong>{{ formataEnderecoGerenti($item['END_LOGRADOURO'], $item['END_NUMERO'], $item['END_CONPLEMENTO']) }}</strong></p>
                <p class="pb-0">Bairro: <strong>{{ $item['END_BAIRRO'] }}</strong></p>
                <p class="pb-0">Município: <strong>{{ $item['END_MUNICIPIO'] }}</strong></p>
                <p class="pb-0">Estado: <strong>{{ $item['END_ESTADO'] }}</strong></p>
                <p class="pb-0">CEP: <strong>{{ $item['END_CEP'] }}</strong></p>
                @if ($item['END_CORRESP'] === 'T   ')
                    <p class="pb-0"><small><i>(Endereço para correspondência)</i></small></p>
                @endif
                <div class="mt-3 {{ $loop->last ? '' : 'mb-3' }}">
                    <form method="GET" action="{{ route('representante.inserir-ou-alterar-endereco.view') }}">
                        <input type="hidden" name="sequencia" value="{{ $item['END_SEQUENCIA'] }}">
                        <button type="submit" class="btn btn-sm btn-info link-nostyle branco">Atualizar endereço</button>
                    </form>
                </div>
            </div>
        @empty
            <p>Nenhum endereço cadastrado.</p>
        @endforelse
    </div>
</div>

<div class="d-block mt-3">
    <a href="{{ route('representante.inserir-ou-alterar-endereco.view') }}" class="btn btn-primary link-nostyle branco">Inserir endereço</a>
</div>

@endsection