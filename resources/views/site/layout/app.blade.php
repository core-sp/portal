<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if (View::hasSection('description'))
          @yield('description')
        @else
          <meta name="description" content="O Core-SP é responsável pela consulta, orientação, disciplina e fiscalização do exercício da profissão de Representação Comercial no estado de São Paulo.">
        @endif

        <title>Core-SP — {{ $title }}</title>
        <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/ico" />

        <link type="text/css" href="{{ asset('/css/app.css') }}" rel="stylesheet">
        <link type="text/css" href="{{ asset('/css/datepicker.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css" rel="stylesheet">
        <link type="text/css" href="{{ asset('/css/site.css?'.time()) }}" rel="stylesheet">

        @yield('meta')

        @if(strstr(request()->getHttpHost(), 'core-sp.org.br'))
          <!-- Global site tag (gtag.js) - Google Analytics -->
          <script async src="https://www.googletagmanager.com/gtag/js?id=UA-141375220-1"></script>
          <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'UA-141375220-1');
          </script>
        @endif
        {!! mostraChatScript() !!}

        <!-- ********************************************************************************************************************** -->
        @if(strpos(config('app.url'), 'homolog') !== false)
        <!-- Teste de uso Clarity - Microsoft -->
        <script type="text/javascript">
          (function(c,l,a,r,i,t,y){
              c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
              t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
              y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
          })(window, document, "clarity", "script", "gd7y1ey5tx");
        </script>
        @endif
        
    </head>
    <body>

    <!-- ACESSIBILIDADE -->
    <a href="#header-principal" accesskey="1"></a>
    <a href="#conteudo-principal" accesskey="3"></a>
    <a href="#rodape" accesskey="4"></a>
    <a id="accesskeyContraste" accesskey="5"></a>

      @section('header')
      <header id="header-principal">
        <div class="container-fluid">
          <div class="row">
            <div class="linha-dourada w-100"></div>
          </div>
        </div>
        <div class="container">
          <div class="row header-margin">
            <div class="col-lg-4 col-md-6 text-left center-768">
                <a href="/"><img src="{{ asset('img/core_selo.png') }}" alt="CORE-SP" id="logo-header" /></a>
            </div>
            <div class="col-lg-4 col-md-6 center-768 aai hide-768">
              <div class="d-block setembro">
                <h3><a href="/servicos-atendimento-ao-rc" class="cinza-claro">Atendimento on-line</a></h3>
                <p class="light"><a href="/servicos-atendimento-ao-rc" class="cinza-claro"><small>Clique aqui e tenha acesso aos principais<br>serviços pelo Setor de Atendimento do Core-SP.</small></a></p>
              </div>
            </div>
            <div class="col-lg-4 align-self-end mt-15-992">
              <div class="mexe-tamanho m-auto text-center">
                @component('components.login-header')
                @endcomponent
                <div class="acessibilidade center-992 text-right hide-768">
                  <button type="button" class="btn btn-sm btn-light btn-acessibilidade" id="increase-font">
                    <h6>A+</h6>
                  </button>
                  <button type="button" class="btn btn-sm btn-light btn-acessibilidade" id="decrease-font">
                    <h6>A-</h6>
                  </button>
                  <button type="button" class="btn btn-sm btn-light btn-access" id="btn-contrast">
                    <i class="fas fa-adjust"></i>
                  </button>
                  <a href="/mapa-do-site">
                    <button type="button" class="btn btn-sm btn-light btn-access">
                      <i class="fas fa-map-marker-alt"></i>
                    </button>
                  </a>
                  <a href="/acessibilidade">
                    <button type="button" class="btn btn-sm btn-light btn-access">
                      <i class="fas fa-wheelchair"></i>
                    </button>
                  </a>
                </div>
                <form class="input-group input-group-sm hide-768"
                  method="GET"
                  role="form"
                  action ="/busca"
                />
                  <input type="text"
                    name="busca"
                    class="form-control float-right {{ $errors->has('busca') ? 'is-invalid' : '' }}"
                    placeholder="Digite o que você procura"
                    accesskey="2" />
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                  @if($errors->has('busca'))
                    <div class="invalid-feedback text-left">
                      {{ $errors->first('busca') }}
                    </div>
                  @endif
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="container-fluid">
          <div class="row">
            <div class="linha-cinza"></div>
          </div>
        </div>
        <div class="container-fluid menu-inteiro pb-1">
          <div class="container">
            <div class="row" id="menu-principal">
              <nav class="menu-principal m-auto">
                <ul class="nomargin nopadding">
                  <li class="nav-item">
                    <a href="/" class="nav-link">Home</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownCore" role="button" data-toggle="dropdown">CORE-SP</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownCore">
                      <a href="/concursos" class="dropdown-item">Concursos</a>
                      <a href="/conselho" class="dropdown-item">Conselho</a>
                      <a href="/institucional" class="dropdown-item">Institucional</a>
                      <a href="/legislacao" class="dropdown-item">Legislação</a>
                      <a href="/missao-visao-e-valores" class="dropdown-item">Missão, Visão e Valores</a>
                      <a href="/politica-de-privacidade" class="dropdown-item bb-0">Política de Privacidade</a>
                    </div>
                  </li>
                  <li class="nav-item">
                    <a href="http://core-sp.implanta.net.br/portaltransparencia/#publico/inicio" class="nav-link" target="_blank">Transparência</a>
                  </li>
                  <li class="nav-item">
                    <a href="/prestacao-de-contas-do-core-sp" class="nav-link">Prestação de Contas</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownFiscal" role="button" data-toggle="dropdown">Fiscalização</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownFiscal">
                      <a href="{{ route('fiscalizacao.mapa') }}" class="dropdown-item">Mapa da fiscalização</a>
                      <a href="{{ route('fiscalizacao.acoesfiscalizacao') }}" class="dropdown-item">Ações da fiscalização</a>
                      <a href="{{ route('fiscalizacao.espacoContador') }}" class="dropdown-item">Espaço do Contador</a>
                      <a href="http://core-sp.implanta.net.br/portaltransparencia/#OUV/Home" target="_blank" class="dropdown-item">Denuncie</a>
                      <a href="/sobre-a-fiscalizacao" class="dropdown-item bb-0">Sobre o departamento</a>
                    </div>
                  </li>
                  <li class="nav-item">
                    <a href="/licitacoes" class="nav-link">Licitações e Aquisições</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownServ" role="button" data-toggle="dropdown">Serviços</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownServ">
                      <div class="sub-dropdown">
                        <div class="dropdown-item">
                          Agendamento <i class="fas fa-angle-right hide-768"></i><i class="fas fa-angle-down show-inline-768"></i>
                        </div>
                        <div class="sub-dropdown-menu">
                          <a href="/agendamento" class="dropdown-item">Agendar</a>
                          <a href="/agendamento-consulta" class="dropdown-item bb-0">Consultar/Cancelar</a>
                        </div>
                      </div>
                      <a href="/anuidade-ano-vigente" class="dropdown-item">Anuidade {{ date('Y') }}</a>
                      <a href="/representante/login" class="dropdown-item">Área Restrita</a>
                      <a href="/servicos-atendimento-ao-rc" class="dropdown-item">Atendimento</a>
                      <div class="sub-dropdown">
                        <div class="dropdown-item">
                          Balcão de Oportunidades <i class="fas fa-angle-right hide-768"></i><i class="fas fa-angle-down show-inline-768"></i>
                        </div>
                        <div class="sub-dropdown-menu">
                          <a href="/balcao-de-oportunidades" class="dropdown-item">Acessar</a>
                          <a href="/anunciar-vaga" class="dropdown-item bb-0">Anunciar</a>
                        </div>
                      </div>
                      <a href="/carta-de-servicos-ao-usuario" class="dropdown-item">Carta de Serviços ao Usuário</a>
                      <a href="/consulta-de-situacao" class="dropdown-item">Consulta de Situação</a>
                      <a href="/cursos" class="dropdown-item">Cursos</a>
                      <a href="/downloads" class="dropdown-item">Downloads</a>
                      <a href="/programa-de-incentivos" class="dropdown-item">Programa de Incentivos</a>
                      <a href="/simulador" class="dropdown-item bb-0">Simulador de Valores</a>
                    </div>
                  </li>
                  <li class="nav-item">
                    <a href="/seccionais" class="nav-link">Seccionais</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownImprensa" role="button" data-toggle="dropdown">Imprensa</a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownImprensa">
                      <a href="{{ route('noticias.siteGrid') }}" class="dropdown-item">Notícias</a>
                      <a href="/informativos" class="dropdown-item">Informativos</a>
                      <a href="/blog" class="dropdown-item">Blog</a>
                      <a href="https://www.confere.org.br/revista.php" target="_blank" class="dropdown-item bb-0">Revistas</a>
                      {{--<a href="#" class="dropdown-item bb-0">Artigos</a>--}}
                    </div>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
        <a href="/servicos-atendimento-ao-rc">
          <div class="container-fluid azul-claro-bg pt-2 pb-2">
            <div class="row">
                <div class="container">
                  <div class="d-flex">
                    <div class="flex-one text-center">
                      <h5 class="d-inline-block pr-3 cinza-claro"><strong>Dúvidas frequentes</strong></h5>
                      <p class="d-inline-block cinza-claro">Clique aqui e tenha acesso aos principais serviços <br class="show-768">pelo Setor de Atendimento do Core-SP.</p>
                    </div>
                  </div>
                </div>
            </div>
          </div>
        </a>  
      </header>

      <header id="fixed-menu" class="pb-2">
        <div class="container">
          <a href="/">
            <img src="{{ asset('img/brasao.png') }}" alt="Core-SP" />
          </a>
          <div class="row" id="append-menu">
            
          </div>
        </div>
      </header>

      <div id="menuResponsivo">
        <div class="container">
          <button type="button" id="sidebarBtn" class="btn btn-info">
            <i class="fas fa-align-left"></i>&nbsp;
            <span>Menu</span>
          </button>
        </div>
      </div>
      <nav id="sidebar">
        <div class="sidebar-header">
            <h3 class="branco">Menu</h3>
        </div>
        <div id="dismiss">
            <i class="fas fa-arrow-left"></i>
        </div>
        <div id="sidebarContent"></div>
      </nav>
      <div class="overlay"></div>
      @show
      
      @yield('content')

      @section('footer')
      <div class="linha-dourada"></div>
      <footer class="pt-4" id="rodape">
        <div class="container mb-4">
          <div class="row">
            <div class="col-md-4">
              <div class="footer-title w-75 mb-3">
                <h5 class="branco">Localização</h5>
              </div>
              <p class="branco mb-1">
                <strong>SEDE</strong>
                <br />Av. Brigadeiro Luís Antônio, 613
                <br />5º andar - CEP: 01317-000
                <br />Bela Vista - São Paulo - SP
              </p>
              <br />
              <p class="branco mb-1">
                <strong>UNIDADE ADMINISTRATIVA ALAMEDA SANTOS</strong>
                <br />Alameda Santos, 1787
                <br />Cj. 61 - CEP: 01419-906
                <br />Cerqueira César - São Paulo - SP
              </p>
              <br />
              <p class="branco mb-1">
                CNPJ: 60.746.179/0001-52
              </p>
              <div class="footer-title w-75 mb-3 mt-4">
                <h5 class="branco">Contato</h5>
              </div>
              <p class="branco mb-1">
                <strong>E-mail:</strong>
                <br />atendimento@core-sp.org.br
              </p>
              <p class="branco">
                <strong>Telefone:</strong>
                <br />(11) 3243-5500 / 5519
              </p>
            </div>
            <div class="col-md-4">
              <div class="footer-title w-75 mb-3 mt-4-768">
                <h5 class="branco">Links úteis</h5>
              </div>
              <div class="text-center w-75">
                <a href="http://www.confere.org.br/" target="_blank">
                  <img src="{{ asset('img/logoConfere.png') }}" alt="CONFERE">
                </a>
              </div>
              <div class="footer-title w-75 mb-3 mt-4">
                <h5 class="branco">Serviços</h5>
              </div>
              <p class="branco"><a href="/agendamento">Agendamento</a></p>
              <p class="branco"><a href="/anuidade-ano-vigente">Anuidade {{ date('Y') }}</a></p>
              <p class="branco"><a href="/representante/login">Área Restrita</a></p>
              <p class="branco"><a href="/servicos-atendimento-ao-rc">Atendimento</a></p>
              <p class="branco"><a href="/balcao-de-oportunidades">Balcão de Oportunidades</a></p>
              <p class="branco"><a href="/carta-de-servicos-ao-usuario">Carta de Serviços ao Usuário</a></p>
              <p class="branco"><a href="/consulta-de-situacao">Consulta de Situação</a></p>
              <p class="branco"><a href="/cursos">Cursos</a></p>
              <p class="branco"><a href="/downloads">Downloads</a></p>
              <p class="branco"><a href="/politica-de-privacidade">Política de Privacidade</a></p>
              <p class="branco"><a href="/programa-de-incentivos">Programa de Incentivos</a></p>
              <p class="branco"><a href="/simulador">Simulador de Valores</a></p>
            </div>
            <div class="col-md-4">
              <div class="footer-title w-75 mb-3 mt-4-768">
                <h5 class="branco">Transparência</h5>
              </div>
              <div class="w-75">
                <a href="http://core-sp.implanta.net.br/portaltransparencia/#publico/inicio" target="_blank">
                  <img src="{{ asset('img/icon_transparencia.png') }}" alt="Transparência | Core-SP" class="d-inline transparencia-footer">&nbsp;&nbsp;<p class="d-inline branco lh-32">Acesso à informação</p>
                </a>
              </div>
              <div class="footer-title w-75 mb-3 mt-4">
                <h5 class="branco">Newsletter</h5>
              </div>
              <div class="w-75">
                <p class="branco">Inscreva-se para receber nossos informativos:</p>
                <form class="mt-3" id="newsletter" method="POST" action="/newsletter">
                  @csrf
                  <div class="form-group">
                    <input type="text"
                      name="nomeNl"
                      class="form-control {{ $errors->has('nomeNl') ? 'is-invalid' : '' }}"
                      value="{{ old('nomeNl') }}"
                      placeholder="Nome *"
                      required
                      />
                      @if($errors->has('nomeNl'))
                      <div class="invalid-feedback">
                      {{ $errors->first('nomeNl') }}
                      </div>
                      @endif
                  </div>
                  <div class="form-group">
                    <input type="text"
                      name="emailNl"
                      class="form-control {{ $errors->has('emailNl') ? 'is-invalid' : '' }}"
                      value="{{ old('emailNl') }}"
                      placeholder="E-mail *"
                      required
                      />
                      @if($errors->has('emailNl'))
                      <div class="invalid-feedback">
                      {{ $errors->first('emailNl') }}
                      </div>
                      @endif
                  </div>
                  <div class="form-group">
                    <input type="text"
                      name="celularNl"
                      class="form-control celularInput {{ $errors->has('celularNl') ? 'is-invalid' : '' }}"
                      value="{{ old('celularNl') }}"
                      placeholder="Celular *"
                      required
                      />
                      @if($errors->has('celularNl'))
                      <div class="invalid-feedback">
                      {{ $errors->first('celularNl') }}
                      </div>
                      @endif
                  </div>
                  <p class="branco mb-2 text-justify textoTermo">Você pode cancelar a sua inscrição a qualquer momento. Suas informações serão armazenadas dentro dos mais rígidos critérios de segurança no banco de dados do CORE-SP e serão tratadas de acordo com a Lei Geral de Proteção de Dados Pessoais (LGPD)</p>
                  <div class="form-check">
                    <input type="checkbox"
                      name="termo"
                      class="form-check-input {{ $errors->has('termo') ? 'is-invalid' : '' }}"
                      id="termo"
                      {{ old('termo') ? 'checked' : '' }}
                      required
                    /> 
                    <label for="termo" class="branco textoTermo text-justify">Li e concordo com o <a href="{{route('termo.consentimento.pdf')}}" target="_blank"><u>Termo de Consentimento</u></a> de uso de dados, e aceito receber boletins informativos a respeito de parcerias e serviços do CORE-SP.
                    </label>
                    @if($errors->has('termo'))
                    <div class="invalid-feedback">
                      {{ $errors->first('termo') }}
                    </div>
                    @endif
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-default">Inscrever-se</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="linha-azul w-100"></div>
        <div class="linha-branca w-100"></div>
        <div class="linha-azul-escuro w-100"></div>     
      </footer>
      @show

      <div class="container">
        <div class="row d-flex justify-content-center">
          <div class="box-cookies hide">
            <p class="msg-cookies">Coletamos dados exclusivamente para atendimento das atividades finais desta autarquia, e para funcionamento de serviços de legítimo interesse do usuário de acordo com a nossa <strong><u><a href="/politica-de-privacidade">Política de Privacidade</a></u></strong> e, ao continuar navegando, você concorda com estas condições.</p>
            <button class="btn-cookies btn btn-default">CONCORDO</button>
          </div>     
        </div>    
      </div>     

      <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
      <script type="text/javascript" src="{{ asset('/js/jquery-ui.min.js') }}"></script>
      <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.9/jquery.lazy.min.js"></script>
      <script type="text/javascript" src="{{ asset('/js/jquery.mask.js') }}"></script>
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.js"></script>
      <script type="text/javascript" src='https://www.google.com/recaptcha/api.js?hl=pt-BR'></script>
      <script type="text/javascript" src="{{ asset('/js/site.js?'.time()) }}"></script>
      <script type="text/javascript" src="{{ asset('/js/acessibilidade.js') }}"></script>
    </body>
</html>
