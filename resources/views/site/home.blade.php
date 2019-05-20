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
          <li data-target="#carousel" data-slide-to="0" class="active"></li>
          <li data-target="#carousel" data-slide-to="1"></li>
          <li data-target="#carousel" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner h-100">
          <div class="carousel-item h-100 active">
            <a href="/agendamento">
              <img class="w-100 hide-576" src="{{ asset('img/banner-refis.png') }}" />
              <img class="w-100 show-576" src="{{ asset('img/banner-refis-m.png') }}" />
            </a>
          </div>
          <div class="carousel-item h-100">
            <a href="/balcao-de-oportunidades">
              <img class="w-100 hide-576" src="{{ asset('img/banner-bdo.png') }}" />
              <img class="w-100 show-576" src="{{ asset('img/banner-bdo-m.png') }}" />
            </a>
          </div>
          <div class="carousel-item h-100">
            <a href="/cursos">
              <img class="w-100 hide-576" src="{{ asset('img/banner-conexoes.png') }}" />
              <img class="w-100 show-576" src="{{ asset('img/banner-conexoes-m.png') }}" />
            </a>
          </div>
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
      <div class="col-lg-3 col-sm-6 pb-15-992">
        <a href="/cartilha-do-representante" class="d-block h-100">
          <div class="box text-center azul-escuro-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/open-book.png') }}" class="inside-img" />
              <p class="text-uppercase mt-3 branco">Cartilha do<br class="hide-992" /> Representante</p>
              <button class="btn-box">Visualizar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15-992">
        <a href="/balcao-de-oportunidades" class="d-block h-100">
          <div class="box text-center azul-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/001-work.png') }}" class="inside-img" />
              <p class="text-uppercase mt-3 branco">Balcão de Oportunidades</p>
              <button class="btn-box">Acessar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15-992">
        <a href="http://boleto.core-sp.org.br" class="d-block h-100" target="_blank">
          <div class="box text-center azul-escuro-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/003-bill.png') }}" class="inside-img" />
              <p class="text-uppercase mt-3 branco">Emissão de boleto<br class="hide-992" /> anuidade 2019</p>
              <button href="#" class="btn-box">EMITIR</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15-992">
        <a href="/agendamento" class="d-block h-100">
          <div class="box text-center azul-bg h-100">
            <div class="inside-box">
              <img src="{{ asset('img/appointment.png') }}" class="inside-img" />
              <p class="text-uppercase mt-3 branco">Agende seu<br class="hide-992" /> atendimento</p>
              <button class="btn-box">Agendar</button>
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
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h4 class="pr-3 ml-1">Fale com o CORE-SP</h4>
          </blockquote>
        </div>
      </div>
    </div>
    <div class="row faleRow">
      <div class="col-lg-3 col-sm-6 faleSingle">
        <a href="/agendamento-consulta">
          <div class="row nomargin">
            <div class="align-self-center">
              <img src="{{ asset('img/002-phone-book-.png') }}" class="inside-img" alt="">
            </div>
            <div class="flex-one fale-txt align-self-center">
              <h5 class="normal">Consulte seu<br class="hide-768" /> agendamento</h5>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 faleSingle">
        <a href="https://api.whatsapp.com/send?phone=551132435516&text=Quero%20receber%20as%20últimas%20notícias%20do%20CORE-SP%20pelo%20WhatsApp!" target="_blank">
          <div class="row nomargin">
            <div class="align-self-center">
              <img src="{{ asset('img/001-whatsapp-.png') }}" class="inside-img" alt="">
            </div>
            <div class="flex-one fale-txt align-self-center">
              <h5 class="normal">Entre em contato<br class="hide-768" /> pelo WhatsApp</h5>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 faleSingle">
        <a href="https://www.youtube.com/channel/UCOT_xwrQrpl_uu8MFl_EzWQ" target="_blank">
          <div class="row nomargin">
            <div class="align-self-center">
              <img src="{{ asset('img/003-youtube-.png') }}" class="inside-img" alt="">
            </div>
            <div class="flex-one fale-txt align-self-center">
              <h5 class="normal">Institucional 2019<br class="hide-768" /> e informativos</h5>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 faleSingle">
        <div class="row nomargin">
          <div class="align-self-center">
            <img src="{{ asset('img/004-headset-.png') }}" class="inside-img" alt="">
          </div>
          <div class="flex-one fale-txt align-self-center">
            <h5 class="normal">Atendimento<br class="hide-768" /> (11) 3243-5500</h5>
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
            <img src="{{ asset('img/benef-v2.png') }}" id="computer" />
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
            <img src="{{ asset('img/computer.png') }}" />
          </div>
          <div class="col-sm-4 hide-576 eouv-imgs align-self-center pl-3 center-992">
            <div class="m-auto pb-3">
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/icon-eouv-01.png') }}" class="azul-bg" data-toggle="tooltip" title="Fale Conosco" />
              </a>
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/icon-eouv-02.png') }}" class="azul-escuro-bg" data-toogle="tooltip" title="Ouvidoria" />
              </a>
            </div>
            <div class="m-auto pb-3">
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/icon-eouv-03.png') }}" class="verde-escuro-bg" data-toogle="tooltip" title="Elogios" />
              </a>
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/icon-eouv-04.png') }}" class="azul-bg" data-toogle="tooltip" title="Sugestões" />
              </a>
            </div>
            <div class="m-auto">
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/icon-eouv-05.png') }}" class="azul-escuro-bg" data-toogle="tooltip" title="Reclamações" />
              </a>
              <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
                <img src="{{ asset('img/icon-eouv-06.png') }}" class="verde-escuro-bg" data-toogle="tooltip" title="Dúvidas" />
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
            <img src="{{ asset('img/calendario.png') }}" />
          </div>
          <div class="col-sm-4 hide-576 align-self-center text-right pr-4">
            <div class="calendario-txt">
              <p class="preto">Confira o calendário completo de<br>atendimento e expediente <br>de sua região.</p>
              <a href="#" class="btn-calendario mt-4">
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
