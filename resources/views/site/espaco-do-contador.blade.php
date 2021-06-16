@extends('site.layout.app', ['title' => 'Espaço do Contador'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-espaco-do-contador.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
        Espaço do Contador
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h2 class="stronger">Contabilidade e Representação Comercial</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>

    <div class="conteudo-txt text-justify">
      <p class="pb-0"><i>Novo ambiente virtual vai reunir documentos necessários e serviços essenciais para auxiliar os contadores que cuidam da rotina de representantes comerciais.</i></p></br>
      <p class="pb-0">Além de registrar, orientar, normatizar e fiscalizar o exercício da profissão, o Conselho Regional dos Representantes Comerciais no Estado de São Paulo também busca preservar os interesses públicos de toda a sociedade.</p></br>
      <p class="pb-0">Pensando nisso, a Coordenadoria de Fiscalização desenvolveu um novo ambiente virtual em nosso Portal: <strong>O Espaço do Contador</strong>.</p></br>
      <p class="pb-0">Nesta página, os profissionais de contabilidade vão encontrar as informações necessárias para solucionar questões administrativas, como o pagamento de taxas e regularização do registro, especialmente quando se trata de Pessoa Jurídica.</p></br>
      <p class="pb-0">A nova ferramenta é o resultado de um Termo de Cooperação, firmado em setembro de 2020, entre o Core-SP e o Conselho Regional de Contabilidade deste Estado (CRC-SP) - que busca facilitar a rotina de contadores, representantes e até mesmo das empresas representadas. Assim, a realização de procedimentos administrativos se tornará mais prática e ágil,  possibilitando a rápida efetivação de novos negócios.</p></br>
    </div>

    <div class="col-12">
      <div class="home-title">
        <blockquote>
          <h2 class="pr-3 ml-1">Notícias</h2>
        </blockquote>
      </div>
    </div>
    <div class="row mt-2">
      @forelse($noticias as $noticia)
        @include('site.inc.noticia-grid')
      @empty
        <div class="col">
          <h4>Em breve!</h4>          
        </div>
      @endforelse
    </div>
    <div class="row mb-2">
      <div class="col">
        @if(isset($noticias))
          <div class="mt-4 float-right">
            {{ $noticias->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection