@php
    use App\Http\Controllers\Helper;
    $status = App\Http\Controllers\Helpers\AgendamentoControllerHelper::status();
    use App\Http\Controllers\Helpers\AgendamentoControllerHelper;
    $now = date('Y-m-d');
@endphp
<div class="card-body pt-3 pl-3">
    <div class="col">
        {{ AgendamentoControllerHelper::txtAgendamento($resultado->dia, $resultado->hora, $resultado->status, $resultado->protocolo, $resultado->idagendamento) }}
    </div>
</div>
<hr class="mb-0 mt-0">
<form role="form" method="POST">
    @csrf
    @method('PUT')
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="nome">Nome</label>
                <input type="text"
                    class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                    placeholder="Nome"
                    name="nome"
                    @if(!empty(old('nome')))
                    value="{{ old('nome') }}"
                    @else
                        @if(isset($resultado))
                        value="{{ $resultado->nome }}"
                        @endif
                    @endif
                    @if($resultado->status === 'Cancelado' || $now >= $resultado->dia)
                    readonly
                    @endif
                    />
                @if($errors->has('nome'))
                <div class="invalid-feedback">
                {{ $errors->first('nome') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="email">Email</label>
                <input type="text"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="Email"
                    name="email"
                    @if(!empty(old('email')))
                        value="{{ old('email') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->email }}"
                        @endif
                    @endif
                    @if($resultado->status === 'Cancelado' || $now >= $resultado->dia)
                    readonly
                    @endif
                    />
                @if($errors->has('email'))
                <div class="invalid-feedback">
                {{ $errors->first('email') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="CPF">CPF</label>
                <input type="text"
                    class="form-control cpfInput {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                    placeholder="CPF"
                    name="cpf"
                    @if(!empty(old('cpf')))
                    value="{{ old('cpf') }}"
                    @else
                        @if(isset($resultado))
                        value="{{ $resultado->cpf }}"
                        @endif
                    @endif
                    @if($resultado->status === 'Cancelado' || $now >= $resultado->dia)
                    readonly
                    @endif
                    />
                @if($errors->has('cpf'))
                <div class="invalid-feedback">
                {{ $errors->first('cpf') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="celular">Celular</label>
                <input type="text"
                    class="form-control {{ $errors->has('celular') ? 'is-invalid' : '' }}"
                    placeholder="Celular"
                    name="celular"
                    @if(!empty(old('celular')))
                    value="{{ old('celular') }}"
                    @else
                        @if(isset($resultado))
                        value="{{ $resultado->celular }}"
                        @endif
                    @endif
                    @if($resultado->status === 'Cancelado' || $now >= $resultado->dia)
                    readonly
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
            <div class="col">
                <label for="idregional">Regional</label>
                <select name="idregional"
                    class="form-control"
                    disabled
                    />
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
            <div class="col">
                <label for="atendente">Atendimento realizado por:</label>
                <select name="idusuario"
                    class="form-control"
                    @if($now < $resultado->dia)
                    disabled
                    @endif
                    />
                <option value="">Ninguém</option>
                @foreach($atendentes as $atendente)
                    @if(!empty(old('idusuario')))
                        @if(old('idusuario') == $atendente->idusuario)
                            <option value="{{ $atendente->idusuario }}" selected>{{ $atendente->nome }}</option>
                        @else
                            <option value="{{ $atendente->idusuario }}">{{ $atendente->nome }}</option>
                        @endif
                    @else
                        @if(isset($resultado))
                            @if($resultado->idusuario == $atendente->idusuario)
                            <option value="{{ $atendente->idusuario }}" selected>{{ $atendente->nome }}</option>
                            @else
                            <option value="{{ $atendente->idusuario }}">{{ $atendente->nome }}</option>
                            @endif
                        @else
                        <option value="{{ $atendente->idusuario }}">{{ $atendente->nome }}</option>
                        @endif
                    @endif
                @endforeach
                </select>
            </div>
            <div class="col">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                <option value="">Nulo</option>
                @foreach($status as $s)
                    @if(!empty(old('status')))
                        @if(old('status') === $s)
                            <option value="{{ $s }}" selected>{{ $s }}</option>
                        @else
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endif
                    @else
                        @if(isset($resultado))
                            @if($resultado->status == $s)
                            <option value="{{ $s }}" selected>{{ $s }}</option>
                            @else
                            <option value="{{ $s }}">{{ $s }}</option>
                            @endif
                        @else
                        <option value="{{ $s }}">{{ $s }}</option>
                        @endif
                    @endif
                @endforeach
                </select>
            </div>
        </div>
        <div class="form-row mt-4">
            <i>* Atendimento agendado pelo usuário no dia {{ Helper::onlyDate($resultado->created_at) }}.</i>
        </div>
        @if($now < $resultado->dia || $resultado->status !== 'Cancelado')
        <div class="form-row mb-2">
            <i>** Para alteração de horário, é necessário cancelar o agendamento e cadastrar um novo horário pelo site.</i>
        </div>
        @endif
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/agendamentos" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>