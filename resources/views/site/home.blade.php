@extends('site.layout.app', ['title' => 'Home'])

@section('content')

@php
  use \App\Http\Controllers\Helper;
@endphp

<section class="mt-1">
  <div class="container-fluid">
    <div class="row" id="conteudo-principal">
      <div id="carousel" class="carousel slide" data-ride="carousel" data-interval="6000">
        <ol class="carousel-indicators">
          @php $i = -1; @endphp
          @foreach($imagens as $img)
          @php $i++; @endphp
          @if(!empty($img->url))
            @if($i === 0)
              <li data-target="#carousel" data-slide-to="{{ $i }}" class="active"></li>
            @else
              <li data-target="#carousel" data-slide-to="{{ $i }}"></li>
            @endif
          @endif
          @endforeach
        </ol>
        <div class="carousel-inner h-100">
          @foreach($imagens as $img)
          @if(!empty($img->url))
            @if($img->ordem === 1)
            <div class="carousel-item h-100 active">
            @else
            <div class="carousel-item h-100">
            @endif
              <a href="{{ $img->link }}" target="{{ $img->target }}">
                <img class="w-100 hide-576" src="{{ asset($img->url) }}" />
                <img class="w-100 show-576" src="{{ asset($img->url_mobile) }}" />
              </a>
            </div>
          @endif
          @endforeach
        </div>
        <a class="carousel-control-prev" href="#carousel" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carousel" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
      </div>
    </div>
  </div>
</section>

<section id="espaco-representante">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Espaço do Representante</h4>
          </blockquote>
          <h5 class="float-right cinza-claro-bg">
            <a href="/portal"><i class="fas fa-user icon-title"></i> Acessar o portal</a>
          </h5>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-3 offset-lg-1-5 col-sm-6 pb-15">
        <a href="/cartilha-do-representante" class="d-block h-100">
          <div class="box text-center azul-escuro-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/open-book.png') }}" class="inside-img" alt="Cartilha do Representante | Core-SP" />
              <p class="text-uppercase mt-3 branco">Cartilha do<br class="hide-992" /> Representante</p>
              <button class="btn-box">Visualizar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="/balcao-de-oportunidades" class="d-block h-100">
          <div class="box text-center azul-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/001-work.png') }}" class="inside-img" alt="Balcão de Oportunidades | Core-SP" />
              <p class="text-uppercase mt-3 branco">Balcão de Oportunidades</p>
              <button class="btn-box">Acessar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="/resolucoes-anuidade-taxas-e-emolumentos" class="d-block h-100">
          <div class="box text-center azul-escuro-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/003-bill.png') }}" class="inside-img" alt="Anuidade 2019 | Core-SP" />
              <p class="text-uppercase mt-3 branco">Anuidade 2019<br class="hide-992" /> taxas e emolumentos</p>
              <button href="#" class="btn-box">ACESSAR</button>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 offset-lg-1-5 col-sm-6 pb-15">
        <a href="/agendamento" class="d-block h-100">
          <div class="box text-center azul-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/appointment.png') }}" class="inside-img" alt="Agendamento | Core-SP" />
              <p class="text-uppercase mt-3 branco">Agendamento<br class="hide-992" /> para refis</p>
              <button class="btn-box">Agendar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 text-right pb-15">
        <a href="/simulador" class="d-block h-100">
          <div class="box text-center azul-escuro-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/001-paper.png') }}" alt="Simulador | Core-SP">
              <p class="text-uppercase mt-3 branco">Simulador de<br class="hide-992" /> valores</p>
              <button class="btn-box">Acessar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="#" class="d-block h-100">
          <div class="box text-center azul-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/file.png') }}" alt="Consulta de Ativos | Core-SP">
              <p class="text-uppercase mt-3 branco">Consulta<br class="hide-992" /> de situação</p>
              <h5 class="mt-4 text-white">EM BREVE</h5>
              {{-- <button class="btn-box">Acessar</button> --}}
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<section id="novo-core" class="mb-5">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="text-center novo-core-box">
          <h2 class="stronger branco text-uppercase">um novo core-sp para você!</h2>
          <a href="/agenda" class="btn-novo-core"><h4 class="normal">Agenda</h4></a>
          <br class="show-576 br-novo-core" />
          <a href="/concursos" class="btn-novo-core"><h4 class="normal">Concursos</h4></a>
          <br class="show-768 br-novo-core" />
          <a href="/feiras" class="btn-novo-core"><h4 class="normal">Feiras</h4></a>
          <br class="show-576 br-novo-core" />
          <a href="/cursos" class="btn-novo-core"><h4 class="normal">Cursos</h4></a>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="home-news">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Notícias</h4>
          </blockquote>
          <h5 class="float-right branco-bg">
            <a href="/noticias"><i class="fas fa-plus-circle icon-title"></i> Ver mais notícias</a>
          </h5>
        </div>
      </div>
    </div>
    <div class="row">
      @php $i = 0; @endphp
      @foreach($noticias as $noticia)
        @php $i++; @endphp
        @include('site.inc.noticia-grid')
      @endforeach
    </div>
  </div>
