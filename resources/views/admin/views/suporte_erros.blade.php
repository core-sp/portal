<div class="card-body">
    {{--
    <p class="mb-4">Obs: Para buscar uma informação no log use Ctrl + F para acionar o Localizar do navegador</p>
    <div class="row mb-4">
        <div class="col-3">
            @if(isset($info))
            <a class="btn btn-success" href="{{ route('suporte.log.externo.hoje.view') }}" target="{{ isset($info) ? '_blank' : '_self' }}">
                Log de hoje
            </a>
            <p class="mt-1"><strong> Última atualização:</strong> {{ $info }}</p>
            @else
            <p class="mt-1"><strong> Ainda não há log do dia de hoje:</strong> {{ date('d/m/Y') }}</p>
            @endif
        </div>
        <div class="col-3">
            <form action="{{ route('suporte.log.externo.busca') }}">
                <div class="form-group">
                    <label for="buscar-data">Buscar por data:</label>
                    <input type="date" 
                        name="data" 
                        class="form-control {{ $errors->has('data') ? 'is-invalid' : '' }}" 
                        id="buscar-data"
                        value="{{ old('data') }}"
                    >
                    @if($errors->has('data'))
                    <div class="invalid-feedback">
                        {{ $errors->first('data') }}
                    </div>
                    @endif
                    <button class="btn btn-primary btn-sm mt-2" type="submit">Buscar</button>
                </div>
            </form>
        </div>
        <div class="col">
            <form action="{{ route('suporte.log.externo.busca') }}">
                <div class="form-group">    
                    <label for="buscar-texto">Buscar por texto nos últimos 3 logs:</label>
                    <input type="text" 
                        name="texto" 
                        class="form-control {{ $errors->has('texto') ? 'is-invalid' : '' }}" 
                        id="buscar-texto"
                        value="{{ old('texto') }}"
                    >
                    @if($errors->has('texto'))
                    <div class="invalid-feedback">
                        {{ $errors->first('texto') }}
                    </div>
                    @endif
                    <button class="btn btn-primary btn-sm mt-2" type="submit">Buscar</button>
                </div>
            </form>
        </div>
    </div>
    @if(isset($tipo))
    <hr>
    <h4 class="mt-4 mb-4">Resultado da busca por {{ $tipo }}</h4>
    <div class="row">
        <div class="col">
        @if(isset($resultado))
            @if($tipo == 'data')
            <p>
                <strong>
                    Log do dia {{ onlyDate($resultado) }}
                </strong>
                <a class="btn btn-success ml-3" href="{{ route('suporte.log.externo.view', $resultado) }}" target="_blank">
                    Abrir Log
                </a>
            </p>
            @elseif($tipo == 'texto')
                @php
                    $i = 0;
                @endphp
                @foreach($resultado as $key => $value)
                    @if(!isset($value))
                    <p><strong>Não há log para o dia <i>{{ onlyDate($key) }}</i></strong></p>
                    @elseif(empty($value))
                    <p><strong>Não foi encontrado o texto: <i>{{ $busca }}</i> no log do dia <i>{{ onlyDate($key) }}</i></p>
                    @else
                    @php
                        $i++;
                    @endphp
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                <a data-toggle="collapse" href="{{ '#collapse'.$i }}"><i class="fas fa-angle-down"></i>&nbsp;&nbsp;
                                    <strong>
                                        {{ count($value) == 1 ? 'Foi encontrada' : 'Foram encontradas' }} <i>{{ count($value) }}</i> {{ count($value) == 1 ? 'linha' : 'linhas' }} com o texto: <i>{{ $busca }}</i> no log do dia <i>{{ onlyDate($key) }}</i>
                                    </strong>
                                    <a class="btn btn-success ml-4" href="{{ route('suporte.log.externo.view', $key) }}" target="_blank">
                                        Abrir Log
                                    </a>
                                </a>
                            </div>
                            <div id="{{ 'collapse'.$i }}" class="collapse" data-parent="#accordion">
                                <div class="card-body bg-light">
                                    <div class="table-responsive-lg">
                                        <table class="table table-hover mb-0">
                                            <tbody>
                                        @if(!empty($value))
                                            @foreach($value as $val) 
                                                <tr>
                                                    <td>{{ $val }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            @endif
        @else
            <p><strong>Não foi encontrado log {{ $tipo == 'data' ? 'do dia: ' : 'para o texto: ' }}<i>{{ $busca }}</i></strong></p>
        @endif
        </div>
    </div>
    @endif
    --}}
</div>