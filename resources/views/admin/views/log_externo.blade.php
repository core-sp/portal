<div class="card-body">
    <p><strong>Obs:</strong> Para buscar uma informação no log use <kbd>Ctrl + F</kbd> para acionar o Localizar do navegador</p>
    <p><i class="fas fa-info-circle text-primary"></i> Para cancelar a busca, tecle <kbd>ESC</kbd></p>
    <p><span class="text-danger"><strong>Atenção!</strong></span> ao optar para buscar o total de ocorrências por log, considere que requer mais tempo de processamento</p>
    <hr />

    <div class="row mb-4 mt-4">
        <div class="col">
            @if(isset($info['externo']))
            <a class="btn btn-success" href="{{ route('suporte.log.externo.hoje.view', 'externo') }}" target="{{ isset($info['externo']) ? '_blank' : '_self' }}">
                Log do Site hoje
            </a>
            <a class="btn btn-warning ml-2" href="{{ route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'externo']) }}">
                <i class="fas fa-download"></i>
            </a>
            <br>
            {{ $size['externo'] }}
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
            <a class="btn btn-warning ml-2" href="{{ route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'interno']) }}">
                <i class="fas fa-download"></i>
            </a>
            <br>
            {{ $size['interno'] }}
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
            <a class="btn btn-warning ml-2" href="{{ route('suporte.log.externo.download', ['data' => date('Y-m-d'), 'tipo' => 'erros']) }}">
                <i class="fas fa-download"></i>
            </a>
            <br>
            {{ $size['erros'] }}
            <p class="mt-1"><strong> Última atualização:</strong> {{ $info['erros'] }}</p>
            @else
            <p class="mt-1"><strong> Ainda não há log de Erros do dia de hoje:</strong> {{ date('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    <hr />

    @php
        $relatorios = $suporte->todosRelatorios();
        $cont = 1;
    @endphp

    <!-- RELATÓRIOS -->
    <div class="row mb-4">
        <div class="col">
            <fieldset class="border border-secondary p-3">
                <legend>Relatórios</legend>

                @if(session()->exists('relat_removido'))
                <div class="toast bg-warning">
                    <div class="toast-body text-danger text-center">
                        <strong>Relatório removido!</strong>
                    </div>
                </div>
                @endif

                @if(!empty($relatorios))
                <p><strong>Relatórios salvos temporariamente:</strong></p>
                @foreach($relatorios as $relat => $r)
                <span class="text-nowrap">{{ $relat != 'relatorio_final' ? $cont++ : 'Final' }}: 
                    <a href="{{ route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']) }}">{{ $suporte->getTituloPorNome($relat) }}</a>
                    <a class="btn btn-link btn-sm" href="{{ route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']) }}"><i class="fas fa-download"></i></a>
                    <a class="btn btn-link btn-sm" href="{{ route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'remover']) }}"><i class="fas fa-times text-danger"></i></a>
                </span>
                <br>
                @endforeach
                <a class="btn btn-success btn-sm mt-3" href="{{ route('suporte.log.externo.relatorios.final') }}">Gerar relatório final</a>
                <hr>
                @endif

                <form action="{{ route('suporte.log.externo.relatorios') }}">
                    <div class="form-row mb-2">
                        <div class="col">
                            <label for="relat_opcoes" class="mr-sm-2">Filtros:</label>
                            <select name="relat_opcoes" class="form-control {{ $errors->has('relat_opcoes') ? 'is-invalid' : '' }}" id="relat_opcoes">
                            @foreach($filtros as $tipoOpcao => $opcao)
                                <option value="{{ $tipoOpcao }}" {{ old('relat_opcoes') == $tipoOpcao ? 'selected' : '' }}>{{ $opcao }}</option>
                            @endforeach
                            </select>
                            @if($errors->has('relat_opcoes'))
                            <div class="invalid-feedback">
                                {{ $errors->first('relat_opcoes') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-row d-flex align-items-end">
                        <div class="col">
                            <label for="relat_tipo" class="mr-sm-2">Tipo:</label>
                            <select name="relat_tipo" class="form-control {{ $errors->has('relat_tipo') ? 'is-invalid' : '' }}" id="relat_tipo">
                            @foreach($tipos as $k => $val)
                                @if($val != 'Erros')
                                <option value="{{ $k }}" {{ old('relat_tipo') == $k ? 'selected' : '' }}>{{ $val }}</option>
                                @endif
                            @endforeach
                            </select>
                            @if($errors->has('relat_tipo'))
                            <div class="invalid-feedback">
                                {{ $errors->first('relat_tipo') }}
                            </div>
                            @endif
                        </div>

                        <div class="col">
                            <div class="input-group" id="relat-buscar-mes">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <input type="radio" class="mr-1" name="relat_data" value="mes" checked><strong>Mês/Ano:</strong>
                                    </div>
                                </div>
                                <input type="month" 
                                    name="relat_mes" 
                                    class="form-control {{ $errors->has('relat_mes') || $errors->has('relat_data') ? 'is-invalid' : '' }}" 
                                    value="{{ date('Y-m') }}"
                                    min="2019-01"
                                    max="{{ date('Y-m') }}"
                                />
                                @if($errors->has('relat_mes') || $errors->has('relat_data'))
                                <div class="invalid-feedback">
                                    {{ $errors->has('relat_data') ? $errors->first('relat_data') : $errors->first('relat_mes') }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col">
                            <div class="input-group" id="relat-buscar-ano">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <input type="radio" class="mr-1" name="relat_data" value="ano"><strong>Ano:</strong>
                                    </div>
                                </div>
                                <input type="number" 
                                    name="relat_ano" 
                                    class="form-control {{ $errors->has('relat_ano') || $errors->has('relat_data') ? 'is-invalid' : '' }}" 
                                    value="{{ date('Y') }}"
                                    min="2019"
                                    max="{{ date('Y') }}"
                                    step="1"
                                    disabled
                                />
                                @if($errors->has('relat_ano') || $errors->has('relat_data'))
                                <div class="invalid-feedback">
                                    {{ $errors->has('relat_data') ? $errors->first('relat_data') : $errors->first('relat_ano') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-secondary btn-sm mt-2 float-right" type="submit" data-toggle="modal" data-target="#modalSuporte" data-backdrop="static">Gerar</button>
                </form>
            </fieldset>
        </div>
    </div>

    <!-- BUSCA POR DATA -->
    <div class="row mb-4">
        <div class="col">
            <fieldset class="border border-secondary p-3">
                <legend>Buscar por dia</legend>
                <form action="{{ route('suporte.log.externo.busca') }}">
                    <div class="form-inline">
                        <label for="tipo" class="mr-sm-2">Tipo de log:</label>
                        <select name="tipo" class="form-control mb-2 mr-sm-3 {{ isset(request()->query()['data']) && $errors->has('tipo') ? 'is-invalid' : '' }}">
                            @foreach($tipos as $k => $val)
                            <option value="{{ $k }}" {{ (isset(request()->query()['data']) && isset(request()->query()['tipo']) && (request()->query()['tipo'] == $k)) || (old('tipo') == $k) ? 'selected' : '' }}>{{ $val }}</option>
                            @endforeach
                        </select>
                        @if(isset(request()->query()['data']) && $errors->has('tipo'))
                        <div class="invalid-feedback">
                            {{ $errors->first('tipo') }}
                        </div>
                        @endif
                        
                        <label for="buscar-data" class="mr-sm-2">Data:</label>
                        <input type="date" 
                            name="data" 
                            class="form-control mb-2 mr-sm-3 {{ isset(request()->query()['data']) && $errors->has('data') ? 'is-invalid' : '' }}" 
                            id="buscar-data"
                            value="{{ isset(request()->query()['data']) ? request()->query()['data'] : now()->yesterday()->format('Y-m-d') }}"
                            min="2019-01-01"
                            max="{{ now()->yesterday()->format('Y-m-d') }}"
                        >
                        @if($errors->has('data'))
                        <div class="invalid-feedback">
                            {{ $errors->first('data') }}
                        </div>
                        @endif
                        
                        <button class="btn btn-secondary btn-sm mb-2 mr-sm-3" type="submit" data-toggle="modal" data-target="#modalSuporte" data-backdrop="static">Buscar</button>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>

    <!-- BUSCA POR MÊS -->
    <div class="row mb-4">
        <div class="col">
            <fieldset class="border border-secondary p-3">
                <legend>Buscar texto por mês / ano</legend>
                <form action="{{ route('suporte.log.externo.busca') }}">
                    <div class="form-inline mb-3">
                        <div class="input-group mr-3" id="buscar-mes">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="radio" class="mr-1" name="optradio" value="mes" {{ isset(request()->query()['mes']) || (!isset(request()->query()['mes']) && !isset(request()->query()['ano'])) ? 'checked' : '' }}><strong>Mês/Ano:</strong>
                                </div>
                            </div>
                            <input type="month" 
                                name="mes" 
                                class="form-control {{ $errors->has('mes') ? 'is-invalid' : '' }}" 
                                value="{{ isset(request()->query()['mes']) ? request()->query()['mes'] : date('Y-m') }}"
                                min="2019-01"
                                max="{{ date('Y-m') }}"
                                {{ isset(request()->query()['mes']) || (!isset(request()->query()['mes']) && !isset(request()->query()['ano'])) ? '' : 'disabled' }}
                            />
                            @if($errors->has('mes'))
                            <div class="invalid-feedback">
                                {{ $errors->first('mes') }}
                            </div>
                            @endif
                        </div>

                        <div class="input-group" id="buscar-ano">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="radio" class="mr-1" name="optradio" value="ano" {{ isset(request()->query()['ano']) ? 'checked' : '' }}><strong>Ano:</strong>
                                </div>
                            </div>
                            <input type="number" 
                                name="ano" 
                                class="form-control {{ $errors->has('ano') ? 'is-invalid' : '' }}" 
                                value="{{ isset(request()->query()['ano']) ? request()->query()['ano'] : date('Y') }}"
                                min="2019"
                                max="{{ date('Y') }}"
                                step="1"
                                {{ isset(request()->query()['mes']) || (!isset(request()->query()['mes']) && !isset(request()->query()['ano'])) ? 'disabled' : '' }}
                            />
                            @if($errors->has('ano'))
                            <div class="invalid-feedback">
                                {{ $errors->first('ano') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-inline">
                        <label for="tipo" class="mr-sm-2">Tipo de log:</label>
                        <select name="tipo" class="form-control mb-2 mr-sm-2 {{ !isset(request()->query()['data']) && $errors->has('tipo') ? 'is-invalid' : '' }}">
                        @foreach($tipos as $k => $val)
                            @if($val != 'Erros')
                            <option value="{{ $k }}" {{ (!isset(request()->query()['data']) && isset(request()->query()['tipo']) && (request()->query()['tipo'] == $k)) || (old('tipo') == $k) ? 'selected' : '' }}>{{ $val }}</option>
                            @endif
                        @endforeach
                        </select>
                        @if(!isset(request()->query()['data']) && $errors->has('tipo'))
                        <div class="invalid-feedback mr-sm-2">
                            {{ $errors->first('tipo') }}
                        </div>
                        @endif

                        <label for="buscar-texto" class="mr-sm-2">Texto:</label>
                        <input type="text" 
                            name="texto" 
                            class="form-control mb-2 mr-sm-2 {{ $errors->has('texto') ? 'is-invalid' : '' }}" 
                            id="buscar-texto"
                            value="{{ isset(request()->query()['texto']) ? request()->query()['texto'] : old('texto') }}"
                            size="50"
                        />
                        @if($errors->has('texto'))
                        <div class="invalid-feedback">
                            {{ $errors->first('texto') }}
                        </div>
                        @endif

                        <div class="form-check-inline">
                            <label class="form-check-label">
                                <input type="checkbox" 
                                    name="n_linhas"
                                    class="form-check-input {{ $errors->has('n_linhas') ? 'is-invalid' : '' }}"
                                    id="n_linhas"
                                    {{ isset(request()->query()['n_linhas']) ? 'checked' : '' }}
                                /> <strong>total de ocorrências por log</strong>
                                @if($errors->has('n_linhas'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('n_linhas') }}
                                </div>
                                @endif
                            </label>
                        </div>

                        <div class="form-check-inline">
                            <label class="form-check-label">
                                <input type="checkbox" 
                                    name="distintos"
                                    class="form-check-input {{ $errors->has('distintos') ? 'is-invalid' : '' }}"
                                    id="distintos"
                                    {{ isset(request()->query()['distintos']) ? 'checked' : '' }}
                                /> <strong>somente registros distintos</strong>
                                @if($errors->has('distintos'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('distintos') }}
                                </div>
                                @endif
                            </label>
                        </div>

                        <button class="btn btn-secondary btn-sm mb-2 mr-sm-3" type="submit" data-toggle="modal" data-target="#modalSuporte" data-backdrop="static">Buscar</button>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>

    <!-- RESULTADO DA BUSCA -->
    @if(request()->query('tipo') != "")
    <hr />

    @php
        $textoTipo = $tipos_textos[request()->query('tipo')];
    @endphp
    <div class="mt-4 mb-4">
        <h4>Resultado da busca "<i>{{ $busca }}</i>" para o log <strong>{{ $textoTipo }}</strong></h4>
        @if(!isset(request()->query()['data']) && (isset(request()->query()['n_linhas']) || isset(request()->query()['distintos'])))
        <h5>Total de ocorrências{{ isset(request()->query()['distintos']) ? ' distintas' : '' }}: {{ number_format($totalFinal, 0, ",", ".") }}</h5>
        @endif
    </div>
    

    <div class="row">
        <div class="col">
        @if(isset($resultado))

            @if(isset(request()->query()['data']))
            @php
                $all = explode(';', $resultado);
            @endphp
            <p><i class="fas fa-file-alt"></i> - Log <strong>{{ $textoTipo }}</strong> do dia {{ onlyDate($all[0]) }} - {{ $all[1] }}
                <a class="btn btn-info ml-3" href="{{ route('suporte.log.externo.view', ['data' => $all[0], 'tipo' => request()->query('tipo')]) }}" target="_blank">
                    Abrir
                </a>
                <a class="btn btn-warning ml-3" href="{{ route('suporte.log.externo.download', ['data' => $all[0], 'tipo' => request()->query('tipo')]) }}">
                    <i class="fas fa-download"></i>
                </a>
                <a class="btn btn-primary btn-sm ml-3" href="{{ route('suporte.log.externo.integridade', ['data' => $all[0], 'tipo' => request()->query('tipo')]) }}">
                    Verificar integridade
                </a>
            </p>
            @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome do Log</th>
                            <th>Tamanho em KB</th>
                            <th>Total de ocorrências</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($resultado as $file)
                    @php
                        $all = explode(';', $file);
                    @endphp
                        <tr>
                            <td><i class="fas fa-file-alt"></i> - Log <strong>{{ $textoTipo }}</strong> do dia {{ onlyDate($all[0]) }}</td>
                            <td>{{ $all[1] }}</td>
                            <td>{{ isset($all[2]) ? number_format($all[2], 0, ",", ".") : '-----' }}</td>
                            <td>
                                <a class="btn btn-info" href="{{ route('suporte.log.externo.view', ['data' => $all[0], 'tipo' => request()->query('tipo')]) }}" target="_blank">
                                    Abrir
                                </a>
                                <a class="btn btn-warning ml-3" href="{{ route('suporte.log.externo.download', ['data' => $all[0], 'tipo' => request()->query('tipo')]) }}">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a class="btn btn-primary btn-sm ml-3" href="{{ route('suporte.log.externo.integridade', ['data' => $all[0], 'tipo' => request()->query('tipo')]) }}">
                                    Verificar integridade
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-start">
                <em>{{ $resultado->total() > 1 ? 'Foram encontrados ' . $resultado->total() . ' logs' : 'Foi encontrado 1 log' }}</em>
            </div>
            <br>
            <div class="d-flex justify-content-start" data-toggle="modal" data-target="#modalSuporte" data-backdrop="static">
                {{ $resultado->appends(request()->input())->links() }}
            </div>
            @endif

        @else
            <p>A busca não retornou log(s) <strong>{{ $textoTipo }}</strong></p>
        @endif
        </div>
    </div>
    @endif

    <!-- The Modal -->
    <div class="modal" id="modalSuporte">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

            <!-- Modal body -->
            <div class="modal-body d-flex justify-content-center">
                <div class="spinner-border text-primary"></div>&nbsp;&nbsp;Aguarde...
            </div>

            </div>
        </div>
    </div>

</div>

<script type="module" src="{{ asset('/js/interno/modulos/suporte.js?'.hashScriptJs()) }}" id="modulo-suporte" class="modulo-visualizar"></script>