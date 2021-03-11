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

        @if(!is_null($certidoes))
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;CERTIDÕES DE REGULARIDADE EMITIDAS</h5>   

        <div class="contatos-table">
            @forelse ($certidoes as $certidao)
            <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                <p class="pb-0">
                    <b>Número:</b> {{ $certidao['NUMERO']}}
                    <small class="light">({{ $certidao['SITUACAO'] }})</small>
                </p>
                @if(trim($certidao['SITUACAO']) == 'Ativa')
                <div class="contato-btns">
                    <form action="{{ route('representante.baixarCertidao') }}" method="GET" class="d-inline">
                        @csrf
                        <input type="hidden" name="numero" value="{{ $certidao['NUMERO'] }}" />
                        <input type="hidden" name="tipo" value="11" />
                        <input type="submit" value="Baixar" class="btn btn-sm btn-success" />
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="contatos-table space-single">
                <p class="light pb-0">Nenhuma certidão encontrada.</p>
            </div>
            @endforelse
        @endif
        </div>

    </div>
</div>

@endsection