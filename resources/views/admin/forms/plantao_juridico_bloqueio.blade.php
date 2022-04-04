<form method="POST" action="{{ isset($resultado) ? route('plantao.juridico.bloqueios.editar', $resultado->id) : route('plantao.juridico.bloqueios.criar') }}">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        @if(!isset($resultado) || (isset($resultado) && $resultado->plantaoJuridico->ativado()))
        <div id="{{ !isset($resultado) ? 'textoAgendados' : '' }}" class="{{ !isset($resultado) ? 'text-hide' : 'mb-3' }}"><strong>
            <span class="text-success">Ativado!</span> Confira <a id="linkAgendadosPlantao" href="{{ isset($resultado) ? route('plantao.juridico.editar.view', $resultado->plantaoJuridico->id) : '' }}">aqui</a> se existem agendados no horário a ser bloqueado para realizar o cancelamento.</i>
        </strong></div>
        @endif
        <div class="form-row">
            <div class="col-3">
                <label for="plantaoBloqueio">Plantões Jurídicos</label>
                <select 
                    name="plantaoBloqueio" 
                    class="form-control {{ $errors->has('plantaoBloqueio') ? 'is-invalid' : '' }}"
                    id="{{ !isset($resultado) ? 'plantaoBloqueio' : '' }}"
                    required
                >
                @if(isset($plantoes))
                    <option value="">Selecione um plantão...</option>
                    @foreach($plantoes as $plantao)
                        <option value="{{ $plantao->id }}" {{ old('plantaoBloqueio') == $plantao->id ? 'selected' : '' }}>{{ $plantao->regional->regional }}</option>
                    @endforeach
                @else
                    <option value="{{ $resultado->idplantaojuridico }}" selected>{{ $resultado->plantaoJuridico->regional->regional }}</option>
                @endif
                </select>
                @if($errors->has('plantaoBloqueio'))
                <div class="invalid-feedback">
                    {{ $errors->first('plantaoBloqueio') }}
                </div>
                @endif
                <p class="mt-2">
                    <i><b>Período do plantão selecionado: </b></i>
                    <span id="bloqueioPeriodoPlantao">
                        {{ isset($resultado->plantaoJuridico->dataInicial) && isset($resultado->plantaoJuridico->dataFinal) ? 
                            onlyDate($resultado->plantaoJuridico->dataInicial).' - '.onlyDate($resultado->plantaoJuridico->dataFinal) : '' }}
                    </span>
                </p>
            </div>
            <div class="col-3">
                <label for="dataInicialBloqueio">Data inicial</label>
                <input 
                    type="date" 
                    name="dataInicialBloqueio" 
                    class="form-control {{ $errors->has('dataInicialBloqueio') ? 'is-invalid' : '' }}" 
                    id="dataInicialBloqueio" 
                    min="{{ isset($resultado->plantaoJuridico->dataInicial) ? $resultado->plantaoJuridico->dataInicial : Carbon\Carbon::tomorrow()->format('Y-m-d') }}"
                    max="{{ isset($resultado->plantaoJuridico->dataFinal) ? $resultado->plantaoJuridico->dataFinal : '' }}"
                    value="{{ isset($resultado->dataInicial) ? $resultado->dataInicial : old('dataInicialBloqueio') }}"
                    required
                />

                @if($errors->has('dataInicialBloqueio'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataInicialBloqueio') }}
                </div>
                @endif
            </div>
            <div class="col-3">
                <label for="dataFinalBloqueio">Data final</label>
                <input 
                    type="date" 
                    name="dataFinalBloqueio" 
                    class="form-control {{ $errors->has('dataFinalBloqueio') ? 'is-invalid' : '' }}" 
                    id="dataFinalBloqueio"
                    min="{{ isset($resultado->plantaoJuridico->dataInicial) ? $resultado->plantaoJuridico->dataInicial : Carbon\Carbon::tomorrow()->format('Y-m-d') }}"
                    max="{{ isset($resultado->plantaoJuridico->dataFinal) ? $resultado->plantaoJuridico->dataFinal : '' }}"
                    value="{{ isset($resultado->dataFinal) ? $resultado->dataFinal : old('dataFinalBloqueio') }}" 
                    required
                />

                @if($errors->has('dataFinalBloqueio'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataFinalBloqueio') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horariosBloqueio">Horários bloqueados</label>
                <select 
                    name="horariosBloqueio[]" 
                    class="form-control {{ $errors->has('horariosBloqueio') ? 'is-invalid' : '' }}" 
                    id="horariosBloqueio" 
                    multiple
                    required
                >
                @php
                    $horarios = isset($resultado->horarios) ? explode(',', $resultado->horarios) : null;
                    $horariosPlantao = isset($resultado->plantaoJuridico->horarios) ? explode(',', $resultado->plantaoJuridico->horarios) : null;
                @endphp

                @if(isset($resultado) && isset($horariosPlantao))
                    @foreach($horariosPlantao as $hora)
                    <option value="{{ $hora }}" {{ (!empty(old('horariosBloqueio')) && in_array($hora, old('horariosBloqueio'))) || (isset($horarios) && in_array($hora, $horarios)) ? 'selected' : '' }}>{{ $hora }}</option>
                    @endforeach
                @else
                    @foreach(todasHoras() as $hora)
                    <option value="{{ $hora }}" {{ !empty(old('horariosBloqueio')) && in_array($hora, old('horariosBloqueio')) ? 'selected' : '' }}>{{ $hora }}</option>
                    @endforeach
                @endif
                </select>

                @if($errors->has('horariosBloqueio'))
                <div class="invalid-feedback">
                    {{ $errors->first('horariosBloqueio') }}
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
                {{ isset($resultado->id) ? 'Salvar' : 'Criar' }}
            </button>
        </div>
    </div>
</form>