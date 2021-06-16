<div class="row nomargin newsCotidiano">
    <div class="col nopadding">
        <h6 class="light cinza">{{ onlyDate($resultado->created_at) }}</h6>
        <h5><a href="{{ route('noticias.show', $resultado->slug) }}">{{ $resultado->titulo }}</a></h5>
    </div>
</div>