@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
  <div class="conteudo-txt-mini light w-100">
    <h4 class="pt-0 pb-0">Agendamento de Sala</h4>
    <div class="linha-lg-mini mb-3"></div>
    <p>Preencha as informações abaixo para agendar o uso de uma sala para reunião ou coworking.</p>
    <form action="{{-- route('representante.inserirSolicitarCedula') --}}" method="POST" id="agendamentoSala">
      @csrf
      <p>
        <span class="text-danger"><strong>*</strong></span><small><em> Preenchimento obrigatório</em></small>
      </p>

      <div class="form-row mb-2 cadastroRepresentante">
        <div class="col-sm mb-2-576">
          <label for="tipo_sala">Tipo de sala <span class="text-danger">*</span></label>
          <select 
            name="tipo_sala" 
            id="tipo_sala" 
            class="form-control {{ $errors->has('tipo_sala') ? 'is-invalid' : '' }}"
            required
          >
            <option value="">Selecione o tipo de sala</option>
            <option value="reuniao">Reunião</option>
            <option value="coworking">Coworking</option>
          </select>
          @if($errors->has('tipo_sala'))
          <div class="invalid-feedback">
            {{ $errors->first('tipo_sala') }}
          </div>
          @endif
        </div>

        <div class="col-sm mb-2-576">
          <label for="sala_reuniao_id">Regional <span class="text-danger">*</span></label>
          <select 
            name="sala_reuniao_id" 
            id="sala_reuniao_id" 
            class="form-control {{ $errors->has('sala_reuniao_id') ? 'is-invalid' : '' }}"
            required
          >
            <option value="">Selecione a regional, se disponível</option>
            @foreach($salas as $sala)
            <option value="{{ $sala->id }}" {{ old('sala_reuniao_id') == $sala->id ? 'selected' : '' }}>{{ $sala->regional->regional }}</option>
            @endforeach 
          </select>
          @if($errors->has('sala_reuniao_id'))
          <div class="invalid-feedback">
            {{ $errors->first('sala_reuniao_id') }}
          </div>
          @endif
        </div>
      </div>

      <div class="form-row mb-2 cadastroRepresentante">
        <div class="col-sm mb-2-576">
          <label for="datepicker">Dia <span class="text-danger">*</span> <span>( <i class="fa fa-square" style="color:red"></i> = sem horário disponível )</span></label>
          <div class="input-group">
            <input type="text" 
              class="form-control {{ $errors->has('dia') ? 'is-invalid' : '' }}"
              id="datepicker"
              name="dia"
              placeholder="Selecione a regional"
              readonly
              disabled
              required
            />
            @if($errors->has('dia'))
            <div class="invalid-feedback">
              {{ $errors->first('dia') }}
            </div>
            @endif
          </div>
          <div id="loadCalendario" class="loadImage">
            <img src="{{ asset('img/ajax-loader.gif') }}" alt="Loading">
          </div>
        </div>

        <div class="col-sm mb-2-576">
          <label for="periodo">Períodos disponíveis <span class="text-danger">*</span></label>
          <select 
            name="periodo" 
            id="periodo"
            class="form-control {{ $errors->has('periodo') ? 'is-invalid' : '' }}"
            disabled 
            required
          >
            <option value="" selected>Selecione o dia da reserva de sala</option>
          </select>
          @if($errors->has('periodo'))
          <div class="invalid-feedback">
            {{ $errors->first('periodo') }}
          </div>
          @endif
          <div id="loadHorario" class="loadImage">
            <img src="{{ asset('img/ajax-loader.gif') }}" alt="Loading">
          </div>
        </div>
      </div>

      <fieldset class="form-group border p-2 mt-3" style="display: none;">
        <legend class="w-auto"><small><i class="far fa-check-square text-success"></i> Itens da sala</small></legend>
        <p id="itensShow"></p>
      </fieldset>

      <fieldset class="form-group border p-2" style="display: none;" id="area_participantes">
        <p class="text-secondary"><span class="text-danger">*</span> <em>Deve ter pelo menos um participante</em></p>
        <legend class="w-auto"><small><i class="fas fa-users text-info"></i> Participantes</small></legend>
        <div class="form-row mb-2 cadastroRepresentante participante">
          <div class="col-sm mb-2-576">

            <div class="input-group mb-2-576">
              <div class="input-group-prepend">
                <span class="input-group-text">Participante:</span>
              </div>
              <input 
                type="text" 
                class="form-control col-3" 
                name="participantes_cpf[]" 
                placeholder="CPF"
              >
              <input 
                type="text" 
                class="form-control text-uppercase" 
                name="participantes_nome[]" 
                placeholder="Nome Completo"
              >
            </div>

          </div>
        </div>
      </fieldset>
            
      <div class="form-group float-right mt-4">
        <button type="submit" class="btn btn-primary">Agendar</button>
      </div>
    </form>

  </div>
</div>

<!-- The Modal -->
<div class="modal fade">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Atenção</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        Você é Representante Comercial?
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal">Sim</button>
        <button type="button" class="btn btn-secondary">Não</button>
      </div>
    </div>
  </div>
</div>

  <div id="dialog_agendamento" title="Atenção"></div>
</section>

@endsection
