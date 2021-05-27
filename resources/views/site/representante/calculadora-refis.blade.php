@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Calculadora Refis</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Teste da calculadora Refis</p>
        <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;Anuidades em aberto</h5>
        @if (!empty($anuidades))
        <table class="table table-bordered bg-white mb-0">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="quinze">Valor Original</th>
                    <th class="quinze">Juros</th>
                    <th class="quinze">Multa</th>
                    <th class="quinze">Correção</th>
                    <th class="quinze">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anuidades as $anuidade)
                    <tr>
                        <td class="ls-meio-neg">{{ $anuidade['DESCRICAO'] }}</td>
                        <td class="ls-meio-neg">R$ {{ toReais($anuidade['VALOR']) }}</td>
                        <td class="ls-meio-neg">R$ {{ toReais($anuidade['JUROS']) }}</td>
                        <td class="ls-meio-neg">R$ {{ toReais($anuidade['MULTA']) }}</td>
                        <td class="ls-meio-neg">R$ {{ toReais($anuidade['CORRECAO']) }}</td>
                        <td class="ls-meio-neg">R$ {{ toReais($anuidade['TOTAL']) }}</td>
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