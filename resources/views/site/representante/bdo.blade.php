@extends('site.representante.app')

@section('content-representante')

<!-- incluir em site.css -->
<style>
    .border-3 {
        border-width: 3px !important;
    }
</style>

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Perfil</h4>
        <div class="linha-lg-mini mb-1"></div>

        @if(isset($perfil_bdo))
        <div class="list-group w-100">
            <div class="list-group-item light d-block border border-3 rounded border-{{ $perfil_bdo->statusRCCores() }}">
        
                <p class="pb-0">
                    ID: <strong>{{ $perfil_bdo->id }}</strong>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    Nome: <strong>{{ $perfil_bdo->representante->nome }}</strong>
                </p>
                <p class="pb-0">
                    Registro Core: <strong>{{ $perfil_bdo->representante->registro_core }}</strong>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    CNPJ: <strong>{{ $perfil_bdo->representante->cpf_cnpj }}</strong>
                </p>
                <p class="pb-0">
                <!-- Se foi solicitada mudança, mostrar o segmento pela tabela de alteração para evitar mostrar se foi aceito ou não antes de finalizar -->
                @if(($perfil_bdo->statusRC() == $perfil_bdo::STATUS_RC_CADASTRO) && 
                ($perfil_bdo->alteracoesRC->where('informacao', strtoupper('segmento'))->isNotEmpty()))
                    <i class="fas fa-sync-alt fa-sm text-primary"></i>&nbsp;
                    Segmento: <strong>{{ $perfil_bdo->alteracoesRC->where('informacao', strtoupper('segmento'))->first()->valor_atual }}</strong>
                @else
                    Segmento: <strong>{{ $perfil_bdo->segmento }}</strong>
                @endif
                    &nbsp;&nbsp;|&nbsp;&nbsp;

                <!-- Se foi solicitada mudança, mostrar a regional pela tabela de alteração para evitar mostrar se foi aceito ou não antes de finalizar -->
                @if(($perfil_bdo->statusRC() == $perfil_bdo::STATUS_RC_CADASTRO) && 
                ($perfil_bdo->alteracoesRC->where('informacao', strtoupper('regional'))->isNotEmpty()))
                    <i class="fas fa-sync-alt fa-sm text-primary"></i>&nbsp;
                    Regional: <strong>{{ $perfil_bdo->alteracoesRC->where('informacao', strtoupper('regional'))->first()->valor_atual }}</strong>
                @else
                    Regional: <strong>{{ json_decode($perfil_bdo->regioes)->seccional }}</strong>
                @endif
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    Telefone: <strong>{{ $perfil_bdo->telefone }}</strong>
                </p>
                <p class="pb-0">
                    E-mail: <strong>{{ $perfil_bdo->email }}</strong>
                </p>
                <p class="pb-0">
                    Endereço: <strong>{{ $perfil_bdo->endereco }}</strong>
                </p>
                <p class="pb-0">
                    Municípios de atuação: <strong>{!! implode('&nbsp;&nbsp;|&nbsp;&nbsp;', json_decode($perfil_bdo->regioes)->municipios) !!}</strong>
                </p>
                <p class="pb-0">
                    Descrição:<br>
                    <!-- manter a formatação criada no <textarea> -->
                    <span style="white-space: pre-wrap;"><strong>{{ $perfil_bdo->descricao }}</strong></span>
                </p>
                <p class="pb-0 mt-3">
                    Status: <strong>{{ $perfil_bdo->statusRC() }}</strong>
                    &nbsp;&nbsp;|&nbsp;&nbsp;

                    @if(!is_null($perfil_bdo->justificativaParaRC()))
                    <i class="far fa-comment-dots"></i>&nbsp;&nbsp;<strong>{{ $perfil_bdo->justificativaParaRC() }}</strong>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    @endif

                    <i><small>Atualizado em: <strong>{{ formataData(json_decode($perfil_bdo->status)->data) }}</strong></small></i>
                </p>
            </div>
        </div>

            @if($perfil_bdo->statusRC() == $perfil_bdo::STATUS_RC_REMOVIDO)
            <a class="btn btn-primary text-white my-3" href="{{ route('representante.bdo.perfil') }}">Iniciar publicação de Perfil</a>
            @endif

            @if($perfil_bdo->statusRC() == $perfil_bdo::STATUS_RC_PUBLICO)
            <form method="POST" action="{{ route('representante.bdo.perfil.remover') }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger my-3" type="submit">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
            @endif

        @else
        <p>Agora pode publicar seu perfil de Representante no Portal!</p>
        <a class="btn btn-primary text-white my-3" href="{{ route('representante.bdo.perfil') }}">Iniciar publicação de Perfil</a>
        @endif
    </div>

    <hr class="bg-success"/>

    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Oportunidades</h4>
        <div class="linha-lg-mini mb-1"></div>
        <h5 class="mb-1">
            <span class="badge badge-success p-2">Outras oportunidades 
                <a href="{{ route('bdosite.index') }}" target="_blank" class="text-white">
                    <u>aqui</u>
                </a>
            </span>
        </h5>
    @if($segmento)
        <div class="contatos-table space-single bg-info mb-2">
            <p class="light pb-0 text-white" data-clarity-mask="True">
                @if($bdo->count() == 0)
                Não foi encontrada nenhuma oportunidade <strong>em andamento</strong> para o seu segmento - <strong>{{ $segmento }}</strong> e sua seccional - <strong>{{ $seccional }}</strong>
                @else
                {!! $bdo->count() == 1 ? 'Foi encontrada 1 oportunidade' : 'Foram encontradas <strong>'.$bdo->count().'</strong> oportunidades' !!} <strong>em andamento</strong> para o seu segmento - <strong>{{ $segmento }}</strong> e sua seccional - <strong>{{ $seccional }}</strong>
                @endif
            </p>
        </div>
        <div class="contatos-table">
            @foreach($bdo as $b)
                <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                    <p class="pb-0">
                    <strong>{{ $b->titulo }} -</strong> Essa empresa possui {{ $b->vagasdisponiveis }} {{ $b->vagasdisponiveis > 1 ? 'vagas disponíveis' : 'vaga disponível' }}! 
                    <a href="{{ $b->observacao }}" target="_blank" class="alert-link"><u>Confira aqui</u></a>.
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <div class="contatos-table space-single">
            <p class="light pb-0">Você não cadastrou o segmento. Por favor, atualize seus dados para receber as oportunidades.</p>
        </div>
    @endif
    </div>
</div>

@endsection