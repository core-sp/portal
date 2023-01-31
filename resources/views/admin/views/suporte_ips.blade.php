<div class="card-body">
    @if(isset($ips) && $ips->isNotEmpty())
    <div class="row">
        <div class="col">
            <div class="table-responsive-lg">
                <table class="table table-hover mb-0">
                    <thead class="thead">
                        <tr>
                            <th>IP</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($ips as $ip)
                        <tr>
                            <td>{{ $ip->ip }}</td>
                            <td class="text-{{ $ip->isLiberado() ? 'success' : 'danger' }}">{{ $ip->status }}</td>
                            <td>{{ formataData($ip->updated_at) }}</td>
                            <td>
                            @if($ip->isLiberado())
                                Exclusão somente via SSH
                            @else
                                <form method="POST" action="{{ route('suporte.ips.excluir', $ip->ip) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-success" value="Liberar Acesso">
                                        Liberar Acesso
                                    </button>
                                </form>
                            @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> 
    @else
    <p>Sem registro de IPs.</p>
    @endif
</div>