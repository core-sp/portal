@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
  <div class="conteudo-txt-mini light w-100">
    <h4 class="pt-0 pb-0">Agendamento de Sala</h4>
    <div class="linha-lg-mini mb-3"></div>

    @if($errors->has('participante_vetado') || $errors->has('participantes_cpf') || $errors->has('participantes_cpf.*') || $errors->has('participantes_nome') || $errors->has('participantes_nome.*'))
    <div class="d-block w-100">
      <p class="alert alert-danger">
        <i class="fas fa-times"></i>&nbsp;&nbsp;
        @if($errors->has('participante_vetado'))
        {!! $errors->first('participante_vetado') !!}
        @elseif($errors->has('participantes_cpf.*') || $errors->has('participantes_nome.*'))
        {{ $errors->has('participantes_cpf.*') ? $errors->first('participantes_cpf.*') : $errors->first('participantes_nome.*') }}
        @else
        {{ $errors->has('participantes_cpf') ? $errors->first('participantes_cpf') : $errors->first('participantes_nome') }}
        @endif
      </p>
    </div>
    @endif
    
    <p>Preencha as informações abaixo para agendar o uso de uma sala para reunião ou coworking.</p>

  @if(isset($salas))
    <form action="{{ route('representante.agendar.inserir.post', 'agendar') }}" method="POST" id="agendamentoSala">
  @elseif(isset($agendamento))
    <form action="{{ route('representante.agendar.inserir.put', ['acao' => $acao, 'id' => $agendamento->id]) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
  @endif

      @csrf
      <p>
        <span class="text-danger"><strong>*</strong></span><small><em> Preenchimento obrigatório</em></small>
      </p>

      <div class="form-row mb-2 cadastroRepresentante">
        <div class="col-sm mb-2-576">
          <label for="tipo_sala">Tipo de sala <span class="text-danger">*</span></label>
          <select 
            name="tipo_sala" 
            id="{{ isset($agendamento) ? '' : 'tipo_sala' }}" 
            class="form-control {{ $errors->has('tipo_sala') ? 'is-invalid' : '' }}"
            required
          >
          @if(isset($agendamento))
          <option value="{{ $agendamento->tipo_sala }}">{{ $agendamento->getTipoSala() }}</option>
          @else
            <option value="">Selecione o tipo de sala</option>
            <option value="reuniao" {{ old('tipo_sala') == "reuniao" ? 'selected' : '' }}>Reunião</option>
            <option value="coworking" {{ old('tipo_sala') == "coworking" ? 'selected' : '' }}>Coworking</option>
          @endif
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
            id="{{ isset($agendamento) ? '' : 'sala_reuniao_id' }}" 
            class="form-control {{ $errors->has('sala_reuniao_id') ? 'is-invalid' : '' }}"
            required
          >
          @if(isset($agendamento))
          <option value="{{ $agendamento->sala_reuniao_id }}">{{ $agendamento->sala->regional->regional }}</option>
          @else
            <option value="">Selecione a regional, se disponível</option>
            @foreach($salas as $sala)
            <option value="{{ $sala->id }}" {{ old('sala_reuniao_id') == $sala->id ? 'selected' : '' }}>{{ $sala->regional->regional }}</option>
            @endforeach 
          @endif
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
          <label for="datepicker">Dia <span class="text-danger">*</span> 
            <span>
              ( <i class="fa fa-square" style="color:red"></i> = sem período disponível, <i class="far fa-square"></i> = seu agendamento )
            </span>
          </label>
          <div class="input-group">
            <input type="text" 
              class="form-control {{ $errors->has('dia') ? 'is-invalid' : '' }}"
              id="{{ isset($agendamento) ? '' : 'datepicker' }}"
              name="dia"
              placeholder="Selecione a regional"
              readonly
              {{ isset($agendamento) ? '' : 'disabled' }}
              required
              value="{{ isset($agendamento) ? onlyDate($agendamento->dia) : '' }}"
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
            id="{{ isset($agendamento) ? '' : 'periodo' }}"
            class="form-control {{ $errors->has('periodo') ? 'is-invalid' : '' }}"
            disabled 
            required
          >
          @if(isset($agendamento))
          <option value="{{ $agendamento->periodo }}">{{ $agendamento->getPeriodo() }}</option>
          @else
            <option value="" selected>Selecione o dia da reserva de sala</option>
          @endif
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

      <fieldset class="form-group border p-2 mt-3" style="display: {{ isset($agendamento) ? 'block' : 'none' }};">
        <legend class="w-auto"><small><i class="far fa-check-square text-success"></i> Itens da sala</small></legend>
        <p id="itensShow">
      @if(isset($agendamento))
        @foreach($agendamento->sala->getItensHtml('reuniao') as $i => $item)
          {!! $i == 0 ? $item : '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;'.$item !!}
        @endforeach
      @endif
        </p>
      </fieldset>

      @if(isset($salas) || (isset($agendamento) && $agendamento->isReuniao()))
      <fieldset class="form-group border p-2" style="display: {{ isset($agendamento) ? 'block' : 'none' }};" id="area_participantes">
        <p class="text-secondary"><span class="text-danger">*</span> <em>Deve ter pelo menos um participante além do responsável</em></p>
        <legend class="w-auto"><small><i class="fas fa-users text-info"></i> Participantes</small></legend>
      @endif

        @if(isset($salas) || (isset($agendamento) && $agendamento->isReuniao()))
        <div class="form-row mb-2 cadastroRepresentante">
          <div class="col-sm mb-2-576">

            <div class="input-group mb-2-576">
              <div class="input-group-prepend">
                <span class="input-group-text">Participante Responsável:</span>
              </div>
              <input 
                type="text" 
                class="form-control col-3" 
                value="{{ auth()->guard('representante')->user()->cpf_cnpj }}"
                disabled
                readonly
              >
              <input 
                type="text" 
                class="form-control text-uppercase" 
                value="{{ auth()->guard('representante')->user()->nome }}"
                disabled
                readonly
              >
            </div>

          </div>
        </div>
        @endif

      @if(isset($agendamento) && $agendamento->isReuniao())
        @foreach($agendamento->getParticipantesComTotal() as $cpf => $nome)
        <div class="form-row mb-2 cadastroRepresentante participante">
          <div class="col-sm mb-2-576">

            <div class="input-group mb-2-576">
              <div class="input-group-prepend">
                <span class="input-group-text">Participante:</span>
              </div>
              <input 
                type="text" 
                class="form-control col-3 cpfInput" 
                name="participantes_cpf[]" 
                placeholder="CPF"
                value="{{ isset($agendamento) && (strlen($cpf) > 9) ? $cpf : '' }}"
                {{ isset($agendamento) && ($acao != 'editar') ? 'disabled' : '' }}
              >
              <input 
                type="text" 
                class="form-control text-uppercase" 
                name="participantes_nome[]" 
                placeholder="Nome Completo"
                value="{{ isset($agendamento) ? $nome : '' }}"
                {{ isset($agendamento) && ($acao != 'editar') ? 'disabled' : '' }}
              >
            </div>

          </div>
        </div>
        @endforeach
      @elseif(isset($salas))
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
                {{ isset($agendamento) && ($acao != 'editar') ? 'disabled' : '' }}
              >
              <input 
                type="text" 
                class="form-control text-uppercase" 
                name="participantes_nome[]" 
                placeholder="Nome Completo"
                {{ isset($agendamento) && ($acao != 'editar') ? 'disabled' : '' }}
              >
            </div>

          </div>
        </div>
      @endif
      </fieldset>

      @if(isset($agendamento) && ($agendamento->podeJustificar()))
      <fieldset class="form-group border p-2">
        <legend class="w-auto"><small><i class="fas fa-marker"></i> Justificar</small></legend>

        <div class="col-sm mb-2-576">
          <label for="justificativa">Insira a justificativa <span class="text-danger">*</span></label>
          <textarea 
            name="justificativa" 
            rows="5" 
            class="form-control {{ $errors->has('justificativa') ? 'is-invalid' : '' }}"
            id="justificativa"
            maxlength="1000"
            required
            autofocus
          >{{ old('justificativa') }}</textarea>
          @if($errors->has('justificativa'))
          <div class="invalid-feedback">
            {{ $errors->first('justificativa') }}
          </div>
          @endif
        </div>

        <div class="col-sm mb-2-576 mt-2">
          <label>Comprovante <small><em>(opcional) - somente .jpg, .jpeg, .png, .pdf com até 2MB</em></small></label>
          <div class="custom-file">
            <input
              type="file"
              name="anexo_sala"
              class="custom-file-input {{ $errors->has('anexo_sala') ? 'is-invalid' : '' }}"
              id="comprovante-justificativa"
              accept="image/png, image/jpeg, image/jpg, application/pdf"
            >
            <label class="custom-file-label" for="comprovante-justificativa">Selecionar arquivo...</label>
            @if($errors->has('anexo_sala'))
            <div class="invalid-feedback">
              {{ $errors->first('anexo_sala') }}
            </div>
            @endif
          </div>
        </div>

      </fieldset>
      @endif
            
      <div class="form-group float-right mt-4">
        <a href="{{ route('representante.agendar.inserir.view') }}" class="btn btn-secondary link-nostyle mr-2">Voltar</a>
        <button type="submit" class="btn btn-{{ $acao == 'cancelar' ? 'danger' : 'primary' }}">
        @switch($acao)
          @case('editar')
            Editar
            @break
          @case('cancelar')
            Cancelar
            @break
          @case('justificar')
            Justificar
            @break
          @default
            Agendar
        @endswitch
        </button>
      </div>
    </form>

    @if(isset($agendamento))
    <div class="float-left mt-4">
      <span><small><em>Criado em: {{ formataData($agendamento->created_at) }}</em></small></span>
    </div>
    @endif
  </div>
</div>

  <div id="dialog_agendamento" title="Atenção"></div>
</section>

@endsection
