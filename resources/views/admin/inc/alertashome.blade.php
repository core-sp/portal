<div class="container-fluid mb-2">
    @if(isset($alertas['agendamentoCount']))
    <div class="row">
        <div class="col">
            <a href="{{ route('agendamentos.pendentes') }}">
                <div class="alert alert-warning link-alert">
                    <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;{{ $alertas['agendamentoCount'] === 1 ? 'Existe ' : 'Existem ' }}<strong>{{ $alertas['agendamentoCount'] }}</strong>{{ $alertas['agendamentoCount'] === 1 ? ' atendimento pendente' : ' atendimentos pendentes' }} de validação!&nbsp;&nbsp;<span class="link-alert-span">(Validar)</span>
                </div>
            </a>
        </div>
    </div>
    @endif
</div>