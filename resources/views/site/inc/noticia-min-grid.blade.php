@php
  use App\Http\Controllers\Helper;
@endphp
<div class="row nomargin newsCotidiano">
    <div class="col nopadding">
        <h6 class="light cinza">{{ Helper::newsData($noticia->updated_at) }}</h6>
        <h5><a href="/noticia/{{ $resultado->slug }}">{{ $resultado->titulo }}</a></h5>
    </div>
</div>