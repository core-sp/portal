<form method="POST" action="{{ route('sala.reuniao.editar', $resultado->id) }}" id="form_salaReuniao">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    @php
        $todasHoras = todasHoras();
        array_push($todasHoras, '18:00');
        $manha = $resultado::horasManha();
        $tarde = $resultado::horasTarde();
    @endphp
    <div class="card-body">
        <h4>Regional - {{ $resultado->regional->regional }}</h4>

        <hr />

        <div class="mb-5">
            <h4 class="text-primary">Horário final dos períodos manhã e tarde</h4>
            <div class="form-row mb-3">
                <div class="col-sm mb-2-576">
                    <label for="hora_limite_final_manha">Horário do almoço</label>
                    <select 
                        name="hora_limite_final_manha" 
                        class="form-control {{ $errors->has('hora_limite_final_manha') ? 'is-invalid' : '' }}" 
                        id="hora_limite_final_manha" 
                    >
                    @foreach(array_slice($todasHoras, 4, 7) as $hora)
                        <option value="{{ $hora }}">
                            {{ $hora }}
                        </option>
                    @endforeach
                    </select>
                    @if($errors->has('hora_limite_final_manha'))
                    <div class="invalid-feedback">
                        {{ $errors->first('hora_limite_final_manha') }}
                    </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="hora_limite_final_tarde">Horário fim de expediente</label>
                    <select 
                        name="hora_limite_final_tarde" 
                        class="form-control {{ $errors->has('hora_limite_final_tarde') ? 'is-invalid' : '' }}" 
                        id="hora_limite_final_tarde" 
                    >
                    @foreach(array_slice($todasHoras, 12, 7) as $hora)
                        <option value="{{ $hora }}">
                            {{ $hora }}
                        </option>
                    @endforeach
                    </select>
                    @if($errors->has('hora_limite_final_tarde'))
                    <div class="invalid-feedback">
                        {{ $errors->first('hora_limite_final_tarde') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <hr />

        <div class="mb-5">
            <h4 class="text-primary"><i class="fas fa-briefcase"></i> - Sala de Reunião</h4>
            <p>
                <strong>Obs:</strong>
                <i>
                    para desativar a sala de reunião nesta regional, coloque zero (0) no total de participantes.
                    <br>
                    Somente <strong>um (1)</strong> agendamento por período (Manhã, Tarde).
                </i>
            </p>
            <div class="form-row mb-3">
                <div class="col-sm mb-2-576">
                    <label for="participantes_reuniao">Total de participantes</label>
                    <input type="text"
                        class="form-control {{ $errors->has('participantes_reuniao') ? 'is-invalid' : '' }}"
                        name="participantes_reuniao"
                        id="participantes_reuniao"
                        value="{{ old('participantes_reuniao') ? old('participantes_reuniao') : $resultado->participantes_reuniao }}"
                        maxlength="2"
                        required
                    />
                    @if($errors->has('participantes_reuniao'))
                    <div class="invalid-feedback">
                        {{ $errors->first('participantes_reuniao') }}
                    </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="manha_horarios_reuniao">Período Manhã</label>
                    <select 
                        name="manha_horarios_reuniao[]" 
                        class="form-control {{ $errors->has('manha_horarios_reuniao') || $errors->has('manha_horarios_reuniao.*') ? 'is-invalid' : '' }}" 
                        id="manha_horarios_reuniao" 
                        multiple
                    >
                    @foreach($manha as $horaRM)
                        <option 
                            value="{{ $horaRM }}" 
                            {{ (is_array(old('manha_horarios_reuniao')) && in_array($horaRM, old('manha_horarios_reuniao'))) || in_array($horaRM, $resultado->getHorariosManha('reuniao')) ? 
                                'selected' : '' }}
                            >
                            {{ $horaRM }}
                        </option>
                    @endforeach
                    </select>

                    @if($errors->has('manha_horarios_reuniao') || $errors->has('manha_horarios_reuniao.*'))
                    <div class="invalid-feedback">
                        {{ $errors->has('manha_horarios_reuniao') ? $errors->first('manha_horarios_reuniao') : $errors->first('manha_horarios_reuniao.*') }}
                    </div>
                    @endif

                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um horário ou Shift para selecionar um grupo de horários</em>
                    </small>
                </div>

                <div class="col-sm mb-2-576">
                    <label for="tarde_horarios_reuniao">Período Tarde</label>
                    <select 
                        name="tarde_horarios_reuniao[]" 
                        class="form-control {{ $errors->has('tarde_horarios_reuniao') || $errors->has('tarde_horarios_reuniao.*') ? 'is-invalid' : '' }}" 
                        id="tarde_horarios_reuniao" 
                        multiple
                    >
                    @foreach($tarde as $horaRT)
                        <option 
                            value="{{ $horaRT }}" 
                            {{ (is_array(old('tarde_horarios_reuniao')) && in_array($horaRT, old('tarde_horarios_reuniao'))) || in_array($horaRT, $resultado->getHorariosTarde('reuniao')) ? 
                                'selected' : '' }}
                            >
                            {{ $horaRT }}
                        </option>
                    @endforeach
                    </select>

                    @if($errors->has('tarde_horarios_reuniao') || $errors->has('tarde_horarios_reuniao.*'))
                    <div class="invalid-feedback">
                        {{ $errors->has('tarde_horarios_reuniao') ? $errors->first('tarde_horarios_reuniao') : $errors->first('tarde_horarios_reuniao.*') }}
                    </div>
                    @endif

                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um horário ou Shift para selecionar um grupo de horários</em>
                    </small>
                </div>
            </div>

            <div class="form-row">
                <div class="col-sm mb-2-576">
                    <label for="todos_itens_reuniao">Selecione os itens existentes na sala</label>
                    <select 
                        id="todos_itens_reuniao"
                        class="form-control" 
                        multiple
                    >
                    @foreach($resultado->getItensOriginaisReuniao() as $itemR)
                        <option value="{{ $itemR }}" >{{ $itemR }}</option>
                    @endforeach
                    </select>
                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um item ou Shift para selecionar um grupo de itens</em>
                        <br>
                        <br>
                    </small>
                    <button class="btn btn-sm btn-success float-left mt-2 addItem" type="button" id="btnAddReuniao">Adicionar itens <i class="fas fa-angle-double-right"></i></button>
                </div>

                <div class="col-sm mb-2-576">
                    <label for="itens_reuniao">Altere a unidade dos itens escolhidos, se necessário</label>
                    <select 
                        name="itens_reuniao[]" 
                        class="form-control {{ $errors->has('itens_reuniao') || $errors->has('itens_reuniao.*') ? 'is-invalid' : '' }}" 
                        id="itens_reuniao" 
                        multiple
                    >
                    @php
                        $itensR = $resultado->getItens('reuniao');
                    @endphp
                    @foreach($itensR as $itemEdit)
                        <option 
                            value="{{ $itemEdit }}" 
                            {{ (is_array(old('itens_reuniao')) && in_array($itemEdit, old('itens_reuniao'))) || in_array($itemEdit, $itensR) ? 
                                'selected' : '' }}
                            >
                            {{ $itemEdit }}
                        </option>
                    @endforeach
                    </select>
                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um item ou Shift para selecionar um grupo de itens</em>
                        <br>
                        <em>** Dê um duplo clique no item para editar um valor</em>
                    </small>

                    @if($errors->has('itens_reuniao') || $errors->has('itens_reuniao.*'))
                    <div class="invalid-feedback">
                        {{ $errors->has('itens_reuniao') ? $errors->first('itens_reuniao') : $errors->first('itens_reuniao.*') }}
                    </div>
                    @endif

                    <button class="btn btn-sm btn-danger float-left mt-2 removeItem" type="button" id="btnRemoveReuniao"><i class="fas fa-angle-double-left"></i> Remover itens</button>

                </div>
            </div>

        </div>

        <hr />

        <!-- COWORKING ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
        <div>
            <h4 class="text-primary"><i class="fas fa-laptop"></i> - Sala de Coworking</h4>
            <p>
                <strong>Obs:</strong>
                <i>
                    para desativar a sala de coworking nesta regional, coloque zero (0) no total de participantes.
                    <br>
                    O total de participantes determina quantos agendamentos são permitidos em cada período (Manhã, Tarde).
                </i>
            </p>
            <div class="form-row mb-3">
                <div class="col-sm mb-2-576">
                    <label for="participantes_coworking">Total de participantes</label>
                    <input type="text"
                        class="form-control {{ $errors->has('participantes_coworking') ? 'is-invalid' : '' }}"
                        name="participantes_coworking"
                        id="participantes_coworking"
                        value="{{ old('participantes_coworking') ? old('participantes_coworking') : $resultado->participantes_coworking }}"
                        maxlength="2"
                        required
                    />
                    @if($errors->has('participantes_coworking'))
                    <div class="invalid-feedback">
                        {{ $errors->first('participantes_coworking') }}
                    </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="manha_horarios_coworking">Período Manhã</label>
                    <select 
                        name="manha_horarios_coworking[]" 
                        class="form-control {{ $errors->has('manha_horarios_coworking') || $errors->has('manha_horarios_coworking.*') ? 'is-invalid' : '' }}" 
                        id="manha_horarios_coworking" 
                        multiple
                    >
                    @foreach($manha as $horaCM)
                        <option value="{{ $horaCM }}" {{ (is_array(old('manha_horarios_coworking')) && in_array($horaCM, old('manha_horarios_coworking'))) || in_array($horaCM, $resultado->getHorariosManha('coworking')) ? 'selected' : '' }}>
                            {{ $horaCM }}
                        </option>
                    @endforeach
                    </select>

                    @if($errors->has('manha_horarios_coworking') || $errors->has('manha_horarios_coworking.*'))
                    <div class="invalid-feedback">
                        {{ $errors->has('manha_horarios_coworking') ? $errors->first('manha_horarios_coworking') : $errors->first('manha_horarios_coworking.*') }}
                    </div>
                    @endif

                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um horário ou Shift para selecionar um grupo de horários</em>
                    </small>
                </div>

                <div class="col-sm mb-2-576">
                    <label for="tarde_horarios_coworking">Período Tarde</label>
                    <select 
                        name="tarde_horarios_coworking[]" 
                        class="form-control {{ $errors->has('tarde_horarios_coworking') || $errors->has('tarde_horarios_coworking.*') ? 'is-invalid' : '' }}" 
                        id="tarde_horarios_coworking" 
                        multiple
                    >
                    @foreach($tarde as $horaCT)
                        <option value="{{ $horaCT }}" {{ (is_array(old('tarde_horarios_coworking')) && in_array($horaCT, old('tarde_horarios_coworking'))) || in_array($horaCT, $resultado->getHorariosTarde('coworking')) ? 'selected' : '' }}>
                            {{ $horaCT }}
                        </option>
                    @endforeach
                    </select>

                    @if($errors->has('tarde_horarios_coworking') || $errors->has('tarde_horarios_coworking.*'))
                    <div class="invalid-feedback">
                        {{ $errors->has('tarde_horarios_coworking') ? $errors->first('tarde_horarios_coworking') : $errors->first('tarde_horarios_coworking.*') }}
                    </div>
                    @endif

                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um horário ou Shift para selecionar um grupo de horários</em>
                    </small>
                </div>

            </div>

            <div class="form-row">
                <div class="col-sm mb-2-576">
                    <label for="todos_itens_coworking">Selecione os itens existentes na sala</label>
                    <select 
                        id="todos_itens_coworking"
                        class="form-control" 
                        multiple
                    >
                    @foreach($resultado->getItensOriginaisCoworking() as $itemC)
                        <option value="{{ $itemC }}" >{{ $itemC }}</option>
                    @endforeach
                    </select>
                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um item ou Shift para selecionar um grupo de itens</em>
                        <br>
                        <br>
                    </small>
                    <button class="btn btn-sm btn-success float-left mt-2 addItem" type="button" id="btnAddCoworking">Adicionar itens <i class="fas fa-angle-double-right"></i></button>
                </div>

                <div class="col-sm mb-2-576">
                    <label for="itens_coworking">Altere a unidade dos itens escolhidos, se necessário</label>
                    <select 
                        name="itens_coworking[]" 
                        class="form-control {{ $errors->has('itens_coworking') || $errors->has('itens_coworking.*') ? 'is-invalid' : '' }}" 
                        id="itens_coworking" 
                        multiple
                    >
                    @php
                        $itensC = $resultado->getItens('coworking');
                    @endphp
                    @foreach($itensC as $itemCEdit)
                        <option 
                            value="{{ $itemCEdit }}" 
                            {{ (is_array(old('itens_coworking')) && in_array($itemCEdit, old('itens_coworking'))) || in_array($itemCEdit, $itensC) ? 
                                'selected' : '' }}
                            >
                            {{ $itemCEdit }}
                        </option>
                    @endforeach
                    </select>
                    <small class="form-text text-muted">
                        <em>* Segure Ctrl para selecionar mais de um item ou Shift para selecionar um grupo de itens</em>
                        <br>
                        <em>** Dê um duplo clique no item para editar um valor</em>
                    </small>

                    @if($errors->has('itens_coworking') || $errors->has('itens_coworking.*'))
                    <div class="invalid-feedback">
                        {{ $errors->has('itens_coworking') ? $errors->first('itens_coworking') : $errors->first('itens_coworking.*') }}
                    </div>
                    @endif

                    <button class="btn btn-sm btn-danger float-left mt-2 removeItem" type="button" id="btnRemoveCoworking"><i class="fas fa-angle-double-left"></i> Remover itens</button>

                </div>
            </div>

        </div>

    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('sala.reuniao.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                Salvar
            </button>
        </div>
    </div>
</form>

<!-- The Modal -->
<div class="modal fade" id="sala_reuniao_itens">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Editar unidade do item</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
        </div>
         <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-success" id="editar_item">Inserir</button>
        </div>
      </div>
    </div>
  </div>