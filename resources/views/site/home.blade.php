@extends('site.layout.app', ['title' => 'Conselho Regional dos Representantes Comerciais do Estado de São Paulo'])

@section('description')
  <meta name="description" content="O Core-SP é responsável pela consulta, orientação, disciplina e fiscalização do exercício da profissão de Representação Comercial no estado de São Paulo.">
@endsection

@section('content')

@include('site.inc.popup')

@if($imagens->isNotEmpty())
<section>
  <div class="container-fluid">
    <div class="row" id="conteudo-principal">
      <div id="carousel" class="carousel slide" data-ride="carousel" data-interval="6000">
        <ol class="carousel-indicators">
          @foreach($imagens as $img)
            <li data-target="#carousel" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></li>
          @endforeach
        </ol>
        <div class="carousel-inner h-100">
          @foreach($imagens as $img)
            <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
              <a href="{{ $img->link }}" target="{{ $img->target }}">
                <img class="w-100 hide-576 lazy-loaded-image" data-src="{{ asset($img->url) }}" alt="Core-SP | Conselho Regional dos Representantes Comercias do Estado de São Paulo" />
                <img class="w-100 show-576 lazy-loaded-image" data-src="{{ asset($img->url_mobile) }}" alt="Core-SP | Conselho Regional dos Representantes Comercias do Estado de São Paulo" />
              </a>
            </div>
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
@endif

<section id="home-news" class="mb-2 mt-4">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h2 class="pr-3 ml-1">Notícias</h2>
          </blockquote>
          <h5 class="float-right branco-bg">
            <a href="{{ route('noticias.siteGrid') }}"><i class="fas fa-plus-circle icon-title"></i> Ver mais notícias</a>
          </h5>
        </div>
      </div>
    </div>
    <div class="row">
      @foreach($noticias as $noticia)
        @include('site.inc.noticia-grid')
      @endforeach
    </div>
  </div>
</section>

