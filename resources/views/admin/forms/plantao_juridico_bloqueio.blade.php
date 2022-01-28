<form method="POST" action="{{-- isset($resultado) ? route('plantao.juridico.bloqueios.editar', $resultado->id) : route('plantao.juridico.bloqueios.criar') --}}">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col-3">
                <label for="plantaoBloqueio">Plantões Jurídicos</label>
                <select 
                    name="plantaoBloqueio" 
                    class="form-control {{ $errors->has('plantaoBloqueio') ? 'is-invalid' : '' }}"
                    id="plantaoBloqueio"
                >
                    <option value="">Selecione um plantão...</option>
                @foreach($plantoes as $plantao)
                    @if(old('plantao'))
                    <option value="{{ $plantao->id }}" {{ old('plantaoBloqueio') == $plantao->id ? 'selected' : '' }}>{{ $plantao->regional->regional }}</option>
                    @elseif(isset($resultado))
                    <option value="{{ $plantao->id }}" {{ $resultado->idplantaojuridico == $plantao->id ? 'selected' : '' }}>{{ $plantao->regional->regional }}</option>
                    @else
                    <option value="{{ $plantao->id }}">{{ $plantao->regional->regional }}</option>
                    @endif
                @endforeach
                </select>
                @if($errors->has('plantaoBloqueio'))
                <div class="invalid-feedback">
                    {{ $errors->first('plantaoBloqueio') }}
                </div>
                @endif
            </div>
            <div class="col-2">
                <label for="dataInicialBloqueio">Data inicial</label>
                <input 
                    type="date" 
                    name="dataInicialBloqueio" 
                    class="form-control {{ $errors->has('dataInicialBloqueio') ? 'is-invalid' : '' }}" 
                    id="dataInicialBloqueio" 
                    min=""
                    max=""
                    value="{{ isset($resultado->dataInicial) ? $resultado->dataInicial : old('dataInicialBloqueio') }}"
                />

                @if($errors->has('dataInicialBloqueio'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataInicialBloqueio') }}
                </div>
                @endif
            </div>
            <div class="col-2">
                <label for="dataFinalBloqueio">Data final</label>
                <input 
                    type="date" 
                    name="dataFinalBloqueio" 
                    class="form-control {{ $errors->has('dataFinalBloqueio') ? 'is-invalid' : '' }}" 
                    id="dataFinalBloqueio"
                    min=""
                    max=""
                    value="{{ isset($resultado->dataFinal) ? $resultado->dataFinal : old('dataFinalBloqueio') }}" />

                @if($errors->has('dataFinalBloqueio'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataFinalBloqueio') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horarios">Horários bloqueados</label>
                <select 
                    name="horarios[]" 
                    class="form-control {{ $errors->has('horarios') ? 'is-invalid' : '' }}" 
                    id="horarios" 
                    multiple
                >
                    @php
                        $horarios = isset($resultado->horarios) ? explode(',', $resultado->horarios) : null;
                    @endphp
                    @foreach (todasHoras() as $hora)
                        <option value="{{ $hora }}" {{ isset($horarios) && in_array($hora, $horarios) ? 'selected' : '' }}>{{ $hora }}</option>
                    @endforeach
                </select>

                @if($errors->has('horarios'))
                <div class="invalid-feedback">
                    {{ $errors->first('horarios') }}
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
            <a href="{{ route('plantao.juridico.bloqueios.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($resultado) ? 'Editar' : 'Criar' }}
            </button>
        </div>
    </div>
</form>