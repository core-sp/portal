@extends('admin.layout.app')

@section('content')

<section class="content-header">
  @if(\Session::has('message'))
  <div class="container-fluid mb-2">
    <div class="row">
      <div class="col">
        <div class="alert alert-dismissible {{ \Session::get('class') }}">
          {!! \Session::get('message') !!}
        </div>
      </div>
    </div>
  </div>
  @endif
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">Ver {{ ucfirst($variaveis->singular) }}</h1>
        @if(isset($variaveis->btn_lista))
          {!! $variaveis->btn_lista !!}
        @endif
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
              Informações sobre {{ $variaveis->singulariza }}
            </div>
          </div>
          @if(isset($variaveis->mostra))
            @include('admin.views.'.$variaveis->mostra)
          @else
            @include('admin.views.'.$variaveis->singular)
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

@endsection