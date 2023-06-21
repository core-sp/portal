<div class="card-body">
    <div class="row">
        <div class="col">
            <h4>Representante Responsável:</h4>
            <p class="mb-0">Nome: <strong>{{ $resultado->representante->nome }}</strong></p>
            <p class="mb-0">Email: <strong>{{ $resultado->representante->email }}</strong></p>
            <p class="mb-0">Registro: <strong>{{ $resultado->representante->registro_core }}</strong></p>
            <p class="mb-0">CPF/CNPJ: <strong>{{ $resultado->representante->cpf_cnpj }}</strong></p>
            <hr>
            <h4>Agendamento:</h4>
            <p class="mb-0">Status: <strong>{!! $resultado->getStatusHTML() !!}</strong></p>
            <p class="mb-0">Tipo de Sala: <strong>{{ $resultado->getTipoSala() }}</strong></p>
            <p class="mb-0">Dia e Período: <strong>{{ onlyDate($resultado->dia) }} | {{ $resultado->getPeriodo() }}</strong></p>
            <p class="mb-0">Regional: <strong>{{ $resultado->sala->regional->regional }}</strong></p>
            <hr>
            @if($resultado->tipo_sala == 'reuniao')
            <h4>Participantes:</h4>
                @foreach($resultado->getParticipantes() as $cpf => $nome)
                <p class="mb-0">CPF: <strong>{{ formataCpfCnpj($cpf) }}</strong> | Nome: <strong>{{ $nome }}</strong></p>
                @endforeach
            <hr>
            @endif

        @if(isset($resultado->justificativa))
            <h4>Justificativa do Representante:</h4>
            <p class="mb-0">{{ $resultado->justificativa }}</p>
            @if(isset($resultado->anexo))
            <a href="{{ route('sala.reuniao.agendados.view', ['id' => $resultado->id, 'anexo' => $resultado->anexo]) }}" 
                class="btn btn-sm btn-info mt-2" 
                target="_blank">
                Comprovante
            </a>
            @endif
        @endif

        @if(isset($resultado->justificativa_admin))
            <hr>
            <h4>Justificativa do(a) atendente <em>{{ $resultado->user->nome }}</em>:</h4>
            <p class="mb-0">{{ $resultado->justificativa_admin }}</p>
        @endif

        @if($resultado->justificativaEnviada())
            <br><br>
            <form action="{{ route('sala.reuniao.agendados.update', ['id' => $resultado->id, 'acao' => 'aceito']) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-primary">Não Compareceu Justificado</button>
            </form>

            <button class="btn btn-info ml-3" id="recusar-trigger">Recusar justificativa&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></button>
            <div class="w-100" id="recusar-form">
                <form action="{{ route('sala.reuniao.agendados.update', ['id' => $resultado->id, 'acao' => 'recusa']) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <label for="justificativa_admin">Insira o motivo:</label>
                    <textarea 
                        name="justificativa_admin" 
                        rows="3" 
                        class="form-control {{ $errors->has('justificativa_admin') ? 'is-invalid' : '' }}"
                        id="justificativa_admin"
                        maxlength="1000"
                    >{{ old('justificativa_admin') }}</textarea>

                    @if($errors->has('justificativa_admin'))
                    <div class="invalid-feedback">
                        {{ $errors->first('justificativa_admin') }}
                    </div>
                    @endif
                    <button type="submit" class="btn btn-danger mt-2">
                        Atualizar para Não Compareceu
                    </button>
                </form>
            </div>
            <hr>
        @endif
        </div>
    </div>

    <div class="float-left">
        <a href="{{ session('url') ?? route('sala.reuniao.agendados.index') }}" class="btn btn-outline-secondary mt-4">
            Voltar
        </a>
    </div>
</div>