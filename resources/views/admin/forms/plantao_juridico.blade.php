<form method="POST" action="{{ route('plantao.juridico.editar', $resultado->id) }}">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <h4>Regional - {{ $resultado->regional->regional }}</h4>
        <p>
            <i>Obs: para desativar o plantão jurídico nesta regional, coloque 0 na quantidade de advogados</i>
            <br>
            <i>Obs 2: a quantidade de advogados determina quantos agendamentos permitidos em cada hora selecionada</i>
        </p>
        <div class="form-row">
            <div class="col-2">
                <label for="qtd_advogados">Quantidade de advogados</label>
                <input type="text"
                    class="form-control {{ $errors->has('qtd_advogados') ? 'is-invalid' : '' }}"
                    name="qtd_advogados"
                    id="qtd_advogados"
                    value="{{ old('qtd_advogados') ? old('qtd_advogados') : $resultado->qtd_advogados }}"
                    maxlength="1"
                    required
                />
                @if($errors->has('qtd_advogados'))
                <div class="invalid-feedback">
                    {{ $errors->first('qtd_advogados') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horarios">Horários p/ agendamento</label>
                <select 
                    name="horarios[]" 
                    class="form-control {{ $errors->has('horarios') ? 'is-invalid' : '' }}" 
                    id="horarios" 
                    multiple
                >
                    @php
                        $horarios = explode(',', $resultado->horarios);
                    @endphp
                    @foreach (todasHoras() as $hora)
                        <option value="{{ $hora }}" {{ in_array($hora, $horarios) ? 'selected' : '' }}>{{ $hora }}</option>
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
            <div class="col">
                <label for="dataInicial">Data inicial</label>
                <input 
                    type="date" 
                    name="dataInicial" 
                    class="form-control {{ $errors->has('dataInicial') ? 'is-invalid' : '' }}" 
                    id="dataInicial" 
                    value="{{ old('dataInicial') ? old('dataInicial') : $resultado->dataInicial }}"
                />

                @if($errors->has('dataInicial'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataInicial') }}
                </div>
                @endif

                <br>

                <label for="dataFinal">Data final</label>
                <input 
                    type="date" 
                    name="dataFinal" 
                    class="form-control {{ $errors->has('dataFinal') ? 'is-invalid' : '' }}" 
                    id="dataFinal"
                    value="{{ old('dataFinal') ? old('dataFinal') : $resultado->dataFinal }}" 
                />

                @if($errors->has('dataFinal'))
                <div class="invalid-feedback">
                    {{ $errors->first('dataFinal') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('plantao.juridico.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                Salvar
            </button>
        </div>
    </div>
</form>