<section id="espaco-representante" class="mb-2">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h2 class="pr-3 ml-1">Espaço do Representante</h2>
          </blockquote>
          <h5 class="float-right cinza-claro-bg hide-576">
          <a href="{{ route('representante.login') }}"><i class="fas fa-lock icon-title"></i> Área restrita do Representante</a>
          </h5>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-3 col-sm-6 pb-15">
        <div class="box text-center {{ isset($itens_home['cards_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_1']) ? 'background-color:'.$itens_home['cards_1'] : '' }}">
          <div class="inside-box">
            <img src="{{ asset('img/padlock.png') }}" class="inside-img" alt="Área restrita do Representante | Core-SP" />
            <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Área restrita<br class="hide-992" /> do Representante</h3>
            <a href="/representante/login" class="d-block h-100">
              <button class="btn-box azul-escuro">Acessar</button>
            </a>
            <a href="/representante/cadastro" class="d-block h-100">
              <button class="btn-box btn-box-little azul-escuro">Cadastrar-se</button>
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="/consulta-de-situacao" class="d-block">
          <div class="box text-center {{ isset($itens_home['cards_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_2']) ? 'background-color:'.$itens_home['cards_2'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/file.png') }}" class="inside-img" alt="Consulta de Ativos | Core-SP">
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Consulta<br class="hide-992" /> Pública</h3>
              <button class="btn-box azul">Consultar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <div class="box text-center {{ isset($itens_home['cards_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_1']) ? 'background-color:'.$itens_home['cards_1'] : '' }}">
          <div class="inside-box">
            <img src="{{ asset('img/001-work.png') }}" class="inside-img" alt="Balcão de Oportunidades | Core-SP" />
            <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Balcão de<br class="hide-992" /> Oportunidades</h3>
            <a href="/balcao-de-oportunidades" class="d-inline h-100">
              <button class="btn-box azul-escuro">Acessar</button>
            </a>
            <a href="/anunciar-vaga" class="d-inline h-100">
              <button class="btn-box btn-box-little azul-escuro">Anunciar</button>
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="/anuidade-ano-vigente" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_2']) ? 'background-color:'.$itens_home['cards_2'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/printer.png') }}" class="inside-img" alt="Anuidade do ano vigente | Core-SP" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Boleto<br class="hide-992" /> anuidade {{ date('Y') }}</h3>
              <button class="btn-box azul">Acessar</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 text-right pb-15">
        <a href="/simulador" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_2']) ? 'background-color:'.$itens_home['cards_2'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/001-paper.png') }}" class="inside-img" alt="Simulador | Core-SP">
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Simulador de<br class="hide-992" /> valores</h3>
              <button class="btn-box azul">Simular</button>
            </div>
          </div>
        </a>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <div class="box text-center {{ isset($itens_home['cards_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_1']) ? 'background-color:'.$itens_home['cards_1'] : '' }}">
          <div class="inside-box">
            <img src="{{ asset('img/appointment.png') }}" class="inside-img" alt="Agendamento | Core-SP" />
            <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Agendamento<br class="hide-992" /> de Atendimento</h3>
            <a href="/agendamento" class="d-inline h-100">
              <button class="btn-box azul-escuro">Agendar</button>
            </a>
            <a href="/agendamento-consulta" class="d-inline h-100">
              <button class="btn-box btn-box-little azul-escuro">Consultar</button>
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="/cartilha-do-representante" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_2']) ? 'background-color:'.$itens_home['cards_2'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/open-book.png') }}" class="inside-img" alt="Cartilha do Representante | Core-SP" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Cartilha do<br class="hide-992" /> Representante</h3>
              <button class="btn-box azul">Visualizar</button>
            </div>
          </div>
        </a>
      </div>
      {{--<div class="col-lg-3 col-sm-6 pb-15">
        <!-- <a href="/noticias/anuidade-2021-taxas-e-emolumentos" class="d-block h-100"> -->
          <div class="box text-center {{ isset($itens_home['cards_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_1']) ? 'background-color:'.$itens_home['cards_1'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/003-bill.png') }}" class="inside-img" alt="Anuidade 2019 | Core-SP" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Anuidade 2021<br class="hide-992" /> taxas e emolumentos</h3>
              <!-- <button href="#" class="btn-box azul-escuro">ACESSAR</button> -->
            </div>
          </div>
        <!-- </a> -->
      </div>--}}

      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="{{ route('cursos.index.website') }}" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_1']) ? 'background-color:'.$itens_home['cards_1'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/icone-curso.png') }}" class="inside-img" alt="Cursos" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Cursos</h3>
              <button href="#" class="btn-box azul-escuro">ACESSAR</button>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="{{ route('agenda-institucional') }}" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_1']) ? 'background-color:'.$itens_home['cards_1'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/appointment.png') }}" class="inside-img" alt="Serviços do Atendimento" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Agenda<br class="hide-992" /> Institucional</h3>
              <button href="#" class="btn-box azul-escuro">ACESSAR</button>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="https://core-sp.implanta.net.br/portaltransparencia/#publico/inicio" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_2']) ? 'background-color:'.$itens_home['cards_2'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/icone-portal-da-transparencia.png') }}" class="inside-img" alt="Portal da transparência" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Portal da <br class="hide-992" /> Transparência</h3>
              <button href="#" class="btn-box azul-escuro">ACESSAR</button>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="https://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_1']) ? '' : 'azul-escuro-bg' }}" style="{{ isset($itens_home['cards_1']) ? 'background-color:'.$itens_home['cards_1'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/icone-denuncie.png') }}" class="inside-img" alt="Exercício ilegal da profissão" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Denuncie o Exercício<br class="hide-992" /> Ilegal da Profissão</h3>
              <button href="#" class="btn-box azul-escuro">ACESSAR</button>
            </div>
          </div>
        </a>
      </div>

      <div class="col-lg-3 col-sm-6 pb-15">
        <a href="/noticias/core-sp-e-a-protecao-de-dados-pessoais" class="d-block h-100">
          <div class="box text-center {{ isset($itens_home['cards_2']) ? '' : 'azul-bg' }}" style="{{ isset($itens_home['cards_2']) ? 'background-color:'.$itens_home['cards_2'] : '' }}">
            <div class="inside-box">
              <img src="{{ asset('img/icone-termo.png') }}" class="inside-img" alt="Termo de Consentimento" />
              <h3 class="text-uppercase mt-3 branco light h3-box mb-3">Termo de Consentimento</h3>
              <button href="#" class="btn-box azul-escuro">ACESSAR</button>
            </div>
          </div>
        </a>
      </div>

    </div>
  </div>
</section>

