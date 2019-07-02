<div class="container-fluid mb-2">
    @if(isset($alertas['agendamentoCount']))
    <div class="row">
        <div class="col">
            <a href="/admin/agendamentos/pendentes">
                <div class="alert alert-warning link-alert">
                    @if($alertas['agendamentoCount'] === 1)
                    <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Existe <strong>1</strong> atendimento pendente de validação!&nbsp;&nbsp;<span class="link-alert-span">(Validar)</span>
                    @else
                    <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Existem <strong>{{ $alertas['agendamentoCount'] }}</strong> atendimentos pendentes de validação!&nbsp;&nbsp;<span class="link-alert-span">(Validar)</span>
                    @endif
                </div>
            </a>
        </div>
    </div>
    @endif
</div>