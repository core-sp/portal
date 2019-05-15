@php
    use App\Http\Controllers\Helper;
    $status = App\Http\Controllers\Helpers\AgendamentoControllerHelper::status();
    use App\Http\Controllers\Helpers\AgendamentoControllerHelper;
@endphp
<form role="form" method="POST">
    @csrf
    {{ method_field('PUT') }}
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="nome">Nome</label>
                <input type="text"
                    class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                    placeholder="Nome"
                    name="nome"
                    @if(isset($resultado))
                    value="{{ $resultado->nome }}"
                    @endif
                    />
                @if($errors->has('nome'))
                <div class="invalid-feedback">
                {{ $errors->first('nome') }}
                </div>
                @endif
            </div>
            <div class="col-sm-3">
                <label for="CPF">CPF</label>
                <input type="text"
                    class="form-control {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                    placeholder="CPF"
                    name="cpf"
                    @if(isset($resultado))
                    value="{{ $resultado->cpf }}"
                    @endif
                    />
                @if($errors->has('cpf'))
                <div class="invalid-feedback">
                {{ $errors->first('cpf') }}
                </div>
                @endif
            </div>
            <div class="col-sm-3">
                <label for="celular">Celular</label>
                <input type="text"
                    class="form-control {{ $errors->has('celular') ? 'is-invalid' : '' }}"
                    placeholder="Celular"
                    name="celular"
                    @if(isset($resultado))
                    value="{{ $resultado->celular }}"
                    @endif
                    />
                @if($errors->has('celular'))
                <div class="invalid-feedback">
                {{ $errors->first('celular') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-sm-4">
                <label for="regional">Regional</label>
                <select name="regional" class="form-control">
                @foreach($regionais as $regional)
                    @if(isset($resultado))
                        @if($resultado->idregional == $regional->idregional)
                        <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                        @else
                        <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @else
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endif
                @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label for="atendente">Atendimento realizado por:</label>
                <select name="atendente" class="form-control">
                <option value="">Ninguém</option>
                @foreach($atendentes as $atendente)
                    @if(isset($resultado))
                        @if($resultado->idusuario == $atendente->idusuario)
                        <option value="{{ $atendente->idusuario }}" selected>{{ $atendente->nome }}</option>
                        @else
                        <option value="{{ $atendente->idusuario }}">{{ $atendente->nome }}</option>
                        @endif
                    @else
                    <option value="{{ $atendente->idusuario }}">{{ $atendente->nome }}</option>
                    @endif
                @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                <option value="">Nulo</option>
                @foreach($status as $s)
                    @if(isset($resultado))
                        @if($resultado->status == $s)
                        <option value="{{ $s }}" selected>{{ $s }}</option>
                        @else
                        <option value="{{ $s }}">{{ $s }}</option>
                        @endif
                    @else
                    <option value="{{ $s }}">{{ $s }}</option>
                    @endif
                @endforeach
                </select>
            </div>
        </div>
        <div class="form-row mt-3 text-right">
            <div class="col">
                {{ AgendamentoControllerHelper::txtAgendamento($resultado->dia, $resultado->hora, $resultado->status, $resultado->protocolo) }}
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/agendamentos" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>