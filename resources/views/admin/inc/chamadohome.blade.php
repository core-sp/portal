@php
    use App\Http\Controllers\Helper;
@endphp

<div class="row mt-2">
    <div class="col">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Meus Chamados</h3>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Código</th>
                            <th>Tipo / Mensagem</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chamados as $chamado)
                        <tr>
                            <td>{{ $chamado->idchamado }}</td>
                            <td>{{ $chamado->tipo }}<br /><small>{{ Helper::resumoTamanho($chamado->mensagem, 75) }}</small></td>
                            <td>{{ $chamado->prioridade }}</td>
                            <td>
                            @if(isset($chamado->deleted_at) && isset($chamado->resposta))
                                <strong>Concluído<br>Resposta Emitida</strong> 
                            @elseif(isset($chamado->deleted_at))
                                <strong>Concluído</strong>
                            @elseif(isset($chamado->resposta))
                                <strong>Resposta Emitida</strong>
                            @else
                                <strong>Registrado</strong>
                            @endif
                            </td>
                            <td>
                                @if(isset($chamado->deleted_at))
                                <a href="/admin/chamados/ver/{{ $chamado->idchamado }}" class="btn btn-sm btn-default">Ver</a>
                                @else
                                    @if(!isset($chamado->resposta))
                                    <a href="/admin/chamados/editar/{{ $chamado->idchamado }}" class="btn btn-sm btn-primary">Editar</a>
                                    @else
                                    <a href="/admin/chamados/ver/{{ $chamado->idchamado }}" class="btn btn-sm btn-default">Ver</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row">
        <div class="col-sm-5 align-self-center">
        @if($chamados instanceof \Illuminate\Pagination\LengthAwarePaginator)
        @if($chamados->count() > 1)
            Exibindo {{ $chamados->firstItem() }} a {{ $chamados->lastItem() }} chamados de {{ $chamados->total() }} resultados.
        @endif
        @endif
        </div>
        <div class="col-sm-7">
        <div class="float-right">
            @if($chamados instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $chamados->links() }}
            @endif
        </div>
        </div>
    </div>
    </div>
        </div>
    </div>
</div>