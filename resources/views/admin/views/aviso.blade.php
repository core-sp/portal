<div class="card-body">
    <div class="row">
        <div class="col">
            @if($resultado->area == $resultado::areas()[1])
            <p><strong><span class="text-danger">ATENÇÃO!</span></strong> Esse aviso <strong>ATIVADO</strong> desabilita o envio de formulário para anunciar vaga!</p>
            @endif
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
    <div class="float-left">
        <a href="{{ route('avisos.index') }}" class="btn btn-default">Voltar</a>
    </div>
</div>