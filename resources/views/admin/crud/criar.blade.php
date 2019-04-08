@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Criar {{ ucfirst($variaveis->singular) }}</h1>
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
              Preencha as informações para criar {{ $variaveis->singulariza }}
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

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script type="text/javascript" src="{{ asset('js/tinymce.js') }}"></script>

@endsection