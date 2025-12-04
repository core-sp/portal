@extends('site.layout.app', ['title' => 'Balcão de Oportunidades'])

@section('description')
  <meta name="description" content="O Balcão de Oportunidades do Core-SP é o local ideal para empresas e Representantes Comerciais se conectarem e compartilharem oportunidades." />
@endsection

@section('content')

@php
use \App\BdoOportunidade;
@endphp

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/bdo.png') }}" />
    <div class="row position-absolute pagina-titulo" id="bdo-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Balcão de Oportunidades
        </h1>
      </div>
    </div>
    <h6 class="branco normal">
      <a href="/anunciar-vaga" style="color:inherit;">Quer anunciar vagas? Clique aqui, preencha os dados e solicite a inclusão</a>
    </h6>
  </div>
</section>

<section id="pagina-bdo">
  <div class="container">
    <div class="row pb-4" id="conteudo-principal">
      <div class="col">
        <form method="GET" role="form" action="/balcao-de-oportunidades/busca" class="pesquisaLicitacao">
          <div class="form-row text-center">
            <div class="m-auto">
              <h5 class="text-uppercase stronger marrom">Busca detalhada</h5>
            </div>
          </div>
          <div class="linha-lg-mini"></div>
          <div class="form-row">
            <div class="col-md mb-2-768">
              <label for="palavra-chave">Palavra-chave</label>
              <input type="text"
                name="palavra-chave"
                class="form-control {{ !empty(Request::input('palavra-chave')) ? 'bg-focus border-info' : '' }}"
                placeholder="Palavra chave"
                id="palavrachave"
                @if(!empty(Request::input('palavra-chave')))
                value="{{ Request::input('palavra-chave') }}"
                @endif
                />
            </div>

            <div class="col-md mb-2-768">
              <label for="bdo_tipo">Tipo</label>
              <select name="tipo" 
                class="form-control 
                {{ !empty(Request::input('tipo')) && in_array(Request::input('tipo'), ['empresas', 'representantes']) ? 'bg-focus border-info' : '' }}" 
                id="bdo_tipo"
              >
                <option value="empresas" {{ Request::input('tipo') == 'empresas' ? 'selected' : '' }}>Empresas</option>
                <option value="representantes" {{ Request::input('tipo') == 'representantes' ? 'selected' : '' }}>Representantes</option>
              </select>
            </div>

            <div class="col-md mb-2-768">
              <label for="segmento">Segmento</label>
              <select name="segmento" class="form-control {{ !empty(Request::input('segmento')) && in_array(Request::input('segmento'), $segmentos) ? 'bg-focus border-info' : '' }}" id="segmento">
                <option value="">Todos</option>
                @foreach($segmentos as $segmento)
                  @if($segmento === Request::input('segmento'))
                  <option value="{{ $segmento }}" selected>{{ $segmento }}</option>
                  @else
                  <option value="{{ $segmento }}">{{ $segmento }}</option>
                  @endif
                @endforeach
              </select>
            </div>

            <div class="col-md mb-2-768">
              <label for="regional"><span class="hide-992 show-inline-768 show-inline">Área de atuação -&nbsp;</span>Regional</label>
              <select name="regional" class="form-control {{ !empty(Request::input('regional')) && (Request::input('regional') >= 0 || Request::input('regional') <= 13 || Request::input('regional') === 'todas') ? 'bg-focus border-info' : '' }}">
                <option value="todas">Qualquer</option>
                @foreach($regionais as $regional)
                  @if($regional->idregional == Request::input('regional'))
                  <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                  @else
                  <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                  @endif
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-row mt-2">

            <div class="col-md mb-2-768">
              <label for="buscar_municipios">Área de atuação - Município</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <i class="fas fa-map-marker-alt text-primary"></i>
                  </span>
                </div>
                <input type="text" class="form-control" id="buscar_municipios" placeholder="Buscar...">
              </div>
              <div class="scrollable-div bg-white" id="lista_municipios"></div>
            </div>

            <div class="col-md mb-2-768">
              <label for="municipios_escolhidos">Área de atuação - Município Escolhido</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <i class="fas fa-map-marked-alt text-primary"></i>
                  </span>
                </div>
                <input type="text" class="form-control" id="municipios_escolhidos" name="municipio" value="{{ !empty(Request::input('municipio')) ? Request::input('municipio') : 'Qualquer' }}" readonly>
                <div class="input-group-append">
                  <button class="btn btn-danger" type="button" id="btn_apagar_municipio">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- <div class="col-md mb-2-768"> -->
              <div class="col-lg-4 col-md-12 mt-4 pt-1">
                <button type="submit" class="btn-buscaavancada loadingPagina"><i class="fas fa-search"></i>&nbsp;&nbsp;Pesquisar</button>
                <a href="/balcao-de-oportunidades" class="btn btn-limpar"><i class="fas fa-times"></i>&nbsp;&nbsp;Limpar</a>
              </div>
            <!-- </div> -->

          
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <div class="linha-cinza"></div>
    </div>
  </div>
  <div class="container">
    <div class="row mt-4">
      <div class="col">
        @if(isset($oportunidades))
          @foreach($oportunidades as $oportunidade)
          <div class="licitacao-grid">
            <div class="licitacao-grid-main">


            @if(get_class($oportunidade) == "App\BdoOportunidade")

              <h5 class="marrom mb-1 text-break">{{ $oportunidade->titulo }}</h5>
              <h6 class="light">
                <i class="far fa-building"></i>&nbsp;
                @if(isset($oportunidade->empresa->fantasia))
                {{ $oportunidade->empresa->fantasia }}
                @else
                {{ $oportunidade->empresa->razaosocial }}
                @endif
                &nbsp;&nbsp;&nbsp;
                <i class="fas fa-map-marker-alt"></i>&nbsp;
                {{ $oportunidade->regiaoFormatada }}
                &nbsp;&nbsp;&nbsp;
                <i class="fas fa-suitcase"></i>&nbsp;
                @if($oportunidade->vagasdisponiveis > 1)
                {{ $oportunidade->vagasdisponiveis }} vagas
                @else
                {{ $oportunidade->vagasdisponiveis }} vaga
                @endif
              </h6>
              <div class="linha-lg-mini"></div>
              <p class="text-break">{{ $oportunidade->descricao }}</p>
              <div class="saiba-mais-info">
                <div class="row mt-3 bot-lg">
                  <div class="col d-flex">
                    <div class="mr-2">
                      <i class="fas fa-phone"></i>
                    </div>
                    <div class="flex-one">
                      <p><strong>Nome:</strong> {{ $oportunidade->empresa->contatonome }}</p>
                      <p><strong>Email:</strong> {{ $oportunidade->empresa->contatoemail }}</p>
                      <p><strong>Telefone:</strong> {{ $oportunidade->empresa->contatotelefone }}</p>
                    </div>
                  </div>
                  <div class="col d-flex">
                    <div class="mr-2">
                      <i class="far fa-building"></i>
                    </div>
                    <div class="flex-one">
                      <p><strong>Empresa:</strong> {{ $oportunidade->empresa->razaosocial }}</p>
                      <p><strong>Endereço:</strong> {{ $oportunidade->empresa->endereco }}</p>
                      <p><strong>Email:</strong> {{ $oportunidade->empresa->email }}</p>
                      <p><strong>Telefone:</strong> {{ $oportunidade->empresa->telefone }}</p>
                      <p><strong>Website:</strong> {{ $oportunidade->empresa->site }}</p>
                    </div>
                  </div>
                  <div class="col d-flex">
                    <div class="mr-2">
                      <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="flex-one">
                      <p><strong>Segmento:</strong> {{ $oportunidade->segmento }}</p>
                      <p><strong>Última atualização:</strong> {{ onlyDate($oportunidade->updated_at) }}</p>
                      <p class="d-inline"><strong>Status:</strong> </p>{!! BdoOportunidade::statusDestacado($oportunidade->status) !!}
                    </div>
                  </div>
                </div>
              </div>
              <button class="saiba-mais mt-3"><i class="fas fa-angle-double-down"></i>&nbsp;&nbsp;Mais Detalhes</button>

            @else

            <h5 class="marrom mb-1 text-break">{{ $oportunidade->representante->nome }}</h5>
            <h6 class="light mt-2">
              <strong>CORE</strong>&nbsp;
              {{ $oportunidade->representante->registro_core }}
              &nbsp;&nbsp;&nbsp;
              <i class="fas fa-map-marker-alt"></i>&nbsp;
              {{ json_decode($oportunidade->regioes)->seccional }}
              &nbsp;&nbsp;&nbsp;
              <i class="far fa-building"></i>&nbsp;
              {{ $oportunidade->representante->cpf_cnpj }}
              &nbsp;&nbsp;&nbsp;
              <strong>SEGMENTO</strong>&nbsp;
              {{ $oportunidade->segmento }}
            </h6>

              @if(!empty(json_decode($oportunidade->regioes)->municipios))
              <h6 class="light mt-2">
                <i class="fas fa-map-marked-alt"></i>&nbsp;
                {!! $oportunidade->municipiosTextual('&nbsp;&nbsp;|&nbsp;&nbsp;') !!}
                &nbsp;&nbsp;&nbsp;
              </h6>
              @endif

              <div class="linha-lg-mini"></div>
              <p class="text-break mb-4">{{ $oportunidade->descricao }}</p>
              <p class="mb-2">
                <i class="fas fa-street-view p-2 border rounded bg-white"></i>&nbsp;&nbsp;
                {{ $oportunidade->endereco }}
              </p>
              <p>
                <i class="fas fa-at p-2 border rounded bg-white"></i>&nbsp;&nbsp;
                {{ $oportunidade->email }}
                &nbsp;&nbsp;&nbsp;&nbsp;
                <i class="fas fa-phone p-2 border rounded bg-white"></i>&nbsp;&nbsp;
                {{ $oportunidade->telefone }}
              </p>

            @endif

            </div>
            <div class="licitacao-grid-bottom">
              <div class="col nopadding">
                <div class="text-right">
                  <h6 class="light marrom">
                    <span>
                      <strong>
                        Data de inclusão 
                        {{ get_class($oportunidade) == "App\BdoOportunidade" ? 'da oportunidade' : 'do perfil público' }}:
                      </strong> 
                      {{ get_class($oportunidade) == "App\BdoOportunidade" ? onlyDate($oportunidade->datainicio) : onlyDate(json_decode($oportunidade->status)->data) }}
                    </span>

                    @if(get_class($oportunidade) != "App\BdoOportunidade")
                    <span class="ml-3">
                      <strong>
                        Última atualização:
                      </strong> 
                      {{ onlyDate($oportunidade->updated_at) }}
                    </span>
                    @endif

                  </h6>
                </div>
              </div>
            </div>

          </div>
          @endforeach
        @else
        <p>Nenhuma oportunidade encontrada!</p>
        @endif
      </div>
    </div>
    @if(isset($oportunidades))
    <div class="row">
      <div class="col">
        <div class="float-right">
          {{ $oportunidades->appends(request()->input())->links() }}
        </div>
      </div>
    </div>
    @endif
  </div>  
</section>

{!! str_replace('aqui', $municipios['json'], $municipios['tag']) !!}

@endsection