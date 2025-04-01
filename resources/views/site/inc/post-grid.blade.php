<div class="col-lg-4 col-sm-6">
  <a href="{{ route('site.blog.post', $post->slug) }}">
    <div class="box-news">
      @if(isset($post->img))
      <img class="lazy-loaded-image lazy bn-img" data-src="{{ asset(imgToThumb($post->img)) }}" />
      @else
      <img class="lazy-loaded-image lazy bn-img" data-src="{{ asset('img/news-generica-thumb.png') }}" />
      @endif
      <div class="box-news-txt">
        <h6 class="light cinza-claro">{{ onlyDate($post->created_at) }}</h6>
        <h5 class="branco mt-1">{{ $post->titulo }}</h5>
      </div>
    </div>
  </a>
</div>