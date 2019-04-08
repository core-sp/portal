@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">{{ ucfirst($variaveis->titulo) }}</h1>
        {!! $variaveis->btn_lista !!}
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              Lista de {{ $variaveis->plural }} deletados do CORE-SP
            </h3>
          </div>
          <div class="card-body">
            @if(isset($resultados))
            {!! $tabela !!}
            @else
            Nenhum {{ $variaveis->singular }} encontrado
            <a href="/admin/{{ $variaveis->plural }}" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
          </div>
          <div class="card-footer">
            @if($resultados)
            <div class="row">
              <div class="col-sm-5 align-self-center">
              @if($resultados->count() > 1)
              Exibindo {{ $resultados->firstItem() }} a {{ $resultados->lastItem() }} resultados de um total de {{ $resultados->total() }}.
              @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  {{ $resultados->links() }}
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection