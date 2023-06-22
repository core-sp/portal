@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
<div class="d-block w-100">
    <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
</div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Agendamentos de Salas</h4>
        <div class="linha-lg-mini mb-2"></div>
        <p>Serviço de reserva de sala, tanto na sede quanto nas seccionais, para reunião e/ou coworking quando disponível, através do agendamento.</p>
        <div class="d-block mt-2">
            <a href="{{ route('representante.agendar.inserir.view', 'agendar') }}" class="btn btn-primary link-nostyle branco">Agendar sala</a>
        </div>

        <ul class="nav nav-tabs mt-3">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#agendamento">Seus Agendamentos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#participando">Agendamentos como participante</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane container active" id="agendamento">
                @if($salas->isNotEmpty())
                <div class="list-group w-100 mt-2">
                    @foreach ($salas as $item)
                    <div class="list-group-item light d-block bg-info">
                        <p class="pb-0 branco">Protocolo: <strong>{{ $item->protocolo }}</strong></p>
                        <p class="pb-0 branco">Regional: <strong>{{ $item->sala->regional->regional }}</strong></p>
                        <p class="pb-0 branco">
                            Sala: <strong>{{ $item->getTipoSala() }}</strong>
                            &nbsp;&nbsp;|&nbsp;&nbsp;Dia: <strong>{{ onlyDate($item->dia) }}</strong>
                            &nbsp;&nbsp;|&nbsp;&nbsp;Período: <strong>{{ $item->getPeriodo() }}</strong>
                        </p>
                        @if($item->isReuniao())
                        <p class="pb-0 branco"><i class="fas fa-users text-dark"></i> Participantes: 
                            @foreach($item->getParticipantes() as $cpf => $nome)
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            {!! 'CPF: <strong>'.formataCpfCnpj($cpf) . '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;Nome: <strong>' .$nome.'</strong>' !!}
                            @endforeach
                        </p>
                        @endif
                        @if($item->podeEditarParticipantes())
                        <a href="{{ route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $item->id]) }}" class="btn btn-secondary btn-sm link-nostyle">Editar Participantes</a>
                        @endif
                        @if($item->podeCancelar())
                        <a href="{{ route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $item->id]) }}" class="btn btn-danger btn-sm link-nostyle">Cancelar</a>
                        @endif
                        @if($item->podeJustificar())
                        <p class="pb-0 branco">
                            <a href="{{ route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $item->id]) }}" class="btn btn-sm btn-dark link-nostyle">Justificar</a>
                            &nbsp;&nbsp;<i class="fas fa-exclamation-triangle text-warning"></i>&nbsp;&nbsp;Caso não tenha comparecido, deve justificar até {{ $item->getDataLimiteJustificar() }}
                        </p>
                        @endif
                    </div>
                    <div class="linha-lg-mini mb-2"></div>
                    @endforeach

                    <div class="float-left mt-3">
                    @if($salas instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        {{ $salas->appends(request()->input())->links() }}
                    @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="tab-pane container fade" id="participando">
                @if($participando->isNotEmpty())
                <div class="list-group w-100 mt-2">
                    @foreach ($participando as $participa)
                    <div class="list-group-item light d-block bg-secondary">
                        <p class="pb-0 branco">Protocolo: <strong>{{ $participa->protocolo }}</strong></p>
                        <p class="pb-0 branco">
                            Representante Responsável:  
                            &nbsp;&nbsp;CPF / CNPJ: <strong>{{ $participa->representante->cpf_cnpj }}</strong>
                            &nbsp;&nbsp;|&nbsp;&nbsp;Nome: <strong>{{ $participa->representante->nome }}</strong>
                        </p>
                        <p class="pb-0 branco">Regional: <strong>{{ $participa->sala->regional->regional }}</strong></p>
                        <p class="pb-0 branco">
                            Sala: <strong>{{ $participa->getTipoSala() }}</strong>
                            &nbsp;&nbsp;|&nbsp;&nbsp;Dia: <strong>{{ onlyDate($participa->dia) }}</strong>
                            &nbsp;&nbsp;|&nbsp;&nbsp;Período: <strong>{{ $participa->getPeriodo() }}</strong>
                        </p>
                    </div>
                    <div class="linha-lg-mini mb-2"></div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

@endsection