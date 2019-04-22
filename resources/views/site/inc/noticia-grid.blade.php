@php
  use App\Http\Controllers\Helper;
@endphp
<div class="col-sm-4">
  <a href="/noticia/{{ $noticia->slug }}">
    <div class="box-news">
      <img src="{{ asset(Helper::imgToThumb($noticia->img)) }}" class="bn-img" />
      <div class="box-news-txt">
        <h6 class="light cinza-claro">{{ Helper::newsData($noticia->updated_at) }}</h6>
        <h5 class="branco mt-1">{{ $noticia->titulo }}</h5>
      </div>
    </div>
  </a>
</div>