@php
use App\Http\Controllers\Helper;
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;
$horas = AgendamentoControllerHelper::todasHoras();
@endphp

<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
    {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="idregional">Regional</label>
                <select name="idregional" class="form-control">
                @foreach($regionais as $regional)
                    @if(isset($resultado))
                        @if($resultado->idregional === $regional->idregional)
                        <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                        @else
                        <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @else
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
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
                    class="form-control dataInput {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}"
                    placeholder="dd/mm/aaaa"
                    name="diainicio"
                    @if(isset($resultado))
                    value="{{ Helper::OnlyDate($resultado->diainicio) }}"
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
                <input type="text"
                    class="form-control dataInput {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}"
                    placeholder="dd/mm/aaaa"
                    name="diatermino"
                    @if(isset($resultado))
                    value="{{ Helper::OnlyDate($resultado->diatermino) }}"
                    @endif
                    />
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
                <select name="horainicio" class="form-control">
                @foreach($horas as $hora)
                    @if(isset($resultado))
                        @if($resultado->horainicio === $hora)
                        <option value="{{ $hora }}" selected>{{ $hora }}</option>
                        @else
                        <option value="{{ $hora }}">{{ $hora }}</option>
                        @endif
                    @else
                    <option value="{{ $hora }}">{{ $hora }}</option>
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
                <select name="horatermino" class="form-control">
                @foreach($horas as $hora)
                    @if(isset($resultado))
                        @if($resultado->horatermino === $hora)
                        <option value="{{ $hora }}" selected>{{ $hora }}</option>
                        @else
                        <option value="{{ $hora }}">{{ $hora }}</option>
                        @endif
                    @else
                    <option value="{{ $hora }}">{{ $hora }}</option>
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
    <div class="card-footer float-right">
        <a href="/admin/agendamentos/bloqueios" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Salvar
        @else
            Publicar
        @endif
        </button>
    </div>
    </form>