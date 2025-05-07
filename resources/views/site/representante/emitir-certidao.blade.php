@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">{!! $titulo !!}</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p class="pt-2" data-clarity-mask="True">
            {!! $mensagem !!}
        </p>
        @if($emitir)
        {{--
        <form method="POST" class="d-inline">
            @csrf
            <input type="submit" value="Emitir certidão" class="emitirCertidaoBtn btn btn-primary link-nostyle branco"/>
        </form>
        --}}

        <button type="button" value="Emitir certidão" class="emitirCertidaoBtn btn btn-primary link-nostyle branco">Emitir certidão</button>
        @endif    

        @if(isset($certidoes))
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;CERTIDÕES EMITIDAS</h5>   

        <div class="contatos-table">
            @forelse ($certidoes as $certidao)
            <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                <p class="pb-0" data-clarity-mask="True">
                    <b>Nº: </b> {{ $certidao['numeroDocumento']}} - 
                    <b>Data emissão: </b> {{ $certidao['dataEmissao']}} - 
                    <b>Hora emissão: </b> {{ $certidao['horaEmissao']}}
                    <small class="light">({{ $certidao['status'] }})</small>
                </p>
                @if(trim($certidao['status']) == 'Emitido')
                <div class="contato-btns">
                    {{--
                    <form action="{{ route('representante.baixarCertidao') }}" method="GET" class="d-inline">
                        @csrf
                    --}}
                        <input type="hidden" name="numero" value="{{ $certidao['numeroDocumento'] }}" />
                    {{--
                        <input type="submit" value="Baixar" class="baixarCertidaoBtn btn btn-sm btn-success" />
                    </form>
                    --}}
                    
                    <button type="button" value="Baixar" class="baixarCertidaoBtn btn btn-sm btn-success">Baixar</button>
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

<script type="module" src="{{ asset('/js/restrita-rc/modulos/certidao.js?'.hashScriptJs()) }}" data-modulo-id="certidao" data-modulo-acao="visualizar"></script>

@endsection