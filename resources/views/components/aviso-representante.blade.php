<div id="accordion">
    <div class="card">
        <div class="card-header {{ $cor_fundo_titulo }}">
            <a data-toggle="collapse" href="#collapseOne"><i class="fas fa-angle-down"></i>&nbsp;&nbsp;
                <strong>{{ $titulo }}</strong>
            </a>
        </div>
        <div id="collapseOne" class="collapse" data-parent="#accordion">
            <div class="card-body bg-light">
                {!! $conteudo !!}
            </div>
        </div>
    </div>
</div>