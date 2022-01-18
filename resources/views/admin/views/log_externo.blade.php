<div class="card-body">
    <p class="mb-4">OBS: Para buscar uma informação no log, use Ctrl + F para acionar o Localizar do navegador</p>
    <div class="row">
        <div class="col">
            <a class="btn btn-success" href="{{ route('suporte.log.externo.hoje.view') }}" target="{{ isset($info) ? '_blank' : '_self' }}">
                Log de hoje
            </a>
            @if(isset($info))
            <p class="mt-1"><strong> Última atualização:</strong> {{ $info }}</p>
            @endif
        </div>
        <div class="col">
            <form action="{{ route('suporte.log.externo.busca') }}" method="GET">
                <h5>Buscar por data um log</h5>
                <input type="date" name="data">
                <button class="btn btn-primary btn-sm" type="submit">Buscar</button>
            </form>
        </div>
        <div class="col">
            <form action="{{ route('suporte.log.externo.busca') }}" method="GET">
                <h5>Buscar por texto nos últimos 3 logs</h5>
                <input type="text" name="texto">
                <button class="btn btn-primary btn-sm" type="submit">Buscar</button>
            </form>
        </div>
    </div>
    @if(isset($resultado))
    <div class="row">
        <h3>Resultado da busca por {{ $tipo }}</h3>
        <div class="col">
            <p>Log Encontrado!!!!</p>
            <a class="btn btn-success" href="{{ route('suporte.log.externo.view') }}" target="_blank">
                {{-- $resultado['resultado'] --}}
            </a>
        </div>
    </div>
    @endif
</div>