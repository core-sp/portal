@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\CursoHelper;
$tipos = CursoHelper::tipos();
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Criar Curso</h1>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="card card-info">
          <div class="card-header">
            <div class="card-title">
              Preencha as informações para criar um novo curso
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="tipo">Tipo</label>
                  <select name="tipo" class="form-control">
                    @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('tipo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('tipo') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="tema">Tema</label>
                  <input type="text" class="form-control {{ $errors->has('tema') ? 'is-invalid' : '' }}" placeholder="Tema" name="tema" maxlength="191" />
                  @if($errors->has('tema'))
                  <div class="invalid-feedback">
                    {{ $errors->first('tema') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="datarealizacao">Data de Realização</label>
                  <input type="datetime-local" class="form-control {{ $errors->has('datarealizacao') ? 'is-invalid' : '' }}" name="datarealizacao">
                  @if($errors->has('datarealizacao'))
                  <div class="invalid-feedback">
                    {{ $errors->first('datarealizacao') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="duracao">Duração do evento</label>
                  <input type="number"
                    step="0.01"
                    class="form-control {{ $errors->has('duracao') ? 'is-invalid' : '' }}"
                    name="duracao"
                    min="1"
                    max="1000" />
                  @if($errors->has('duracao'))
                  <div class="invalid-feedback">
                    {{ $errors->first('duracao') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="endereco">Endereço</label>
                <input type="text"
                  name="endereco"
                  class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}"
                  maxlength="191" />
                @if($errors->has('endereco'))
                <div class="invalid-feedback">
                  {{ $errors->first('endereco') }}
                </div>
                @endif
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="nrvagas">Nº de vagas</label>
                  <input type="number"
                    name="nrvagas"
                    class="form-control {{ $errors->has('nrvagas') ? 'is-invalid' : '' }}"
                    min="1"
                    max="1000" />
                  @if($errors->has('nrvagas'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nrvagas') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="idregional">Regional</label>
                  <select name="idregional" class="form-control" id="idregional">
                    @foreach($regionais as $regional)
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" class="form-control my-editor {{ $errors->has('descricao') ? 'is-invalid' : '' }}" id="descricao" rows="10"></textarea>
                @if($errors->has('descricao'))
                <div class="invalid-feedback">
                  {{ $errors->first('descricao') }}
                </div>
                @endif
              </div>
              <div class="form-group mt-2">
                <label for="observacao">Observação</label>
                <textarea name="observacao" class="form-control {{ $errors->has('observacao') ? 'is-invalid' : '' }}" id="observacao" rows="3"></textarea>
                @if($errors->has('observacao'))
                <div class="invalid-feedback">
                  {{ $errors->first('observacao') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/concursos" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script type="text/javascript" src="{{ asset('js/tinymce.js') }}"></script>

@endsection