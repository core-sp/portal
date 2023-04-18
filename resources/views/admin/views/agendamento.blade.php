<div class="card-body">
    @if(!$resultado->isAfter())
    <div class="col mb-3 mt-2">
        {!! $resultado->getMsgByStatus() !!}
    </div>
    <hr>
    @endif
    <div class="row">
        <div class="col">
            <p>Nome: <strong>{{ !isset($resultado->nome) ? '----' : $resultado->nome }}</strong></p>
            <p>Email: <strong>{{ !isset($resultado->email) ? '----' : $resultado->email }}</strong></p>
            <p>CPF: <strong>{{ !isset($resultado->cpf) ? '----' : $resultado->cpf }}</strong></p>
            <p>Celular: <strong>{{ !isset($resultado->celular) ? '----' : $resultado->celular }}</strong></p>
            <p>Tipo de serviço: <strong>{{ !isset($resultado->tiposervico) ? '----' : $resultado->tiposervico }}</strong></p>
            <p>Regional: <strong>{{ !isset($resultado->regional->regional) ? '----' : $resultado->regional->regional }}</strong></p>
            <p>Dia e Hora: <strong>{{ !isset($resultado->dia) ? '----' : onlyDate($resultado->dia) }} às {{ !isset($resultado->hora) ? '----' : $resultado->hora }}</strong></p>
            <p>Status: <strong>{{ !isset($resultado->status) ? 'Sem status' : $resultado->status }}</strong></p>
            <p>Atendido por: <strong>{{ ($resultado->status == 'Compareceu') && isset($resultado->user->nome) ? $resultado->user->nome : 'Ninguém' }}</strong></p>
            <p>Data de criação do agendamento pelo site: <strong>{{ onlyDate($resultado->created_at) }}</strong></p>
        </div>
    </div>
    <div class="float-left mt-3">
        <a href="{{ session('url') ?? route('agendamentos.lista') }}" class="btn btn-default">Voltar</a>
    </div>
</div>