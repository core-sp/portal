<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="idregional">Regional</label>
                <select name="idregional" class="form-control">
                @foreach($regionais as $regional)
                    @if(!empty(old('idregional')))
                        @if(old('idregional') == $regional->idregional)
                            <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                        @else
                            <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @else
                        @if(isset($resultado))
                            @if($resultado->idregional === $regional->idregional)
                            <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                            @else
                            <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                            @endif
                        @else
                        <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @endif
                @endforeach
                </select>
                @if($errors->has('regional'))
                <div class="invalid-feedback">
                {{ $errors->first('regional') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="diainicio">Data de início</label>
                <input type="text"
                    class="form-control {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}"
                    placeholder="dd/mm/aaaa"
                    id="dataInicio"
                    name="diainicio"
                    @if(!empty(old('diainicio')))
                        value="{{ old('diainicio') }}"
                    @else
                        @if(isset($resultado))
                            @if($resultado->diainicio != '2000-01-01')
                                value="{{ onlyDate($resultado->diainicio) }}"
                            @endif
                        @endif
                    @endif
                    />
                <small class="form-text text-muted">
                    <em>* Deixe o campo vazio para aplicar a regra a partir de hoje</em>
                </small>
                @if($errors->has('diainicio'))
                <div class="invalid-feedback">
                {{ $errors->first('diainicio') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="diatermino">Data de término</label>
                <input type="text"
                    class="form-control {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}"
                    placeholder="dd/mm/aaaa"
                    id="dataTermino"
                    name="diatermino"
                    @if(!empty(old('diatermino')))
                        value="{{ old('diatermino') }}"
                    @else
                        @if(isset($resultado))
                            @if($resultado->diatermino != '2100-01-01')
                                value="{{ onlyDate($resultado->diatermino) }}"
                            @endif
                        @endif
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
                <label for="horainicio">Hora de início</label>
                <select name="horainicio" class="form-control {{ $errors->has('horainicio') ? 'is-invalid' : '' }}" id="horaInicioBloqueio">
                <option selected disabled>Selecione o horário</option>
                @foreach(todasHoras() as $hora)
                    @if(!empty(old('horainicio')))
                        @if(old('horainicio') == $hora)
                            <option value="{{ $hora }}" selected>{{ $hora }}</option>
                        @else
                            <option value="{{ $hora }}">{{ $hora }}</option>
                        @endif
                    @else
                        @if(isset($resultado))
                            @if($resultado->horainicio === $hora)
                            <option value="{{ $hora }}" selected>{{ $hora }}</option>
                            @else
                            <option value="{{ $hora }}">{{ $hora }}</option>
                            @endif
                        @else
                        <option value="{{ $hora }}">{{ $hora }}</option>
                        @endif
                    @endif
                @endforeach
                </select>
                @if($errors->has('horainicio'))
                <div class="invalid-feedback">
                {{ $errors->first('horainicio') }}
                </div>
                @endif
            </div>
            <div class="col">
            <label for="horatermino">Hora de término</label>
                <select name="horatermino" class="form-control {{ $errors->has('horatermino') ? 'is-invalid' : '' }}" id="horaTerminoBloqueio">
                <option selected disabled>Selecione o horário</option>
                @foreach(todasHoras() as $hora)
                    @if(!empty(old('horatermino')))
                        @if(old('horatermino') == $hora)
                            <option value="{{ $hora }}" selected>{{ $hora }}</option>
                        @else
                            <option value="{{ $hora }}">{{ $hora }}</option>
                        @endif
                    @else
                        @if(isset($resultado))
                            @if($resultado->horatermino === $hora)
                            <option value="{{ $hora }}" selected>{{ $hora }}</option>
                            @else
                            <option value="{{ $hora }}">{{ $hora }}</option>
                            @endif
                        @else
                        <option value="{{ $hora }}">{{ $hora }}</option>
                        @endif
                    @endif
                @endforeach
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
            <a href="/admin/agendamentos/bloqueios" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
            @if(isset($resultado))
                Salvar
            @else
                Publicar
            @endif
            </button>
        </div>
    </div>
</form>