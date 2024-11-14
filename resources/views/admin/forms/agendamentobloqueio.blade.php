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
                <option value="Todas">Todas</option>
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
                @if(!isset($resultado->idregional))
                <small class="form-text text-muted">
                    <em>* A opção 'Todas' somente permite criar bloqueio para o dia todo com 0 atendentes em todas as regionais. Ex: feriados</em>
                </small>
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
                @if($errors->has('diatermino'))
                <div class="invalid-feedback">
                {{ $errors->first('diatermino') }}
                </div>
                @endif
                <small class="form-text text-muted">
                    <em>* Deixe o campo vazio para aplicar a regra por tempo indeterminado</em>
                </small>
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-6">
                <label for="horarios">Horários a serem bloqueados / qtd de agendamentos alterada</label>
                <select 
                    name="horarios[]" 
                    class="form-control {{ $errors->has('horarios') || $errors->has('horarios.*') ? 'is-invalid' : '' }}" 
                    id="horarios" 
                    multiple
                    required
                >
                @php
                    $horarios = isset($resultado->horarios) ? explode(',', $resultado->horarios) : null;
                    $horasTotais = isset($resultado) ? $resultado->regional->horariosAge() : todasHoras();
                @endphp
                @foreach($horasTotais as $hora)
                    <option value="{{ $hora }}" {{ !empty(old('horarios')) && is_array(old('horarios')) && in_array($hora, old('horarios')) || (isset($horarios) && in_array($hora, $horarios)) ? 'selected' : '' }}>{{ $hora }}</option>
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
            <div class="col">
                <label for="qtd_atendentes">Quantidade de agendamentos por horário</label>
                <input type="text"
                    class="form-control {{ $errors->has('qtd_atendentes') ? 'is-invalid' : '' }}"
                    name="qtd_atendentes"
                    id="qtd_atendentes"
                    value="{{ empty(old('qtd_atendentes')) && isset($resultado->qtd_atendentes) ? $resultado->qtd_atendentes : old('qtd_atendentes') }}"
                    maxlength="1"
                    required
                />
                @if($errors->has('qtd_atendentes'))
                <div class="invalid-feedback">
                    {{ $errors->first('qtd_atendentes') }}
                </div>
                @endif
                <small class="form-text text-muted">
                    <em>* Nesta regional pode ter, no máximo, <span id="totalAtendentes">{{ isset($resultado->regional->ageporhorario) ? $resultado->regional->ageporhorario : '' }}</span> agendamento(s) por horário</em>
                    <br />
                    <em>** Coloque 0 (zero) para bloquear o(s) horário(s) ou coloque mais de 0 (zero) para alterar a quantidade de agendamentos por horário</em>
                </small>
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

<script type="module" src="{{ asset('/js/interno/modulos/agenda-bloqueio.js?'.time()) }}" id="modulo-agenda-bloqueio" class="modulo-editar"></script>