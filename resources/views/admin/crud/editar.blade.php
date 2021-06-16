@extends('admin.layout.app')

@section('content')

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
              Preencha as informações para editar {{ $variaveis->singulariza }}
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
    @if(!isset($variaveis->cancela_idusuario))
      @if(isset($resultado->idusuario))
      <div class="row mt-2 mb-4">
        <div class="col">
          @if($resultado->updated_at == $resultado->created_at)
          <div class="callout callout-info">
            <strong>Criado por:</strong> {{ isset($resultado->user->nome) ? $resultado->user->nome : 'Usuário Deletado' }}, às {{ organizaData($resultado->created_at) }}
          </div>
          @else
          <div class="callout callout-info">
            <strong>Última alteração:</strong> {{ isset($resultado->user->nome) ? $resultado->user->nome : 'Usuário Deletado' }}, às {{ organizaData($resultado->updated_at) }}
          </div>
          @endif
        </div>
      </div>
      @endif
    @endif
  </div>
</section>

<!-- <script src="//cdn.tinymce.com/4/tinymce.min.js"></script> -->
<script src="{{'https://cdn.tiny.cloud/1/' . env('TINY_API_KEY') . '/tinymce/5/tinymce.min.js'}}" referrerpolicy="origin"></script>
<script type="text/javascript" src="{{ asset('js/tinymce.js?'.time()) }}"></script>

@endsection