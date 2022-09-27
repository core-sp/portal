<div class="card-body">
    <p class="mb-4">Obs: Para buscar uma informação no log use <kbd>Ctrl + F</kbd> para acionar o Localizar do navegador</p>
    
    <div class="row mb-4">
        <div class="col">
            @if(isset($info['externo']))
            <a class="btn btn-success" href="{{ route('suporte.log.externo.hoje.view', 'externo') }}" target="{{ isset($info['externo']) ? '_blank' : '_self' }}">
                Log do Site hoje
            </a>
            <p class="mt-1"><strong> Última atualização:</strong> {{ $info['externo'] }}</p>
            @else
            <p class="mt-1"><strong> Ainda não há log do Site do dia de hoje:</strong> {{ date('d/m/Y') }}</p>
            @endif
        </div>

        <div class="col">
            @if(isset($info['interno']))
            <a class="btn btn-primary" href="{{ route('suporte.log.externo.hoje.view', 'interno') }}" target="{{ isset($info['interno']) ? '_blank' : '_self' }}">
                Log do Admin hoje
            </a>
            <p class="mt-1"><strong> Última atualização:</strong> {{ $info['interno'] }}</p>
            @else
            <p class="mt-1"><strong> Ainda não há log do Admin do dia de hoje:</strong> {{ date('d/m/Y') }}</p>
            @endif
        </div>

        <div class="col">
            @if(isset($info['erros']))
            <a class="btn btn-danger" href="{{ route('suporte.log.externo.hoje.view', 'erros') }}" target="{{ isset($info['erros']) ? '_blank' : '_self' }}">
                Log de Erros hoje
            </a>
            <p class="mt-1"><strong> Última atualização:</strong> {{ $info['erros'] }}</p>
            @else
            <p class="mt-1"><strong> Ainda não há log de Erros do dia de hoje:</strong> {{ date('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    <hr />

    <!-- BUSCA POR DATA -->
    <div class="row mb-4">
        <div class="col">
            <fieldset class="border border-secondary p-3">
                <legend>Buscar por dia</legend>
                <form action="{{ route('suporte.log.externo.busca') }}">
                    @csrf
                    <div class="form-inline">
                        <label for="tipo" class="mr-sm-2">Tipo de log:</label>
                        <select name="tipo" class="form-control mb-2 mr-sm-3 {{ $errors->has('tipo') ? 'is-invalid' : '' }}">
                            <option value="externo" {{ old('tipo') == 'externo' ? 'selected' : '' }}>Site</option>
                            <option value="interno" {{ old('tipo') == 'interno' ? 'selected' : '' }}>Admin</option>
                            <option value="erros" {{ old('tipo') == 'erros' ? 'selected' : '' }}>Erros</option>
                        </select>
                        
                        <label for="buscar-data" class="mr-sm-2">Data:</label>
                        <input type="date" 
                            name="data" 
                            class="form-control mb-2 mr-sm-3 {{ $errors->has('data') ? 'is-invalid' : '' }}" 
                            id="buscar-data"
                            value="{{ empty(old('data')) ? date('Y-m-d', strtotime('yesterday')) : old('data') }}"
                            max="{{ date('Y-m-d', strtotime('yesterday')) }}"
                        >
                        
                        <button class="btn btn-secondary btn-sm mb-2 mr-sm-3" type="submit">Buscar</button>
                        @if($errors->has('data') || $errors->has('tipo'))
                        <div class="invalid-feedback">
                            {{ $errors->has('data') ? $errors->first('data') : $errors->first('tipo') }}
                        </div>
                        @endif
                    </div>
                </form>
            </fieldset>
        </div>
    </div>

    <!-- BUSCA POR MÊS -->
    <div class="row mb-4">
        <div class="col">
            <fieldset class="border border-secondary p-3">
                <legend>Buscar texto por mês</legend>
                <form action="{{ route('suporte.log.externo.busca') }}">
                    @csrf
                    <div class="form-inline">
                        <label for="tipo" class="mr-sm-2">Tipo de log:</label>
                        <select name="tipo" class="form-control mb-2 mr-sm-3 {{ $errors->has('tipo') ? 'is-invalid' : '' }}">
                            <option value="externo" {{ old('tipo') == 'externo' ? 'selected' : '' }}>Site</option>
                            <option value="interno" {{ old('tipo') == 'interno' ? 'selected' : '' }}>Admin</option>
                        </select>
                        
                        <label for="buscar-mes" class="mr-sm-2">Mês/Ano:</label>
                        <input type="month" 
                            name="mes" 
                            class="form-control mb-2 mr-sm-3 {{ $errors->has('mes') ? 'is-invalid' : '' }}" 
                            id="buscar-mes"
                            value="{{ empty(old('mes')) ? date('Y-m') : old('mes') }}"
                            max="{{ date('Y-m') }}"
                        >

                        <label for="buscar-texto" class="mr-sm-2">Texto:</label>
                        <input type="text" 
                            name="texto" 
                            class="form-control mb-2 mr-sm-3 {{ $errors->has('texto') ? 'is-invalid' : '' }}" 
                            id="buscar-texto"
                            value="{{ old('texto') }}"
                        >
                        
                        <button class="btn btn-secondary btn-sm mb-2 mr-sm-3" type="submit">Buscar</button>
                        @if($errors->has('mes') || $errors->has('tipo') || $errors->has('texto'))
                        <div class="invalid-feedback">
                            @if($errors->has('mes'))
                                {{ $errors->first('mes') }}
                            @elseif($errors->has('tipo'))
                                {{ $errors->first('tipo') }}
                            @else
                                {{ $errors->first('texto') }}
                            @endif
                        </div>
                        @endif
                    </div>
                </form>
            </fieldset>
        </div>
    </div>

    <!-- BUSCA POR ANO -->
    <div class="row mb-4">
        <div class="col">
            <fieldset class="border border-secondary p-3">
                <legend>Buscar texto por ano</legend>
                <form action="{{ route('suporte.log.externo.busca') }}">
                    @csrf
                    <div class="form-inline">
                        <label for="tipo" class="mr-sm-2">Tipo de log:</label>
                        <select name="tipo" class="form-control mb-2 mr-sm-3 {{ $errors->has('tipo') ? 'is-invalid' : '' }}">
                            <option value="externo" {{ old('tipo') == 'externo' ? 'selected' : '' }}>Site</option>
                            <option value="interno" {{ old('tipo') == 'interno' ? 'selected' : '' }}>Admin</option>
                        </select>
                        
                        <label for="buscar-ano" class="mr-sm-2">Mês/Ano:</label>
                        <input type="number" 
                            name="ano" 
                            class="form-control mb-2 mr-sm-3 {{ $errors->has('ano') ? 'is-invalid' : '' }}" 
                            id="buscar-ano"
                            value="{{ empty(old('ano')) ? date('Y') : old('ano') }}"
                            min="2019"
                            max="{{ date('Y') }}"
                            step="1"
                        >

                        <label for="buscar-texto" class="mr-sm-2">Texto:</label>
                        <input type="text" 
                            name="texto" 
                            class="form-control mb-2 mr-sm-3 {{ $errors->has('texto') ? 'is-invalid' : '' }}" 
                            id="buscar-texto"
                            value="{{ old('texto') }}"
                        >
                        
                        <button class="btn btn-secondary btn-sm mb-2 mr-sm-3" type="submit">Buscar</button>
                        @if($errors->has('ano') || $errors->has('tipo') || $errors->has('texto'))
                        <div class="invalid-feedback">
                            @if($errors->has('ano'))
                                {{ $errors->first('ano') }}
                            @elseif($errors->has('tipo'))
                                {{ $errors->first('tipo') }}
                            @else
                                {{ $errors->first('texto') }}
                            @endif
                        </div>
                        @endif
                    </div>
                </form>
            </fieldset>
        </div>
    </div>

    <!-- RESULTADO DA BUSCA -->
    @if(request()->query('tipo') != "")
    <hr />

    @php
        $tipos = ['erros' => 'de Erros', 'interno' => 'do Admin', 'externo' => 'do Site'];
        $textoTipo = $tipos[request()->query('tipo')];
    @endphp
    <h4 class="mt-4 mb-4">Resultado da busca "<i>{{ $busca }}</i>" para o log <strong>{{ $textoTipo }}</strong></h4>
    <div class="row">
        <div class="col">
        @if(isset($resultado))

            @if(isset(request()->query()['data']))
            <p><i class="fas fa-file-alt"></i> - Log <strong>{{ $textoTipo }}</strong> do dia {{ onlyDate($resultado) }}
                <a class="btn btn-info ml-3" href="{{ route('suporte.log.externo.view', ['data' => $resultado, 'tipo' => request()->query('tipo')]) }}" target="_blank">
                    Abrir Log
                </a>
            </p>
            @else
                @foreach($resultado as $file)
                <p><i class="fas fa-file-alt"></i> - Log <strong>{{ $textoTipo }}</strong> do dia {{ onlyDate($file) }}
                    <a class="btn btn-info ml-3" href="{{ route('suporte.log.externo.view', ['data' => $file, 'tipo' => request()->query('tipo')]) }}" target="_blank">
                        Abrir Log
                    </a>
                </p>
                @endforeach
                {{ $resultado->appends(request()->input())->links() }}
            @endif

        @else
            <p>Não foi encontrado log(s) <strong>{{ $textoTipo }}</strong> para a busca: <i>{{ $busca }}</i></p>
        @endif
        </div>
    </div>
    @endif

</div>