@extends('site.layout.app', ['title' => 'Carta de Serviços ao Usuário'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
  <img src="{{asset('img/institucional.png')}}" alt="CORE-SP">
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
        Carta de Serviços ao Usuário
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-conteudo">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h2 class="stronger">Core-SP lança carta de serviços ao usuário</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
	    <div class="col-lg conteudo-txt pr-4">
        <!-- <p>
          O ano foi de muito trabalho e novidades no Core-SP. Nosso portal ganhou um visual contemporâneo e conta com notícias sobre tudo o que importa para o representante desempenhar suas funções com maestria; novos meios de pagamento da anuidade, via maquininha do cartão ou impressão de boletos para a quitação antecipada; a criação da Área Restrita, onde é possível atualizar seu cadastro e ficar a par, tanto  dos pagamentos pendentes quanto daqueles que já foram feitos.
        </p>
        <p>
          Dezembro chegou, mas as inovações e a atenção que damos a você não podem parar. Por isso, <strong>lançamos a nova Carta de serviços ao Usuário</strong>. No texto, estão dispostos, detalhadamente, todos os serviços oferecidos a quem procura o Conselho Regional dos Representantes Comerciais no Estado de São Paulo (CORE-SP), além uma Pesquisa de Satisfação – que visa amplificar a sua voz, representante. Afinal, é dessa troca de ideias que nascem as iniciativas para  que possamos atendê-lo da melhor maneira possível.
        </p> -->
        
        @if(!isset($resultado) || $resultado->isEmpty())
        <p><strong>Ainda não consta a publicação atual.</strong></p>
        @else
        <p>
          Clique no sumário abaixo e acesse o documento. Aproveite  também para registrar suas impressões a respeito do nosso trabalho e dos procedimentos envolvidos na execução dele. É por você, e pra você que estamos aqui.
        </p>

        <!-- SUMÁRIO *********************************************************************************************************** -->
        <label for="textosSumario">Sumário:</label>
        <select
          class="form-control mb-3" 
          id="textosSumario"
        >
          <option value="" style="font-style: italic;">Escolha um título ou subtítulo ...</option>
          @foreach($resultado as $texto)
            <option value="{{ $texto->id }}" style="{{ $texto->tipoTitulo() ? '' : 'font-weight: bold;' }}" {{ request()->url() == route('carta-servicos', $texto->id) ? 'selected' : '' }}>{!! $texto->tipoTitulo() ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' !!}{{ $texto->tipoTitulo() ? $texto->tituloFormatado() : $texto->subtituloFormatado() }}</option>
          @endforeach
        </select>

        <!-- FORM BUSCA *********************************************************************************************************** -->
        <form method="GET" class="form-inline mt-2" action="{{ route('carta-servicos-buscar') }}">
          <label for="buscaTextoSumario" class="mb-2 mr-sm-2">Buscar:</label>
          <input type="text"
            name="buscaTexto"
            class="form-control col mb-2 mr-sm-2 {{ $errors->has('buscaTexto') ? 'is-invalid' : '' }}"
            placeholder="Palavra chave"
            id="buscaTextoSumario"
            value="{{ request()->query('buscaTexto') }}"
          />
          <button type="submit" class="btn btn-sm btn-primary mb-2">
            <i class="fas fa-search"></i>
          </button>
          @if($errors->has('buscaTexto'))
            <div class="invalid-feedback">
              {{ $errors->first('buscaTexto') }}
            </div>
          @endif
        </form>
        
        <!-- RESULTADO BUSCA *********************************************************************************************************** -->
        @if(isset($busca))
          <hr />
          <p class="light">Busca por: <strong>{{ request()->query('buscaTexto') }}</strong>
              <small><i>- {{ $busca->count() === 1 ? $busca->count() . ' resultado' : $busca->count() . ' resultados' }}</i></small>
          </p>
          @if($busca->count() > 0)
          <div class="list-group list-group-flush">
            @foreach($busca as $t)
              <a href="{{ route('carta-servicos', $t->id) }}" class="list-group-item list-group-item-action"><strong>{{ $t->tipoTitulo() ? $t->tituloFormatado() : $t->subtituloFormatado() }}</strong></a>
            @endforeach
          </div>
          @endif
        @endif

        <!-- RESULTADO TEXTO SELECIONADO *********************************************************************************************************** -->
        @if(isset($textos) && !empty($textos))
        <ul class="pagination p-0 mt-2 mb-2">
          <li class="page-item {{ isset($btn_anterior) ? '' : 'disabled' }}"><a class="page-link" href="{{ isset($btn_anterior) ? $btn_anterior : '#' }}"><i class="fas fa-angle-double-left"></i></a></li>
          <li class="page-item {{ isset($btn_proximo) ? '' : 'disabled' }}"><a class="page-link" href="{{ isset($btn_proximo) ? $btn_proximo : '#' }}"><i class="fas fa-angle-double-right"></i></a></li>
        </ul>

        <div class="border">
          <div class="p-2">
            <!-- cabeçalho da carta -->
            <img src="{{ asset('img/LOGO-VERDE002.png') }}" class="mb-3" style="display: block;margin: 0 auto;"/>
            <hr />
            <span id="corpoTexto" tabindex="0"></span>
          @foreach($textos as $t)
              @switch($t->nivel)
                @case(1)
                  <p {!! $t->getCorTituloSub() !!}>&nbsp;&nbsp;&nbsp;<strong>{{ $t->subtituloFormatado() }}</strong></p>
                  <div class="pl-3">{!! $t->conteudo !!}</div>
                  @break
                @case(2)
                  <p {!! $t->getCorTituloSub() !!}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ $t->subtituloFormatado() }}</strong></p>
                  <div class="pl-4">{!! $t->conteudo !!}</div>
                  @break
                @case(3)
                  <p {!! $t->getCorTituloSub() !!}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ $t->subtituloFormatado() }}</strong></p>
                  <div class="pl-5">{!! $t->conteudo !!}</div>
                  @break
                @default
                  <h4 class="font-weight-bolder" {!! $t->getCorTituloSub() !!}>{{ $t->tituloFormatado() }}</h4>
                  <div class="pl-0">{!! $t->conteudo !!}</div>
              @endswitch
          @endforeach
          </div>
          <!-- rodapé da carta -->
          <img src="{{ asset('img/base_carta_servicos.png') }}" />
        </div>

        <div class="float-right mr-1 mt-3">
          <small><i>Última atualização: {{ $dt_atualizacao }}</i></small>
        </div>

        <ul class="pagination p-0 mt-2 mb-0">
          <li class="page-item {{ isset($btn_anterior) ? '' : 'disabled' }}"><a class="page-link" href="{{ isset($btn_anterior) ? $btn_anterior : '#' }}"><i class="fas fa-angle-double-left"></i></a></li>
          <li class="page-item {{ isset($btn_proximo) ? '' : 'disabled' }}"><a class="page-link" href="{{ isset($btn_proximo) ? $btn_proximo : '#' }}"><i class="fas fa-angle-double-right"></i></a></li>
        </ul>
        @endif

      @endif
      </div>
	  </div>
  </div>
</section>

@endsection