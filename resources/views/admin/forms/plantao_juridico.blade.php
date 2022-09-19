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
            <i>Obs 2: a quantidade de advogados determina quantos agendamentos são permitidos em cada hora selecionada</i>
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
                    class="form-control {{ $errors->has('horarios') || $errors->has('horarios.*') ? 'is-invalid' : '' }}" 
                    id="horarios" 
                    multiple
                >
                @php
                    $horarios = explode(',', $resultado->horarios);
                @endphp
                @foreach(todasHoras() as $hora)
                    <option value="{{ $hora }}" {{ (!empty(old('horarios')) && is_array(old('horarios')) && in_array($hora, old('horarios'))) || in_array($hora, $horarios) ? 'selected' : '' }}>{{ $hora }}</option>
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

    @if(isset($resultado) && $resultado->ativado() && isset($agendamentos))
        @if($agendamentos->isEmpty())
        <p class="mt-5"><strong>Ainda não há agendados</strong></p>
        @else
        <div class="col mt-5">
            <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#demo">Agendados</button>
            <div id="demo" class="collapse mt-2">
                <p><strong>Total de agendamentos deste plantão ativo já cadastrados</strong></p>            
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Total p/ cada hora</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($agendamentos as $dia => $value)
                        <tr>
                            <td>{{ onlyDate($dia) }}</td>
                            <td>
                            @foreach($value as $hora => $total)
                                {{ $total->count().' agendado(s) às '.$hora }} <i class="fas fa-grip-lines-vertical" style="font-size:16px;color:red"></i>
                            @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endif

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