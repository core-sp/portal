@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\LicitacaoHelper;
$modalidades = LicitacaoHelper::modalidades();
$situacoes = LicitacaoHelper::situacoes();
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Criar Licitação</h1>
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
              Preencha as informações para criar uma nova licitação
            </div>
          </div>
          <form role="form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="modalidade">Modalidade</label>
                  <select name="modalidade" class="form-control">
                    @foreach($modalidades as $modalidade)
                    <option value="{{ $modalidade }}">{{ $modalidade }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('modalidade'))
                  <div class="invalid-feedback">
                    {{ $errors->first('modalidade') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="nrlicitacao">Nº da Licitação</label>
                  <input type="text" class="form-control {{ $errors->has('nrlicitacao') ? 'is-invalid' : '' }}" placeholder="Número" name="nrlicitacao" />
                  @if($errors->has('nrlicitacao'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nrlicitacao') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="nrprocesso">Nº do Processo</label>
                  <input type="text" class="form-control {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}" placeholder="Número" name="nrprocesso" />
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
                    <option value="{{ $situacao }}">{{ $situacao }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('situacao'))
                  <div class="invalid-feedback">
                    {{ $errors->first('situacao') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col-6">
                  <label for="datarealizacao">Data de Realização</label>
                  <input type="datetime-local" class="form-control" name="datarealizacao">
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="objeto">Objeto da Licitação</label>
                <textarea name="objeto" class="form-control {{ $errors->has('objeto') ? 'is-invalid' : '' }} my-editor" id="conteudo" rows="10"></textarea>
                @if($errors->has('objeto'))
                <div class="invalid-feedback">
                  {{ $errors->first('objeto') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/licitacoes" class="btn btn-default">Cancelar</a>
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