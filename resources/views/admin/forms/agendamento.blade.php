<div class="card-body pt-3 pl-3">
    <div class="col">
        {!! $resultado->getMsgByStatus() !!}
    </div>
</div>
<hr class="mb-0 mt-0">
<form role="form" method="POST">
    @csrf
    @method('PUT')
    <div class="card-body">
        <input type="hidden" name="antigo" value="{{ $resultado->isAfter() ? '0' : '1' }}" />
        <div class="form-row">
            <div class="col">
                <label for="nome">Nome</label>
                <input type="text"
                    class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                    placeholder="Nome"
                    name="nome"
                    value="{{ old('nome') ? old('nome') : $resultado->nome }}"
                    required
                    {{ ($resultado->status === 'Cancelado') || (!$resultado->isAfter()) ? 'readonly' : '' }}
                />
                @if($errors->has('nome'))
                <div class="invalid-feedback">
                {{ $errors->first('nome') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="email">Email</label>
                <input type="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="Email"
                    name="email"
                    value="{{ old('email') ? old('email') : $resultado->email }}"
                    required
                    {{ ($resultado->status === 'Cancelado') || (!$resultado->isAfter()) ? 'readonly' : '' }}
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
                    value="{{ old('cpf') ? old('cpf') : $resultado->cpf }}"
                    required
                    {{ ($resultado->status === 'Cancelado') || (!$resultado->isAfter()) ? 'readonly' : '' }}
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
                    value="{{ old('celular') ? old('celular') : $resultado->celular }}"
                    required
                    {{ ($resultado->status === 'Cancelado') || (!$resultado->isAfter()) ? 'readonly' : '' }}
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
                <label for="tiposervico">Tipo de serviço</label>
                <select name="tiposervico" 
                    class="form-control {{ $errors->has('tiposervico') ? 'is-invalid' : '' }}" 
                    required
                >
                @foreach($servicos as $servico)
                    @if(old('tiposervico'))
                    <option value="{{ $servico }}" {{ old('tiposervico') === $servico ? 'selected' : '' }}>{{ $servico }}</option>
                    @else
                    <option value="{{ $servico }}" {{ isset($resultado->tiposervico) && ($resultado->tiposervico == $servico) ? 'selected' : '' }}>{{ $servico }}</option>
                    @endif
                @endforeach
                </select>
                @if($errors->has('tiposervico'))
                <div class="invalid-feedback">
                {{ $errors->first('tiposervico') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="status">Status</label>
                <select name="status" 
                    class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" 
                    id="statusAgendamentoAdmin"
                >
                <option value="">Sem Status</option>
                @foreach($status as $s)
                    @if(old('status'))
                    <option value="{{ $s }}" {{ old('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @else
                    <option value="{{ $s }}" {{ isset($resultado->status) && ($resultado->status == $s) ? 'selected' : '' }}>{{ $s }}</option>
                    @endif
                @endforeach
                </select>
                @if($errors->has('status'))
                <div class="invalid-feedback">
                {{ $errors->first('status') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-4">
                <label for="idregional">Regional</label>
                <input type="text" name="idregional"
                    class="form-control"
                    readonly
                    value="{{ isset($resultado->idregional) ? $resultado->regional->regional : '' }}"
                />
            </div>
            <div class="col-1">
                <label for="dia">Dia</label>
                <input type="text" name="dia"
                    class="form-control"
                    readonly
                    value="{{ isset($resultado->dia) ? onlyDate($resultado->dia) : '' }}"
                />
            </div>
            <div class="col-1">
                <label for="hora">Hora</label>
                <input type="text" name="hora"
                    class="form-control"
                    readonly
                    value="{{ isset($resultado->hora) ? $resultado->hora : '' }}"
                />
            </div>
            <div class="col">
                <label for="atendente">Atendimento realizado por:</label>
                <select name="idusuario"
                    id="idusuarioAgendamento"
                    class="form-control {{ $errors->has('idusuario') ? 'is-invalid' : '' }}"
                >
                    <option value="">Ninguém</option>
                @if(isset($atendentes))
                    @foreach($atendentes as $atendente)
                        @if(old('idusuario'))
                        <option value="{{ $atendente->idusuario }}" {{ old('idusuario') == $atendente->idusuario ? 'selected' : '' }}>{{ $atendente->nome }}</option>
                        @else
                        <option value="{{ $atendente->idusuario }}" {{ isset($resultado->idusuario) && ($resultado->idusuario == $atendente->idusuario) ? 'selected' : '' }}>{{ $atendente->nome }}</option>
                        @endif
                    @endforeach
                @endif
                </select>
                @if($errors->has('idusuario'))
                <div class="invalid-feedback">
                {{ $errors->first('idusuario') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-4">
            <i>* Atendimento agendado pelo usuário no dia {{ onlyDate($resultado->created_at) }}.</i>
        </div>
        @if($resultado->isAfter() || $resultado->status !== 'Cancelado')
        <div class="form-row mb-2">
            <i>** Para alteração de horário, é necessário cancelar o agendamento e cadastrar um novo horário pelo site.</i>
        </div>
        @endif
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ session('url') ?? route('agendamentos.lista') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>

<script type="module" src="{{ asset('/js/interno/modulos/agendamento.js?'.time()) }}" id="modulo-agendamento" class="modulo-editar"></script>