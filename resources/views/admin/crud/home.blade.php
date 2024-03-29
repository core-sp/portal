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
      <div class="col">
        <h1 class="d-inline mr-3 align-middle">
          {{ ucfirst($variaveis->pluraliza) }}
          @if(isset($variaveis->continuacao_titulo))
            {!! $variaveis->continuacao_titulo !!}
          @endif
        </h1>
        @if(isset($variaveis->btn_criar))
        {!! $variaveis->btn_criar !!}
        @endif
        @if(auth()->user()->isAdmin())
          @if(isset($variaveis->btn_lixeira))
            {!! $variaveis->btn_lixeira !!}
          @endif
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
              <a href="/admin/{{ isset($variaveis->slug) ? $variaveis->slug : $variaveis->plural }}" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
            @endif
            <div class="card-tools">
              <form class="input-group input-group-sm"
                method="GET"
                role="form"
                action ="/admin/{{ isset($variaveis->busca) ? $variaveis->busca : $variaveis->plural }}/busca"
              >
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
            @if(isset($variaveis->filtro))
              @if(isset($variaveis->mostraFiltros))
              <div class="row mb-4">
                <div class="col-sm-auto align-self-end">
                  <p class="d-inline"><small>Filtro:</small></p>
                </div>
                {!! $variaveis->filtro !!}
                @if(isset($temFiltro))
                <div class="col-sm-auto align-self-end">
                  <small>
                    <a href="/admin/{{ $variaveis->plural }}" class="text-danger pointer">
                      <i class="fas fa-times"></i>&nbsp;&nbsp;Remover filtro
                    </a>
                  </small>
                </div>
                @endif
              </div>
              @endif
            @endif
            @if(isset($busca))
              <div class="row mb-3">
                <div class="col">
                  Mostrando resultados para a busca: <strong>{{ $busca }}</strong>
                </div>
              </div>
            @endif
            @if(isset($variaveis->addonsHome))
              {!! $variaveis->addonsHome !!}
            @endif
            @if(count($resultados) > 0)
            {!! $tabela !!}
            @else
              @if(isset($busca))
              <hr />
              <strong>Nenhum {{ $variaveis->singular }} encontrado</strong>
              <a href="/admin/{{ isset($variaveis->slug) ? $variaveis->slug : $variaveis->plural }}" class="badge badge-primary d-inline ml-2">Mostrar todos</a>
              @else
              <strong>Nenhum {{ $variaveis->singular }} encontrado</strong>
              @endif
            @endif
          </div>
          <div class="card-footer">
            @if($resultados)
            <div class="row">
              <div class="col-sm-5 align-self-center">
              @if($resultados instanceof \Illuminate\Pagination\LengthAwarePaginator)
                @if($resultados->count() > 1)
                  Exibindo {{ $resultados->firstItem() }} a {{ $resultados->lastItem() }} {{ $variaveis->plural }} de {{ $resultados->total() }} resultados.
                @endif
              @endif
              </div>
              <div class="col-sm-7">
                <div class="float-right">
                  @if($resultados instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $resultados->appends(request()->input())->links() }}
                  @endif
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