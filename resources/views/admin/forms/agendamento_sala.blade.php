<form role="form" method="POST" id="criarAgendaSala" action="{{ route('sala.reuniao.agendados.store') }}">
    @csrf    
    <div class="card-body">
        <h5 class="text-primary">Formulário para registrar o uso da sala pelo representante quando atendido presencialmente</h5>
        <p><strong>Status: <span class="text-success">Presença Confirmada</span></strong></p>

    @foreach(['registro_core', 'nome', 'email', 'participantes_cpf.*', 'participantes_cpf', 'participantes_nome.*', 'participantes_nome'] as $error)
        @if($errors->has($error))
        <p class="alert alert-danger">
            <i class="fas fa-times"></i>&nbsp;&nbsp;Erro encontrado em 
            {{ !in_array($error, ['registro_core', 'nome', 'email']) ? 'participantes da reunião' : 'dados do Gerenti' }}:&nbsp;&nbsp;
            {{ $errors->first($error) }}
            @break
        </p>
        @endif
    @endforeach

        <div class="form-row mt-2">
            <div class="col">
                <label for="cpf_cnpj">CPF / CNPJ <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
                    placeholder="CPF / CNPJ"
                    name="cpf_cnpj"
                    value="{{ !empty(old('cpf_cnpj')) ? apenasNumeros(old('cpf_cnpj')) : '' }}"
                    required
                />
                @if($errors->has('cpf_cnpj'))
                <div class="invalid-feedback">
                {{ $errors->first('cpf_cnpj') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="sala_reuniao_id">Sala de Reunião / Coworking <span class="text-danger">*</span></label>
                <select 
                    name="sala_reuniao_id" 
                    class="form-control {{ $errors->has('sala_reuniao_id') ? 'is-invalid' : '' }}"
                    required
                >
                    <option value="">Selecione uma sala...</option>
                    @foreach($salas as $sala)
                    <option value="{{ $sala->id }}">{{ $sala->regional->regional }}</option>
                    @endforeach
                </select>
                @if($errors->has('sala_reuniao_id'))
                <div class="invalid-feedback">
                    {{ $errors->first('sala_reuniao_id') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="tipo_sala">Tipo de sala <span class="text-danger">*</span></label>
                <select 
                    name="tipo_sala" 
                    class="form-control {{ $errors->has('tipo_sala') ? 'is-invalid' : '' }}"
                    required
                >
                    <option value="">Selecione tipo de sala...</option>
                    @foreach(['reuniao' => 'Reunião', 'coworking' => 'Coworking'] as $chave => $tipo)
                    <option value="{{ $chave }}">{{ $tipo }}</option>
                    @endforeach
                </select>
                @if($errors->has('tipo_sala'))
                <div class="invalid-feedback">
                    {{ $errors->first('tipo_sala') }}
                </div>
                @endif
            </div>
        </div>

        <fieldset class="form-group border p-2 mt-2" id="area_gerenti" style="display: none;">
            <legend class="w-auto">
                <small><i class="fas fa-info-circle text-danger"></i> Informações do Gerenti</small>
            </legend>
            <div class="col">
                <span><strong>Nome: </strong><span id="nomeGerenti"></span></span>&nbsp;&nbsp;|&nbsp;&nbsp;
                <span><strong>Registro: </strong><span id="registroGerenti"></span></span>&nbsp;&nbsp;|&nbsp;&nbsp;
                <span><strong>E-mail: </strong><span id="emailGerenti"></span></span>&nbsp;&nbsp;|&nbsp;&nbsp;
                <span><strong>Situação: </strong><span id="situacaoGerenti"></span></span>
            </div>
        </fieldset>

        <fieldset class="form-group border p-2 mt-2" id="area_participantes">
            <p class="text-secondary">
                <span class="text-danger">*</span> <em>Deve ter pelo menos um participante além do responsável</em>
            </p>
            <legend class="w-auto">
                <small><i class="fas fa-users text-info"></i> Participantes</small>
            </legend>

            <div class="form-row mt-2 participanteResponsavel">
                <div class="col-sm mb-2-576">

                    <div class="input-group mb-2-576">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Participante Responsável:</span>
                        </div>
                        <input 
                            type="text" 
                            class="form-control col-3 cpfInput"
                            id="cpfResponsavel"
                            disabled
                        />
                        <input 
                            type="text" 
                            class="form-control text-uppercase"
                            id="nomeResponsavel"
                            disabled
                        />
                    </div>

                </div>
            </div>
            <div class="form-row mt-2 participante">
                <div class="col-sm mb-2-576">

                    <div class="input-group mb-2-576">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Participante:</span>
                        </div>
                        <input 
                            type="text" 
                            class="form-control col-3 cpfInput" 
                            name="participantes_cpf[]" 
                            placeholder="CPF"
                        />
                        <input 
                            type="text" 
                            class="form-control text-uppercase" 
                            name="participantes_nome[]" 
                            placeholder="Nome Completo"
                        />
                    </div>

                </div>
            </div>
        </fieldset>

        <div class="form-row mt-2">
            <div class="col">
                <label for="dia">Dia <span class="text-danger">*</span></label>
                <input type="date"
                    class="form-control {{ $errors->has('dia') ? 'is-invalid' : '' }}"
                    name="dia"
                    max="{{ now()->format('Y-m-d') }}"
                    value="{{ now()->format('Y-m-d') }}"
                    required
                />
                @if($errors->has('dia'))
                <div class="invalid-feedback">
                {{ $errors->first('dia') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="periodo_entrada">Hora de entrada <span class="text-danger">*</span></label>
                <select 
                    name="periodo_entrada" 
                    class="form-control {{ $errors->has('periodo_entrada') ? 'is-invalid' : '' }}"
                    required
                >
                @foreach(todasHoras() as $hora)
                    <option value="{{ $hora }}" {{ old('periodo_entrada') == $hora ? 'selected' : '' }}>{{ $hora }}</option>
                @endforeach
                </select>
                @if($errors->has('periodo_entrada'))
                <div class="invalid-feedback">
                    {{ $errors->first('periodo_entrada') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="periodo_saida">Hora de saída <span class="text-danger">*</span></label>
                <select 
                    name="periodo_saida" 
                    class="form-control {{ $errors->has('periodo_saida') ? 'is-invalid' : '' }}"
                    required
                >
                @foreach(array_merge(todasHoras(), ['18:00']) as $hora)
                    @if($hora > '09:00')
                    <option value="{{ $hora }}" {{ old('periodo_saida') == $hora ? 'selected' : '' }}>{{ $hora }}</option>
                    @endif
                @endforeach
                </select>
                @if($errors->has('periodo_saida'))
                <div class="invalid-feedback">
                    {{ $errors->first('periodo_saida') }}
                </div>
                @endif
            </div>
        </div>
    </div>
        
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('sala.reuniao.agendados.index') }}" class="btn btn-default">Voltar</a>
            <button type="button" class="btn btn-primary ml-1" id="verificaSuspensos">
                Salvar
            </button>
        </div>
    </div>
</form>

<script type="module" src="{{ asset('/js/interno/modulos/sala-reuniao-agendados.js?'.hashScriptJs()) }}" data-modulo-id="sala-reuniao-agendados" data-modulo-acao="criar"></script>