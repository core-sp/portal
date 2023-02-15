<div class="card-body">
    <div class="row">
        <div class="col">
            <p><small>
                * Alguns detalhes de layout, como margem e fonte, podem sofrer alterações na área do {{ $resultado->area }}
            </small></p>
            @component(
                'components.aviso-'.$resultado::componente()[$resultado->area], 
                [
                    'cor_fundo_titulo' => $resultado->cor_fundo_titulo,
                    'titulo' => $resultado->titulo, 
                    'conteudo' => $resultado->conteudo
                ]
            )
            @endcomponent
        </div>
    </div>
</div>