@php
  use App\Http\Controllers\Helper;
@endphp
@if(isset($i))
  @php $classe = 'home-'.$i; @endphp
@else
  @php $classe = ''; @endphp
@endif
<div class="col-lg-4 col-sm-6 {{ $classe }}">
  <a href="/noticia/{{ $noticia->slug }}">
    <div class="box-news">
      @if(isset($noticia->img))
      <img class="lazy bn-img" data-src="{{ asset(Helper::imgToThumb($noticia->img)) }}" />
      @else
      <img class="lazy bn-img" data-src="{{ asset('img/news-generica-thumb.png') }}" />
      @endif
      <div class="box-news-txt">
        <h6 class="light cinza-claro">{{ Helper::newsData($noticia->updated_at) }}</h6>
        <h5 class="branco mt-1">{{ $noticia->titulo }}</h5>
      </div>
    </div>
  </a>
</div>