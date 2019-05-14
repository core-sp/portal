@php
use App\Http\Controllers\Helpers\ConcursoHelper;
use App\Http\Controllers\Helper;
$modalidades = ConcursoHelper::modalidades();
$situacoes = ConcursoHelper::situacoes();
@endphp

<form role="form" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($resultado))
    {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
      <div class="form-row">
        <div class="col-sm-3">
          <label for="modalidade">Modalidade</label>
          <select name="modalidade" class="form-control">
          @foreach($modalidades as $modalidade)
            @if(isset($resultado))
              @if($resultado->modalidade === $modalidade)
              <option value="{{ $modalidade }}" selected>{{ $modalidade }}</option>
              @else
              <option value="{{ $modalidade }}">{{ $modalidade }}</option>
              @endif
            @else
            <option value="{{ $modalidade }}">{{ $modalidade }}</option>
            @endif
          @endforeach
          </select>
          @if($errors->has('modalidade'))
          <div class="invalid-feedback">
          {{ $errors->first('modalidade') }}
          </div>
          @endif
        </div>
        <div class="col">
          <label for="titulo">Título</label>
          <input type="text"
          class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
          name="titulo"
          placeholder="Título"
          @if(isset($resultado))
          value="{{ $resultado->titulo }}"
          @endif
          />
          @if($errors->has('titulo'))
          <div class="invalid-feedback">
          {{ $errors->first('titulo') }}
          </div>
          @endif
        </div>
      </div>
      <div class="form-row mt-2">
        <div class="col">
          <label for="nrprocesso">Nº do Processo</label>
          <input type="text"
          class="form-control nrprocessoInput {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}"
          placeholder="Número"
          name="nrprocesso"
          maxlength="19"
          @if(isset($resultado))
          value="{{ $resultado->nrprocesso }}"
          @endif
          />
          @if($errors->has('nrprocesso'))
          <div class="invalid-feedback">
          {{ $errors->first('nrprocesso') }}
          </div>
          @endif
        </div>
        <div class="col">
          <label for="situacao">Situação</label>
          <select name="situacao" class="form-control">
          @foreach($situacoes as $situacao)
            @if(isset($resultado))
              @if($resultado->situacao === $situacao)
              <option value="{{ $situacao }}" selected>{{ $situacao }}</option>
              @else
              <option value="{{ $situacao }}">{{ $situacao }}</option>
              @endif
            @else
            <option value="{{ $situacao }}">{{ $situacao }}</option>
            @endif
          @endforeach
          </select>
          @if($errors->has('situacao'))
          <div class="invalid-feedback">
          {{ $errors->first('situacao') }}
          </div>
          @endif
        </div>
        <div class="col">
          <label for="datarealizacao">Data de Realização</label>
          <input type="text"
            class="form-control dataInput"
            name="datarealizacao"
            placeholder="dd/mm/aaaa"
            @if(isset($resultado))
            value="{{ Helper::onlyDate($resultado->datarealizacao) }}"
            @endif
            />
        </div>
        <div class="col">
          <label for="horainicio">Horário de Início</label>
          <input type="text" 
            class="form-control timeInput" 
            name="horainicio"
            placeholder="00:00"
            @if(isset($resultado))
            value="{{ Helper::onlyHour($resultado->datarealizacao) }}"
            @endif
            />
          @if($errors->has('horainicio'))
          <div class="invalid-feedback">
          {{ $errors->first('horainicio') }}
          </div>
          @endif
        </div>
      </div>
      <div class="form-group mt-2">
        <label for="linkexterno">Link Oficial do Concurso</label>
        <input type="text"
          class="form-control {{ $errors->has('linkexterno') ? 'is-invalid' : '' }}"
          name="linkexterno"
          id="linkexterno"
          placeholder="Insira a URL"
          @if(isset($resultado))
          value="{{ $resultado->linkexterno }}"
          @endif
          />
        @if($errors->has('linkexterno'))
        <div class="invalid-feedback">
          {{ $errors->first('linkexterno') }}
        </div>
        @endif
      </div>
      <div class="form-group">
        <label for="objeto">Objeto do Concurso</label>
        <textarea name="objeto"
          class="form-control {{ $errors->has('objeto') ? 'is-invalid' : '' }} my-editor"
          id="conteudo"
          rows="10">
      @if(isset($resultado))
      {!! $resultado->objeto !!}
      @endif    
      </textarea>
        @if($errors->has('objeto'))
        <div class="invalid-feedback">
          {{ $errors->first('objeto') }}
        </div>
        @endif
      </div>
    </div>
    <div class="card-footer">
      <div class="float-right">
      <a href="/admin/concursos" class="btn btn-default">Cancelar</a>
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