<section id="beneficios" class="mb-2">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="beneficios-box row nomargin">
          <div class="col-lg-7 beneficios-txt center-992">
            <h2 class="stronger text-white text-uppercase">Programa de Benefícios</h2>
            <p class="text-white light">O Core-SP traz serviços diferenciados para Representantes Comerciais.</p>
            <p class="text-white light">Faça parte do Grupo do WhatsApp e receba todos os dias os benefícios disponíveis.</p>
            <div>
              <a href="/programa-de-beneficios-core-sp" class="btn-beneficios">saiba mais</a>
              <a href="https://chat.whatsapp.com/HPAXB7yne537CRQChfRfQe" class="btn-beneficios bg-white"><i class="fab fa-whatsapp fa-lg"></i> Grupo WhatsApp</a>
            </div>
          </div>
          <div class="col-lg-5 hide-992">
            <img class="lazy-loaded-image lazy" data-src="{{ asset('img/Imagem-celular002_beneficios_2024.png') }}" id="computer" alt="Programa de Benefícios | Core-SP" />
          </div>
        </div>
      </div>
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
            <h2 class="pr-3 ml-1">Cotidiano</h2>
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
            <h2 class="pr-3 ml-1">Fale com o CORE-SP</h2>
          </blockquote>
        </div>
        <div class="row">
          <div class="col-lg-6 faleSingle">
            <div class="row nomargin">
              <div class="align-self-center">
                <img src="{{ asset('img/the-phone-icon.png') }}" class="inside-img" alt="Atendimento | Core-SP">
              </div>
              <div class="flex-one fale-txt align-self-center">
                <h5 class="normal">Atendimento<br class="hide-992" /> (11) 3243-5500</h5>
              </div>
            </div>
          </div>
          <div class="col-lg-6 faleSingle">
            <a href="http://core-sp.implanta.net.br/portaltransparencia/#publico/inicio" target="_blank">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/icon_transparencia.png') }}" alt="Transparência | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Acesso à<br class="hide-992" /> informação</h5>
                </div>
              </div>
            </a>
          </div>
        </div>
        <div class="home-title mt-4">
          <blockquote>
            <i></i>
            <h2 class="pr-3 ml-1">CORE-SP nas Mídias Sociais</h2>
          </blockquote>
        </div>
        <div class="row">
          <div class="col-lg-6 faleSingle">
            <a href="https://t.me/+xrSNaf5S10oxZGE5" target="_blank">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/icone-telegram.png') }}" class="inside-img" alt="Telegram | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Faça parte do nosso grupo no Telegram<br class="hide-992" /></h5>
                </div>
              </div>
            </a>
          </div>
          <div class="col-lg-6 faleSingle">
            <a href="https://www.youtube.com/channel/UCOT_xwrQrpl_uu8MFl_EzWQ" target="_blank">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/the-youtube-icon.png') }}" class="inside-img" alt="YouTube | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Nosso canal<br class="hide-992" /> no YouTube</h5>
                </div>
              </div>
            </a>
          </div>
          <div class="col-lg-6 faleSingle">
            <a href="https://www.instagram.com/coresaopaulo/?hl=pt-br" target="_blank">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/the-instagram-icon.png') }}" class="inside-img" alt="Instagram | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Siga-nos no<br class="hide-992" /> Instagram</h5>
                </div>
              </div>
            </a>
          </div>
          <div class="col-lg-6 faleSingle">
            <a href="https://www.linkedin.com/company/core-saopaulo">
              <div class="row nomargin">
                <div class="align-self-center">
                  <img src="{{ asset('img/the-linkedin-logo.png') }}" class="inside-img" alt="LinkedIn | Core-SP">
                </div>
                <div class="flex-one fale-txt align-self-center">
                  <h5 class="normal">Siga-nos no<br class="hide-992" /> LinkedIn</h5>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="eouv-calendario" class="pb-5">
  <div class="container">
    <div class="row mb-2">
      <div class="col-12">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h2 class="pr-3 ml-1">Blog</h2>
          </blockquote>
          <h5 class="float-right cinza-claro-bg">
            <a href="{{ route('site.blog') }}"><i class="fas fa-plus-circle icon-title"></i> Ver mais posts</a>
          </h5>
        </div>
        <div></div>
      </div>
    </div>
    <div class="row" id="home-blog">
      @foreach($posts as $post)
        @include('site.inc.post-grid')
      @endforeach
    </div>
    <div class="row">
      <div class="col-lg-6">
        <div class="home-title">
          <blockquote>
            <i></i>
            <h2 class="pr-3 ml-1">E-ouv</h2>
          </blockquote>
        </div>
        <div class="row">
          <div class="col-sm-8">
            <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank">
              <img class="lazy-loaded-image lazy" data-src="{{ asset('img/computer.png') }}" alt="E-OUV | Core-SP" />
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
            <h2 class="pr-3 ml-1">Calendário</h2>
          </blockquote>
        </div>
        <div id="calendario" class="row">
          <div class="col-sm-8">
            <a href="/calendario-oficial-core-sp">
              <img class="lazy-loaded-image lazy" data-src="{{ isset($itens_home['calendario']) ? asset($itens_home['calendario']) : asset('img/arte-calendario-2023.png') }}" alt="Calendário | Core-SP" />
            </a>
          </div>
          <div class="col-sm-4 hide-576 align-self-center text-right pr-4">
            <div class="calendario-txt">
              <p class="preto">Confira o calendário completo de<br>atendimento e expediente <br>de sua região.</p>
              <a href="/calendario-oficial-core-sp" class="btn-calendario mt-4">
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
