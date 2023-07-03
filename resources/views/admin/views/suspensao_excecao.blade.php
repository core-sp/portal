<div class="card-body">
    <div class="row">
        <div class="col">
            <h4>Detalhes da suspensão do CPF / CNPJ <strong>{{ $resultado->getCpfCnpj() }}</strong></h4>
            <hr>
            <p><b>Período: </b>{{ $resultado->mostraPeriodo() }} (<em>{{ $resultado->mostraPeriodoEmDias() }}</em>)</p>
            
            @foreach($resultado->getProtocolosDasJustificativas() as $key => $protocolo)
                @if($key == 0)
                <b>Protocolos dos agendamentos das justificativas: </b>
                @endif
                <a href="{{ route('sala.reuniao.agendados.busca', ['q' => $protocolo]) }}" target="_blank">{{ $protocolo }}</a>&nbsp;&nbsp;<b>|</b>
            @endforeach
            </p>

            @if(isset($resultado->justificativa))
            <h5 class="mt-4"><b>Histórico de justificativas:</b></h5>
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