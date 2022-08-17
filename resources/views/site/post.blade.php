@extends('site.layout.app', ['title' => isset($post) ? $post->titulo : 'Página'])

@section('description')
  <meta name="description" content="{!! retornaDescription($post->conteudo) !!}" />
@endsection

@section('meta')
  <meta property="og:url" content="{{ url('/') . '/blog/' . $post->slug }}" />
  <meta property="og:type" content="article" />
  <meta property="og:title" content="{{ $post->titulo }}" />
  <meta property="og:description" content="{!! retornaDescription($post->conteudo) !!}" />
  <meta property="og:image" content="{{ isset($post->img) ? formataImageUrl(url('/') . $post->img) : asset('img/news-generica-2.png') }}" />
  <meta property="og:image:secure_url" content="{{ isset($post->img) ? formataImageUrl(url('/') . $post->img) : asset('img/news-generica-2.png') }}" />

  <meta name="twitter:title" content="{{ $post->titulo }}" />
  <meta name="twitter:description" content="{!! retornaDescription($post->conteudo) !!}" />
  <meta name="twitter:image" content="{{ formataImageUrl(url('/') . $post->img) }}" />
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/noticias.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Blog
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-noticias">
  <div class="container">
    @if(isset($post))
      <div class="row limite-sociais">
        <div class="col-md-2 center sociais-post position-relative hide-768">
          <div id="prender">
            <a class="fb-share" href="https://www.facebook.com/dialog/share?app_id=788710944865049&display=popup&href={{ url('/') . '/blog/' . $post->slug }}" target="_blank">
              <img src="{{ asset('img/facebook-share.png') }}" alt="Core-SP | Facebook Share">
            </a>
            <a class="twitter-share" href="https://twitter.com/intent/tweet?url={{ url('/') . '/blog/' . $post->slug }}&text={{ $post->titulo }}&hashtags=RepresentanteComercial,Core-SP,Vendas">
              <img src="{{ asset('img/twitter-share.png') }}" alt="Core-SP | Twitter Share">
            </a>
            <a href="https://wa.me/?text={{ url('/') . '/blog/' . $post->slug }}" target="_blank">
              <img src="{{ asset('img/whatsapp-share.png') }}" alt="Core-SP | Whatsapp Share">
            </a>
            <a class="linkedin-share" href="https://www.linkedin.com/shareArticle?mini=true&url={{ url('/') . '/blog/' . $post->slug }}&title={{ $post->titulo }}&summary={!! strip_tags(substr($post->conteudo, 0, 100)) !!}&source=Core-SP">
              <img src="{{ asset('img/linkedin-share.png') }}" alt="Core-SP | LinkedIn Share">
            </a>
          </div>
        </div>
        <div class="col-md-8">
          <div class="d-block mb-4">
            <h1 class="post-title mb-2">{{ $post->titulo }}</h1>
            <h4 class="post-subtitle mb-3">{{ $post->subtitulo }}</h4>
            <p><small class="light">Por: {{ $post->user->perfil->nome === 'Editor' ? 'Setor de comunicação' : $post->user->nome }} | {{ formataData($post->created_at) }}</small></p>
          </div>
          <div class="d-block mb-4">
            @if(isset($post->img))
              <img src="{{asset($post->img)}}" />
            @else
              <img src="{{asset('img/news-generica-2.png')}}" />
            @endif
          </div>
          <div class="d-block conteudo-txt">
            {!! $post->conteudo !!}
          </div>
          <hr>
          <div class="row nomargin">
            <div class="col-5">
              @if(isset($previous))
                <a href="{{ route('site.blog.post', $previous->slug) }}">  
                  <h5><i class="fas fa-arrow-left"></i> Anterior</h5>
                  <p class="light mt-1">{{ $previous->titulo }}</p>
                </a>
              @endif
            </div>
            <div class="col-2"></div>
            <div class="col-5 text-right">
              @if(isset($next))
                <a href="{{ route('site.blog.post', $next->slug) }}">
                  <h5>Próximo <i class="fas fa-arrow-right"></i></h5>
                  <p class="light mt-1">{{ $next->titulo }}</p>
                </a>
              @endif
            </div>
          </div>
          <hr class="mb-4">
          <div class="d-block bot-post">
            <h5>Como podemos melhorar?</h5>
            <p>Tem alguma sugestão, critica ou elogio? Algo que gostaria de comentar? Sentiu falta de algum serviço que o Core-SP pode oferecer?</p>
            <p>Nosso objetivo é de aprimorar os serviços oferecidos para que possamos lhe atender cada vez melhor. Ajude a construir um novo Conselho!</p>
            <p><strong>Email:</strong> comunicação@core-sp.org.br</p>
          </div>
        </div>
      </div>
    @else
      @include('site.inc.content-error')
    @endif
  </div>
</section>

@endsection