</section>

<section id="fale">
  <div class="container">
    <div class="row faleRow">
      <div class="col-md-6 pb-30-768">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Cotidiano</h4>
          </blockquote>
        </div>
        @foreach($cotidianos as $resultado)
          @include('site.inc.noticia-min-grid')
        @endforeach
      </div>
      <div class="col-md-6">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Fale com o CORE-SP</h4>
          </blockquote>
        </div>
        <div class="row">
          <div class="col-lg-6 faleSingle">
            <a href="/agendamento-consulta">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/002-phone-book-.png') }}" class="inside-img" alt="Consulta de Agendamento | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Consulte seu<br class="hide-992" /> agendamento</h5>
                </div>
              </div>
            </a>
          </div>
          <div class="col-lg-6 faleSingle">
            <a href="https://api.whatsapp.com/send?phone=551132435516&text=Quero%20receber%20as%20últimas%20notícias%20do%20CORE-SP%20pelo%20WhatsApp!" target="_blank">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/001-whatsapp-.png') }}" class="inside-img" alt="WhatsApp | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Entre em contato<br class="hide-992" /> pelo WhatsApp</h5>
                </div>
              </div>
            </a>
          </div>
          <div class="col-lg-6 faleSingle">
            <a href="https://www.youtube.com/channel/UCOT_xwrQrpl_uu8MFl_EzWQ" target="_blank">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/003-youtube-.png') }}" class="inside-img" alt="YouTube | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Institucional 2019<br class="hide-992" /> e informativos</h5>
                </div>
              </div>
            </a>
          </div>
          <div class="col-lg-6 faleSingle">
            <div class="row nomargin">
              <div class="align-self-center">
                <img src="{{ asset('img/004-headset-.png') }}" class="inside-img" alt="Atendimento | Core-SP">
              </div>
              <div class="flex-one fale-txt align-self-center">
                <h5 class="normal">Atendimento<br class="hide-992" /> (11) 3243-5500</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="beneficios">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="beneficios-box row nomargin">
          <div class="col-lg-5 hide-992">
            <img class="lazy" data-src="{{ asset('img/benef-v2.png') }}" id="computer" alt="Programa de Benefícios | Core-SP" />
          </div>
          <div class="col-lg-7 beneficios-txt center-992">
            <h2 class="stronger branco text-uppercase">Programa de Benefícios</h2>
            <p class="branco light">O Core-SP traz benefícios diferenciados para Representantes Comerciais</p>
            <div>
              <a href="/programa-de-beneficios" class="btn-beneficios">saiba mais</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="eouv-calendario" class="pb-5">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">E-ouv</h4>
          </blockquote>
        </div>
        <div class="row">
          <div class="col-sm-8">
            <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
              <img class="lazy" data-src="{{ asset('img/computer.png') }}" alt="E-OUV | Core-SP" />
            </a>
          </div>
          <div class="col-sm-4 hide-576 eouv-imgs align-self-center pl-3 center-992">
            <div class="m-auto pb-3">
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/ie-1.png') }}" class="azul-bg" data-toggle="tooltip" title="Fale Conosco" alt="Fale Conosco | Core-SP" />
              </a>
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/ie-2.png') }}" class="azul-escuro-bg" data-toogle="tooltip" title="Ouvidoria" alt="Ouvidoria | Core-SP" />
              </a>
            </div>
            <div class="m-auto pb-3">
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/ie-3.png') }}" class="verde-escuro-bg" data-toogle="tooltip" title="Elogios" alt="Elogios | Core-SP" />
              </a>
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/ie-4.png') }}" class="azul-bg" data-toogle="tooltip" title="Sugestões" alt="Sugestões | Core-SP" />
              </a>
            </div>
            <div class="m-auto">
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/ie-5.png') }}" class="azul-escuro-bg" data-toogle="tooltip" title="Reclamações" alt="Reclamações | Core-SP" />
              </a>
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/ie-6.png') }}" class="verde-escuro-bg" data-toogle="tooltip" title="Dúvidas" alt="Dúvidas | Core-SP" />
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mt-2-992">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Calendário</h4>
          </blockquote>
        </div>
        <div id="calendario" class="row">
          <div class="col-sm-8">
            <a href="/calendario-2019">
              <img class="lazy" data-src="{{ asset('img/calendario.png') }}" alt="Calendário | Core-SP" />
            </a>
          </div>
          <div class="col-sm-4 hide-576 align-self-center text-right pr-4">
            <div class="calendario-txt">
              <p class="preto">Confira o calendário completo de<br>atendimento e expediente <br>de sua região.</p>
              <a href="/calendario-2019" class="btn-calendario mt-4">
                <h4 class="normal">confira</h4>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
