@extends('site.layout.app', ['title' => 'Teste APIs Gerenti'])

@section('description')
<meta name="description" content="Teste teste teste ===============================================================" />
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-simulador.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Teste das APIs do Gerenti =============================
        </h1>
      </div>
    </div>
  </div>
</section>


<section id="pagina-busca">
  <div class="container">
    <p class="bold text-danger mb-2">!!!!!!!!! Resultado no final da página !!!!!!!!!!!!!!!</p>

    <!-- REPRESENTANTE REGISTRADO -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">REPRESENTANTE REGISTRADO (retorna ass_id usado na maioria dos endpoints)</h3>
        <form method="post" action="{{ route('api-representante-registrado') }}" class="w-100 simulador">
          @csrf
          <div class="form-row">
            <div class="col-sm mb-2-576">
              <label for="registro">REGISTRO do Representante</label>
              <input type="text" name="registro" class="form-control" />
            </div>
            <div class="col-sm mb-2-576">
              <label for="cpf_cnpj">CPF / CNPJ do Representante</label>
              <input type="text" name="cpf_cnpj" class="form-control" />
            </div>
            <div class="col-sm mb-2-576">
              <label for="email">E-MAIL do Representante</label>
              <input type="text" name="email" class="form-control" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-2">Recuperar ass_id</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- SIMULADOR -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">SIMULADOR</h3>
        <form method="post" action="{{ route('api-simulador') }}" class="w-100 simulador">
          @csrf
          <div class="form-row">
            <div class="col-sm mb-2-576">
              <label for="tipoAssociado">Tipo de Pessoa</label>
              <select name="tipoAssociado" class="form-control">
                @foreach([1 => 'Pessoa Jurídica', 2 => 'Pessoa Física', 5 => 'Pessoa Física RT'] as $key => $tipo)
                <option value="{{ $key }}">{{ $tipo }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm mb-2-576">
              <label for="dataInicio">Data de início das atividades</label>
              <input type="date" name="dataInicio" class="form-control" autocomplete="off" min="1900-01-01" max="{{ now()->format('Y-m-d') }}" value="{{ now()->format('Y-m-d') }}" />
            </div>
            <div class="col-sm mb-2-576">
              <label for="capitalSocial">Capital Social</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">R$</span>
                </div>
                <input type="text" id="capitalSocial" name="capitalSocial" class="form-control capitalSocial" value="1,00" maxlength="15" />
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-sm mt-2">Simular</button>
        </form>

      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- TIPOS CONTATOS -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">TIPOS CONTATOS (usado para representante verificar os contatos que pode incluir)</h3>
        <form method="get" action="{{ route('api-tipos-contatos') }}" class="w-100 simulador">
          <button type="submit" class="btn btn-primary btn-sm mt-2">Verificar</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- CONTATOS REPRESENTANTE -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">CONTATOS POR REPRESENTANTE</h3>
        <form method="get" action="{{ route('api-contatos') }}" class="w-100 simulador">
          <div class="form-row">
            <div class="col-sm mb-2-576">
              <label for="ass_id">ASS ID do Representante</label>
              <input type="text" name="ass_id" class="form-control" />
            </div>
            <div class="col-sm mb-2-576">
              <label for="tipo">Filtro por tipo (opcional)</label>
              <select name="tipo" class="form-control">
                <option value="">Filtrar por tipo de contato...</option>
                @foreach(['Telefone' => 'Telefone', 'Celular' => 'Celular', 'Email' => 'E-mail', 'TelReferencia' => 'Telefonia de Referência', 'HomePage' => 'Site', 'Fax' => 'Fax', 'TelContato' => 'Telefone de Contato'] as $key => $tipo)
                <option value="{{ $key }}">{{ $tipo }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-2">Recuperar contatos</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- ENDEREÇOS REPRESENTANTE -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">ENDEREÇOS POR REPRESENTANTE</h3>
        <form method="get" action="{{ route('api-enderecos') }}" class="w-100 simulador">
          <div class="form-row">
            <div class="col-sm mb-2-576">
              <label for="ass_id">ASS ID do Representante</label>
              <input type="text" name="ass_id" class="form-control" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-2">Recuperar endereços</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- EXTRATO REPRESENTANTE -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">EXTRATO POR REPRESENTANTE</h3>
        <form method="get" action="{{ route('api-extrato') }}" class="w-100 simulador">
          <div class="form-row">
            <div class="col-sm mb-2-576">
              <label for="ass_id">ASS ID do Representante</label>
              <input type="text" name="ass_id" class="form-control" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-2">Recuperar extrato</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- SEGMENTOS -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">SEGMENTOS</h3>
        <form method="get" action="{{ route('api-segmentos') }}" class="w-100 simulador">
          <button type="submit" class="btn btn-primary btn-sm mt-2">Verificar</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- VALIDAR REPRESENTANTE -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">VALIDAR REPRESENTANTE (se representante pode utilizar os serviços)</h3>
        <form method="get" action="{{ route('api-validar-representante') }}" class="w-100 simulador">
          <div class="form-row">
            <div class="col-sm mb-2-576">
              <label for="ass_id">ASS ID do Representante</label>
              <input type="text" name="ass_id" class="form-control" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-2">Recuperar validação</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- DADOS DO REPRESENTANTE -->
    <div class="row">
      <div class="col">

        <h3 class="text-success">DADOS DO REPRESENTANTE</h3>
        <form method="get" action="{{ route('api-dados-representante') }}" class="w-100 simulador">
          <div class="form-row">
            <div class="col-sm mb-2-576">
              <label for="ass_id">ASS ID do Representante</label>
              <input type="text" name="ass_id" class="form-control" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-sm mt-2">Recuperar dados</button>
        </form>
      </div>
    </div>
    <!-- ===================================================================================================== -->

    <hr>

    <!-- RESULTADO =========================================================================================== -->

    @if(isset($dados) && count($dados) > 0)
    <div class="row">
      <div class="col">
        <h4 class="mt-2 mb-2 text-danger">RESULTADO - {{ isset($dados['message']) && (strlen($dados['message']) > 0) ? $dados['message'] : $message }}</h4>

        <h5 class="mb-2">Chave: Success - {{ isset($dados['success']) && $dados['success'] ? 'true' : 'não tem a chave ou retorno false' }}</h5>
        
        @if(isset($dados['data']))

          @foreach($dados['data'] as $key => $dado)
            <h5>Chave: {{ $key }}</h5>
            @if(!is_array($dado) && ($dado != strip_tags($dado)))
              <p>{!! $dado !!}</p>
            @elseif(is_array($dado))
              <p>
                <pre>{{ json_encode($dado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) }}</pre>
              </p>
            @elseif(is_bool($dado))
              <p>{{ $dado ? 'true' : 'false' }}</p>
            @else
              <p>{{ $dado }}</p>
            @endif
            <br>
          @endforeach

        @endif
      </div>
    </div>
    @endif

  </div>
</section>

@endsection