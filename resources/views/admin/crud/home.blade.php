@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1 class="d-inline mr-3 align-middle">{{ ucfirst($variaveis->plural) }}</h1>
        @if(isset($variaveis->btn_criar))
        {!! $variaveis->btn_criar !!}
        @endif
        @if(isset($variaveis->btn_lixeira))
        {!! $variaveis->btn_lixeira !!}
        @endif
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
            <h3 class="card-title d-inline">
              Lista de {{ $variaveis->plural }} do CORE-SP
            </h3>
            @if(isset($busca))
            <a href="/admin/{{ $variaveis->plural }}" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/{{ $variaveis->plural }}/busca">
                <input type="text"
                  name="q"
                  class="form-control float-right"
                  placeholder="Pesquisar" />
                <div class="input-group-append">
                  <button type="submit" class="btn btn-default">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </form>
            </div>
          </div>
          <div class="card-body">
            @if($resultados)
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