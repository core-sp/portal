@extends('admin.layout.app')

@section('content')

@php
use App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Editar {{ ucfirst($variaveis->singular) }}</h1>
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
              Preencha as informações para editar o {{ $variaveis->singular }}
            </div>
          </div>
          @include('admin.crud.forms.'.$variaveis->singular)
        </div>
      </div>
    </div>
    @if($resultado)
    <div class="row mt-2 mb-4">
      <div class="col">
        @if($resultado->updated_at == $resultado->created_at)
        <div class="callout callout-info">
          <strong>Criado por:</strong> {{ $resultado->user->nome }}, às {{ Helper::organizaData($resultado->created_at) }}
        </div>
        @else
        <div class="callout callout-info">
          <strong>Ultima alteração:</strong> {{ $resultado->user->nome }}, às {{ Helper::organizaData($resultado->updated_at) }}
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>
</section>

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script type="text/javascript" src="{{ asset('js/tinymce.js') }}"></script>

@endsection