@php
    $tipos = App\Http\Controllers\Helpers\ChamadoControllerHelper::tipos();
    $prioridades = App\Http\Controllers\Helpers\ChamadoControllerHelper::prioridades();
@endphp

<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="tipo">Tipo</label>
                <select name="tipo" class="form-control">
                    @foreach($tipos as $tipo)
                        @if(!empty(old('tipo')))
                            @if(old('tipo') === $tipo)
                                <option value="{{ $tipo }}" selected>{{ $tipo }}</option>
                            @else
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endif
                        @else
                            @if(isset($resultado))
                                @if($tipo === $resultado->tipo)
                                    <option value="{{ $tipo }}" selected>{{ $tipo }}</option>
                                @else
                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                @endif
                            @else
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endif
                        @endif
                    @endforeach
                </select>
                @if($errors->has('tipo'))
                    <div class="invalid-feedback">
                        {{ $errors->first('tipo') }}
                    </div>
                @endif
            </div>
            <div class="col">
                <label for="prioridade">Prioridade</label>
                <select name="prioridade" class="form-control">
                    @foreach($prioridades as $prioridade)
                        @if(!empty(old('prioridade')))
                            @if(old('prioridade') === $prioridade)
                                <option value="{{ $prioridade }}" selected>{{ $prioridade }}</option>
                            @else
                                <option value="{{ $prioridade }}">{{ $prioridade }}</option>
                            @endif
                        @else
                            @if(isset($resultado))
                                @if($prioridade === $resultado->prioridade)
                                    <option value="{{ $prioridade }}" selected>{{ $prioridade }}</option>
                                @else
                                    <option value="{{ $prioridade }}">{{ $prioridade }}</option>
                                @endif
                            @else
                                <option value="{{ $prioridade }}">{{ $prioridade }}</option>
                            @endif
                        @endif
                    @endforeach
                </select>
                @if($errors->has('prioridade'))
                    <div class="invalid-feedback">
                        {{ $errors->first('prioridade') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="mensagem">Mensagem</label>
                <textarea name="mensagem"
                    class="form-control {{ $errors->has('mensagem') ? 'is-invalid' : '' }}"
                    id="mensagem"
                    placeholder="Descreva com detalhes sua solicitação"
                    rows="3">@if(!empty(old('mensagem'))){{old('mensagem')}}@else @if(isset($resultado)){{$resultado->mensagem}}@endif @endif</textarea>
                @if($errors->has('mensagem'))
                <div class="invalid-feedback">
                    {{ $errors->first('mensagem') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="lfm">Print</label>
                <div class="input-group">
                <span class="input-group-prepend">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                    <i class="fas fa-picture-o"></i> Inserir imagem
                    </a>
                </span>
                <input id="thumbnail"
                    class="form-control"
                    type="text"
                    name="img"
                    @if(!empty(old('img')))
                        value="{{ old('img') }}"
                    @else
                        @if(isset($resultado->img))
                            value="{{ $resultado->img }}"
                        @endif
                    @endif
                    placeholder="Se necessário, anexe um print à solicitação"
                    />
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                @if(isset($resultado))
                    Salvar
                @else
                    Registrar
                @endif
            </button>
        </div>
    </div>
</form>