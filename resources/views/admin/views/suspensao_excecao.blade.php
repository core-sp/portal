<div class="card-body">
    <div class="row">
        <div class="col">
            <h5>Detalhes da suspensão do CPF / CNPJ <strong>{{ $resultado->getCpfCnpj() }}</strong></h5>
            <p>{{ $resultado->mostraPeriodo() }}: <em>{{ $resultado->mostraPeriodoEmDias() }}</em></p>
            @if(isset($resultado->agendamento_sala_id))
            <p>
                Link do agendamento responsável pela última suspensão através do agendamento -
                <a href="{{ route('sala.reuniao.agendados.view', [$resultado->agendamento_sala_id]) }}">{{ $resultado->agendamento->protocolo }}</a>
            </p>
            <hr>
            <p>
                @foreach($resultado->getProtocolosDasJustificativas() as $key => $protocolo)
                    {!! $key == 0 ? '<b>Protocolos dos agendamentos das justificativas: </b>' . $protocolo : ' <b>|</b> ' . $protocolo !!}
                @endforeach
            </p>
            
            @endif

        @if(isset($resultado->justificativa))
            <h5><b>Histórico de justificativas:</b></h5>
            @foreach($resultado->getJustificativasDesc() as $justificativa)
                <p>{{ $justificativa }}</p>
                <hr>
            @endforeach
        @endif

        </div>
    </div>

    <div class="float-left">
        <a href="{{ route('sala.reuniao.suspensao.lista') }}" class="btn btn-outline-secondary mt-4">
            Voltar
        </a>
    </div>
</div>