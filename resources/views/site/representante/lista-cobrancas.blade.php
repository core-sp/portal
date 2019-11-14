@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="nomargin conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Situação Financeira</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Confira abaixo sua lista de cobranças vigentes, as quais ainda estão <strong>dentro do prazo de vencimento.</strong></p>
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
                    @foreach (Auth::guard('representante')->user()->cobrancas()['anuidades'] as $cobranca)
                        @include('site.representante.inc.cobranca')
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="light">Nada a mostrar aqui.</p>
        @endif
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;OUTRAS COBRANÇAS</h5>
        @if (!empty(Auth::guard('representante')->user()->cobrancas()['outros']))
            <table class="table table-bordered bg-white mb-0">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th class="quinze">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (Auth::guard('representante')->user()->cobrancas()['outros'] as $cobranca)
                        @include('site.representante.inc.cobranca')
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="light">Nada a mostrar aqui.</p>
        @endif
    </div>            
</div>

@endsection