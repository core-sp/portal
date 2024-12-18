@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>{{ $variaveis->titulo_criar }}</h1>
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
              {{ isset($variaveis->muda_criar) ? $variaveis->muda_criar : 'Preencha as informações para criar ' . $variaveis->singulariza }}
            </div>
          </div>
          @if(isset($variaveis->form))
            @include('admin.forms.'.$variaveis->form)
          @else
            @include('admin.forms.'.$variaveis->singular)
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

@endsection