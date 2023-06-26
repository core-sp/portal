<form method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col-3">
                <label for="salaBloqueio">Sala de Reunião</label>
                <select 
                    name="sala_reuniao_id" 
                    class="form-control {{ $errors->has('sala_reuniao_id') ? 'is-invalid' : '' }}"
                    id="{{ !isset($resultado) ? 'salaBloqueio' : '' }}"
                    required
                >
                @if(isset($salas))
                    <option value="">Selecione uma sala...</option>
                    @foreach($salas as $sala)
                        <option value="{{ $sala->id }}" {{ old('sala_reuniao_id') == $sala->id ? 'selected' : '' }}>{{ $sala->regional->regional }}</option>
                    @endforeach
                @else
                    <option value="{{ $resultado->sala_reuniao_id }}" selected>{{ $resultado->sala->regional->regional }}</option>
                @endif
                </select>
                @if($errors->has('sala_reuniao_id'))
                <div class="invalid-feedback">
                    {{ $errors->first('sala_reuniao_id') }}
                </div>
                @endif
            </div>
            <div class="col-3">
                <label for="dataInicialBloqueio">Data inicial</label>
                <input 
                    type="date" 
                    name="dataInicial" 
                    class="form-control {{ $errors->has('dataInicial') ? 'is-invalid' : '' }}" 
                    id="dataInicialBloqueio" 
                    min="{{ isset($resultado->sala->dataInicial) ? $resultado->sala->dataInicial : now()->addDay()->format('Y-m-d') }}"
                    max="{{ isset($resultado->sala->dataFinal) ? $resultado->sala->dataFinal : '' }}"
                    value="{{ isset($resultado->dataInicial) ? $resultado->dataInicial : old('dataInicial') }}"
                    required
                />

                @if($errors->has('dataInicial'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataInicial') }}
                </div>
                @endif
            </div>
            <div class="col-3">
                <label for="dataFinalBloqueio">Data final</label>
                <input 
                    type="date" 
                    name="dataFinal" 
                    class="form-control {{ $errors->has('dataFinal') ? 'is-invalid' : '' }}" 
                    id="dataFinalBloqueio"
                    min="{{ isset($resultado->sala->dataInicial) ? $resultado->sala->dataInicial : now()->addDay()->format('Y-m-d') }}"
                    max="{{ isset($resultado->sala->dataFinal) ? $resultado->sala->dataFinal : '' }}"
                    value="{{ isset($resultado->dataFinal) ? $resultado->dataFinal : old('dataFinal') }}" 
                />

                @if($errors->has('dataFinal'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataFinal') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horariosBloqueio">Horários bloqueados</label>
                <select 
                    name="horarios[]" 
                    class="form-control {{ $errors->has('horarios') || $errors->has('horarios.*') ? 'is-invalid' : '' }}" 
                    id="horariosBloqueio" 
                    multiple
                    required
                >
                @php
                    $horarios = isset($resultado->horarios) ? explode(',', $resultado->horarios) : null;
                    $horasTotais = isset($resultado) ? $resultado->sala->getTodasHoras() : todasHoras();
                @endphp

                @foreach($horasTotais as $hora)
                    <option value="{{ $hora }}" {{ (!empty(old('horarios')) && is_array(old('horarios')) && in_array($hora, old('horarios'))) || (isset($horarios) && in_array($hora, $horarios)) ? 'selected' : '' }}>{{ $hora }}</option>
                @endforeach
                </select>

                @if($errors->has('horarios') || $errors->has('horarios.*'))
                <div class="invalid-feedback">
                    {{ $errors->has('horarios') ? $errors->first('horarios') : $errors->first('horarios.*') }}
                </div>
                @endif

                <small class="form-text text-muted">
                    <em>* Segure Ctrl para selecionar mais de um horário ou Shift para selecionar um grupo de horários</em>
                </small>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('sala.reuniao.bloqueio.lista') }}" class="btn btn-default">Voltar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($resultado->id) ? 'Salvar' : 'Criar' }}
            </button>
        </div>
    </div>
</form>