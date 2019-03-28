@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\BdoOportunidadeControllerHelper;
$status = BdoOportunidadeControllerHelper::status();
$segmentos = BdoOportunidadeControllerHelper::segmentos();
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Criar Oportunidade</h1>
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
              Preencha as informações para criar uma nova oportunidade
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            <input type="hidden" name="empresa" value="{{ $empresa->idempresa }}">
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="empresafake">Empresa</label>
                  <input type="text" name="empresafake" class="form-control" placeholder="{{ $empresa->razaosocial }}" readonly />
                </div>
                <div class="col">
                  <label for="segmento">Segmento</label>
                  <select name="segmento" class="form-control" id="segmento">
                    @foreach($segmentos as $segmento)
                    <option value="{{ $segmento }}">{{ $segmento }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('segmento'))
                  <div class="invalid-feedback">
                    {{ $errors->first('segmento') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
              	<div class="col">
                  <label for="status">Status</label>
                  <select name="status" class="form-control">
                    @foreach($status as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('status'))
                  <div class="invalid-feedback">
                    {{ $errors->first('status') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="vagasdisponiveis">Vagas Disponíveis</label>
                  <input type="number" class="form-control {{ $errors->has('vagasdisponiveis') ? 'is-invalid' : '' }}" name="vagasdisponiveis">
                  @if($errors->has('vagasdisponiveis'))
                  <div class="invalid-feedback">
                    {{ $errors->first('vagasdisponiveis') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="descricao">Descrição</label>
                <textarea name="descricao" class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}" id="descricao" rows="10"></textarea>
                @if($errors->has('descricao'))
                <div class="invalid-feedback">
                  {{ $errors->first('descricao') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/bdo" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection