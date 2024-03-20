@extends('site.layout.app', ['title' => 'Prestação de Contas do Core-SP'])

@section('content')

@php
use Illuminate\Support\Str;
$titulos = $resultado->whereIn('nivel', [0])->pluck('ordem')->toArray();
@endphp

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-prestacao-de-contas.jpg') }}" alt="CORE-SP" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
        Prestação de Contas do Core-SP
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
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2">
	    <div class="col-lg conteudo-txt prestacao-txt">
        <p class="text-justify">
          O Conselho Regional dos Representantes Comerciais no Estado de São Paulo, em consonância com a Instrução Normativa nº 84/2020 e a Decisão Normativa nº 187/2020, 
          ambas do Tribunal de Contas da União, implementou novo modelo de prestação de contas, garantindo maior transparência, completude e clareza na disponibilização 
          das informações acerca da gestão e dos atos emanados pelo Core-SP.
        </p>
        <p class="text-justify">
          Dessa forma, todas as informações referentes à prestação de contas serão permanentemente disponibilizadas e atualizadas ao longo do exercício financeiro, de 
          acordo com os prazos definidos pelo Tribunal de Contas da União, possibilitando o imediato acesso por qualquer cidadão ou interessado por meio dos seguintes 
          links:
        </p>

        @if($resultado->isEmpty())
        <p><i>Informações sendo atualizadas.</i></p>

        @else

        <div id="accordionPrimario" class="accordion">
        @foreach($resultado as $texto)
          
          @if($texto->tipoTitulo())

            @php
              $temp_titulo_slug = Str::slug(strtolower($texto->texto_tipo), '-');
              $temp_titulo_studly = Str::studly($temp_titulo_slug);
            @endphp

          <p class="pb-0">
            <a href="#lista-{{ $temp_titulo_slug }}" data-toggle="collapse">
              @if(\File::exists(public_path('img/icone-' . $temp_titulo_slug . '.png')))
              <img src="{{ asset('img/icone-' . $temp_titulo_slug . '.png') }}" width="320" height="143" />
              @else
              <strong><u>{{ $texto->texto_tipo }}</u></strong>
              @endif
            </a>
          </p>

          <div id="lista-{{ $temp_titulo_slug }}" class="collapse" data-parent="#accordionPrimario">
            <div id="accordion{{ $temp_titulo_studly }}" class="accordion">
              <ul class="mb-0 pb-0">
          @else
          
            @php
              if(!$texto->possuiConteudoPrestacaoContas())
                $temp_sub_slug = Str::slug(strtolower($texto->texto_tipo), '-');
              if(!$texto->possuiConteudoPrestacaoContas() && in_array($texto->nivel, [1,2]))
                $temp[$texto->nivel] = $temp_sub_slug;
            @endphp
                <li>
                  <a href="{{ $texto->possuiConteudoPrestacaoContas() ? strip_tags($texto->conteudo) : '#lista-'.$temp_sub_slug }}" 
                    target="_blank" rel="noopener"
                    @if(!$texto->possuiConteudoPrestacaoContas())
                      data-toggle="collapse"
                    @endif
                  >
                    {{ $texto->texto_tipo }}
                  </a>
                  @if($texto->possuiConteudoPrestacaoContas())
                </li>
                  @elseif(!$texto->possuiConteudoPrestacaoContas() && !$loop->last)
                  <div id="lista-{{ $temp_sub_slug }}" class="collapse" data-parent="#{{ ($texto->nivel > 1) && isset($temp[$texto->nivel - 1]) ? 'lista-'.Str::slug(strtolower($temp[$texto->nivel - 1]), '-') : 'accordion'.$temp_titulo_studly }}">
                    <ul class="mb-0 pb-0">
                  @endif

                  @if(((isset($resultado[$loop->index + 1]) && ($resultado->get($loop->index + 1)->nivel < $texto->nivel)) || $loop->last) && ($texto->nivel > 1))
                    @php
                      $cont = $loop->last ? 0 : $resultado->get($loop->index + 1)->nivel;
                      $cont = $cont == 0 ? $texto->nivel - 1 : $texto->nivel - $cont;
                    @endphp
                    {!! str_repeat('</ul></div></li>', $cont) !!}
                  @endif
          @endif

          @if(in_array($texto->ordem + 1, $titulos) || $loop->last)
              </ul>
            </div>
          </div>
          @endif

        @endforeach

        </div>
        @endif
        
      </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
	  </div>
  </div>
</section>

@endsection