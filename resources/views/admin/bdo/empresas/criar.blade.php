@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\BdoEmpresaControllerHelper;
$segmentos = BdoEmpresaControllerHelper::segmentos();
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Nova Empresa</h1>
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
              Preencha as informações para adicionar uma nova empresa
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="segmento">Segmento</label>
                  <select name="segmento" class="form-control">
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
                <div class="col">
                  <label for="cnpj">CNPJ</label>
                  <input type="text" class="form-control {{ $errors->has('cnpj') ? 'is-invalid' : '' }}" placeholder="CNPJ" name="cnpj" maxlength="191" />
                  @if($errors->has('cnpj'))
                  <div class="invalid-feedback">
                    {{ $errors->first('cnpj') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="razaosocial">Razão Social</label>
                  <input type="text" class="form-control {{ $errors->has('razaosocial') ? 'is-invalid' : '' }}" name="razaosocial" placeholder="Razão Social">
                  @if($errors->has('razaosocial'))
                  <div class="invalid-feedback">
                    {{ $errors->first('razaosocial') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="endereco">Endereço</label>
                  <input type="text" class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}" name="endereco" placeholder="Endereço">
                  @if($errors->has('endereco'))
                  <div class="invalid-feedback">
                    {{ $errors->first('endereco') }}
                  </div>
                  @endif
                </div>
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/bdo/empresas" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection