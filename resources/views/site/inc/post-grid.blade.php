@php
  use App\Http\Controllers\Helper;
@endphp
<div class="col-lg-4 col-sm-6">
  <a href="/blog/{{ $post->slug }}">
    <div class="box-news">
      @if(isset($post->img))
      <img class="lazy bn-img" data-src="{{ asset(Helper::imgToThumb($post->img)) }}" />
      @else
      <img class="lazy bn-img" data-src="{{ asset('img/news-generica-thumb.png') }}" />
      @endif
      <div class="box-news-txt">
        <h6 class="light cinza-claro">{{ Helper::onlyDate($post->created_at) }}</h6>
        <h5 class="branco mt-1">{{ $post->titulo }}</h5>
      </div>
    </div>
  </a>
</div>