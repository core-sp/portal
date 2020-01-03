@extends('site.representante.app')

@section('content-representante')

@php
    $cobrancas = Auth::guard('representante')->user()->cobrancas();    
@endphp

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Situação Financeira</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Verifique abaixo a lista de cobranças vigentes junto ao Core-SP, sendo certo que <strong>só será possível imprimir boletos dentro do prazo de vencimento</strong>. Demais pendências, fora do prazo de vencimento, deverão ser regularizadas na sede ou em uma das Seccionais do Core-SP, pessoalmente, ou pelo email <strong>financeiro@core-sp.org.br</strong></p>
        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;ANUIDADES</h5>
        @if (!empty(Auth::guard('representante')->user()->cobrancas()['anuidades']))
            <table class="table table-bordered bg-white mb-0">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th class="quinze">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cobrancas['anuidades'] as $cobranca)
                        <tr>
                            <td class="ls-meio-neg">
                                {{ $cobranca['DESCRICAO'] }} ⋅ {!! secondLine($cobranca['SITUACAO'], $cobranca['VENCIMENTOBOLETO'], $cobranca['LINK'], $cobranca['DESCRICAO'], $cobranca['BOLETO']) !!}
                            </td>
                            <td class="ls-meio-neg">R$ {{ toReais($cobranca['VALOR']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="contatos-table space-single">
                <p class="light pb-0">Nada a mostrar aqui.</p>
            </div>
        @endif
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;OUTRAS COBRANÇAS</h5>
        @if (!empty($cobrancas['outros']))
            <table class="table table-bordered bg-white mb-0">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th class="quinze">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cobrancas['outros'] as $cobranca)
                        <tr>
                            <td class="ls-meio-neg">
                                {{ $cobranca['DESCRICAO'] }} ⋅ {!! secondLine($cobranca['SITUACAO'], $cobranca['VENCIMENTOBOLETO'], $cobranca['LINK'], $cobranca['DESCRICAO']) !!}
                            </td>
                            <td class="ls-meio-neg">R$ {{ toReais($cobranca['VALOR']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="contatos-table space-single">
                <p class="light pb-0">Nada a mostrar aqui.</p>
            </div>
        @endif
    </div>            
</div>

@endsection