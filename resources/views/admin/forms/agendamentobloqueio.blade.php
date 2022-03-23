<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="idregional">Regional</label>
                <select 
                    name="idregional" 
                    class="form-control {{ $errors->has('idregional') ? 'is-invalid' : '' }}"
                    id="{{ isset($resultado->idregional) ? '' : 'idregionalBloqueio' }}"
                    required
                >
            @if(isset($resultado->idregional))
                <option value="{{ $resultado->idregional }}">{{ $resultado->regional->regional }}</option>
            @else
                @foreach($regionais as $regional)
                <option value="{{ $regional->idregional }}" {{ old('idregional') == $regional->idregional ? 'selected' : '' }}>{{ $regional->regional }}</option>
                @endforeach
            @endif
                </select>
                @if($errors->has('idregional'))
                <div class="invalid-feedback">
                {{ $errors->first('idregional') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="diainicio">Data de início</label>
                <input type="date"
                    class="form-control {{ $errors->has('diainicio') ? 'is-invalid' : '' }}"
                    name="diainicio"
                    min="{{ date('Y-m-d') }}"
                    required
                @if(empty(old('diainicio')) && isset($resultado->diainicio))
                    value="{{ $resultado->diainicio }}"
                @elseif(old('diainicio'))
                    value="{{ old('diainicio') }}"
                @else
                    value="{{ date('Y-m-d') }}"
                @endif
                />
                @if($errors->has('diainicio'))
                <div class="invalid-feedback">
                {{ $errors->first('diainicio') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="diatermino">Data de término</label>
                <input type="date"
                    class="form-control {{ $errors->has('diatermino') ? 'is-invalid' : '' }}"
                    name="diatermino"
                    min="{{ date('Y-m-d') }}"
                @if(empty(old('diatermino')) && isset($resultado->diatermino))
                    value="{{ $resultado->diatermino }}"
                @elseif(old('diatermino'))
                    value="{{ old('diatermino') }}"
                @endif
                />
                <small class="form-text text-muted">
                    <em>* Deixe o campo vazio para aplicar a regra por tempo indeterminado</em>
                </small>
                @if($errors->has('diatermino'))
                <div class="invalid-feedback">
                {{ $errors->first('diatermino') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="horainicio">Hora inicial a ser bloqueada</label>
                <select 
                    name="horainicio" 
                    class="form-control {{ $errors->has('horainicio') ? 'is-invalid' : '' }}" 
                    id="horaInicioBloqueio"
                    required
                >
            @if(isset($resultado))
                @foreach($resultado->regional->horariosAge() as $hora)
                <option value="{{ $hora }}" {{ (old('horainicio') == $hora) || ($resultado->horainicio == $hora) ? 'selected' : '' }}>{{ $hora }}</option>
                @endforeach
            @else
                <option value="">Selecione o horário de início</option>
                @foreach(todasHoras() as $hora)
                <option value="{{ $hora }}" {{ (old('horainicio') == $hora) ? 'selected' : '' }}>{{ $hora }}</option>
                @endforeach
            @endif
                </select>
                @if($errors->has('horainicio'))
                <div class="invalid-feedback">
                {{ $errors->first('horainicio') }}
                </div>
                @endif
            </div>
            <div class="col">
            <label for="horatermino">Hora final a ser bloqueada</label>
                <select 
                    name="horatermino" 
                    class="form-control {{ $errors->has('horatermino') ? 'is-invalid' : '' }}" 
                    id="horaTerminoBloqueio"
                    required
                >
            @if(isset($resultado))
                 @foreach($resultado->regional->horariosAge() as $hora)
                <option value="{{ $hora }}" {{ (old('horatermino') == $hora) || ($resultado->horatermino == $hora) ? 'selected' : '' }}>{{ $hora }}</option>
                @endforeach
            @else
                <option value="">Selecione o horário de término</option>
                @foreach(todasHoras() as $hora)
                <option value="{{ $hora }}" {{ (old('horatermino') == $hora) ? 'selected' : '' }}>{{ $hora }}</option>
                @endforeach
            @endif
                </select>
                @if($errors->has('horatermino'))
                <div class="invalid-feedback">
                {{ $errors->first('horatermino') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('agendamentobloqueios.lista') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
            {{ isset($resultado->idagendamentobloqueio) ? 'Salvar' : 'Publicar' }}
            </button>
        </div>
    </div>
</form>