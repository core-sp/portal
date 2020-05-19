<div class="col-lg-4 col-sm-6">
  <a href="{{ route('noticias.show', $noticia->slug) }}">
    <div class="box-news">
      @if(isset($noticia->img))
      <img class="lazy bn-img" data-src="{{ asset(imgToThumb($noticia->img)) }}" />
      @else
      <img class="lazy bn-img" data-src="{{ asset('img/news-generica-thumb.png') }}" />
      @endif
      <div class="box-news-txt">
        <h6 class="light cinza-claro">{{ onlyDate($noticia->created_at) }}</h6>
        <h5 class="branco mt-1">{{ $noticia->titulo }}</h5>
      </div>
    </div>
  </a>
</div>