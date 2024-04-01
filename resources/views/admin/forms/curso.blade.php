<form role="form" method="POST" action="{{ !isset($resultado) ? route('cursos.store') : route('cursos.update', $resultado->idcurso) }}">
    @csrf
    @if(isset($resultado))
        @method('PATCH')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col-sm-3">
                <label for="tipo">Tipo</label>
                <select name="tipo" class="form-control {{ $errors->has('tipo') ? 'is-invalid' : '' }}" required>
                @foreach($tipos as $tipo)
                    @if(old('tipo'))
                    <option value="{{ $tipo }}" {{ old('tipo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @else
                    <option value="{{ $tipo }}" {{ isset($resultado->tipo) && ($resultado->tipo == $tipo) ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endif
                @endforeach
                </select>
                @if($errors->has('tipo'))
                <div class="invalid-feedback">
                {{ $errors->first('tipo') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="tema">Tema</label>
                <input type="text"
                    class="form-control {{ $errors->has('tema') ? 'is-invalid' : '' }}" 
                    placeholder="Tema" 
                    name="tema"
                    maxlength="191"
                    value="{{ isset($resultado->tema) ? $resultado->tema : old('tema') }}"
                    required
                />
                @if($errors->has('tema'))
                <div class="invalid-feedback">
                {{ $errors->first('tema') }}
                </div>
                @endif
            </div>
            <div class="col-2">
                <label for="nrvagas">Nº de vagas</label>
                <input type="text"
                    name="nrvagas"
                    class="form-control vagasInput {{ $errors->has('nrvagas') ? 'is-invalid' : '' }}"
                    placeholder="00"
                    value="{{ isset($resultado->nrvagas) ? $resultado->nrvagas : old('nrvagas') }}"
                    required
                />
                @if($errors->has('nrvagas'))
                <div class="invalid-feedback">
                {{ $errors->first('nrvagas') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mt-2">
            <div class="col">
                <label for="conferencista">Conferencista</label>
                <input type="text"
                    class="form-control {{ $errors->has('conferencista') ? 'is-invalid' : '' }}" 
                    placeholder="Nome do conferencista" 
                    name="conferencista"
                    maxlength="191"
                    value="{{ isset($resultado->conferencista) ? $resultado->conferencista : old('conferencista') }}"
                    required
                />
                @if($errors->has('conferencista'))
                <div class="invalid-feedback">
                {{ $errors->first('conferencista') }}
                </div>
                @endif
            </div>
            <div class="col-2">
                <label for="carga_horaria">Carga Horária</label>
                <input type="text" 
                    class="form-control horaInput {{ $errors->has('carga_horaria') ? 'is-invalid' : '' }}" 
                    name="carga_horaria" 
                    id="carga_horaria"
                    @if(empty(old('carga_horaria')) && !isset($resultado->carga_horaria))
                    value="00:00"
                    @else
                    value="{{ isset($resultado->carga_horaria) ? $resultado->carga_horaria : old('carga_horaria') }}"
                    @endif
                    required
                />
                @if($errors->has('carga_horaria'))
                <div class="invalid-feedback">
                {{ $errors->first('carga_horaria') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mt-2">
            <div class="col-sm-3">
                <label for="idregional">Regional</label>
                <select name="idregional" class="form-control {{ $errors->has('idregional') ? 'is-invalid' : '' }}" id="idregional" required>
                @foreach($regionais as $regional)
                    @if(old('idregional'))
                    <option value="{{ $regional->idregional }}" {{ old('idregional') == $regional->idregional ? 'selected' : '' }}>{{ $regional->regional }}</option>
                    @else
                    <option value="{{ $regional->idregional }}" {{ isset($resultado->idregional) && ($resultado->idregional == $regional->idregional) ? 'selected' : '' }}>{{ $regional->regional }}</option>
                    @endif
                @endforeach
                </select>
            </div>
            <div class="col">
                <label for="endereco">Endereço</label>
                <input type="text"
                    name="endereco"
                    class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}"
                    maxlength="191"
                    placeholder="Endereço"
                    value="{{ isset($resultado->endereco) ? $resultado->endereco : old('endereco') }}"
                />
                @if($errors->has('endereco'))
                <div class="invalid-feedback">
                {{ $errors->first('endereco') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-sm-3">
                <label for="add_campo">Adicionar campo para inscrição?</label>
                <select name="add_campo" class="form-control {{ $errors->has('add_campo') ? 'is-invalid' : '' }}" required>
                    <option value="1" {{ (old('add_campo') == '1') || (isset($resultado) && ($resultado->add_campo == '1')) ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ (old('add_campo') == '0') || (isset($resultado) && ($resultado->add_campo == '0')) ? 'selected' : '' }}>Não</option>
                </select>
                @if($errors->has('add_campo'))
                <div class="invalid-feedback">
                {{ $errors->first('add_campo') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="campo_rotulo">Tipo de campo</label>
                <select name="campo_rotulo" class="form-control {{ $errors->has('campo_rotulo') ? 'is-invalid' : '' }}">
                    <option value="">Selecione a validação do campo...</option>
                @foreach($rotulos as $chave => $rotulo)
                    @if(old('campo_rotulo'))
                    <option value="{{ $chave }}" {{ old('campo_rotulo') == $chave ? 'selected' : '' }}>{{ $rotulo }}</option>
                    @else
                    <option value="{{ $chave }}" {{ isset($resultado->campo_rotulo) && ($resultado->campo_rotulo == $chave) ? 'selected' : '' }}>{{ $rotulo }}</option>
                    @endif
                @endforeach
                </select>
                @if($errors->has('campo_rotulo'))
                <div class="invalid-feedback">
                {{ $errors->first('campo_rotulo') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="campo_required">Campo na inscrição é obrigatório?</label>
                <select name="campo_required" class="form-control {{ $errors->has('campo_required') ? 'is-invalid' : '' }}" required>
                    <option value="1" {{ (old('campo_required') == '1') || (isset($resultado) && ($resultado->campo_required == '1')) ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ (old('campo_required') == '0') || (isset($resultado) && ($resultado->campo_required == '0')) ? 'selected' : '' }}>Não</option>
                </select>
                @if($errors->has('campo_required'))
                <div class="invalid-feedback">
                {{ $errors->first('campo_required') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="datarealizacao">Dia e Hora de Realização</label>
                <input type="datetime-local" 
                    class="form-control {{ $errors->has('datarealizacao') ? 'is-invalid' : '' }}" 
                    name="datarealizacao" 
                    id="datarealizacao"
                    value="{{ isset($resultado->datarealizacao) ? $resultado->datarealizacao : old('datarealizacao') }}"
                    required
                />
                @if($errors->has('datarealizacao'))
                <div class="invalid-feedback">
                {{ $errors->first('datarealizacao') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="datatermino">Dia e Hora de Término</label>
                <input type="datetime-local" 
                    class="form-control {{ $errors->has('datatermino') ? 'is-invalid' : '' }}" 
                    name="datatermino"
                    id="dataTermino"
                    value="{{ isset($resultado->datatermino) ? $resultado->datatermino : old('datatermino') }}"
                    required
                />
                @if($errors->has('datatermino'))
                <div class="invalid-feedback">
                {{ $errors->first('datatermino') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="inicio_inscricao">Dia e Hora de Início das Inscrições</label>
                <input type="datetime-local" 
                    class="form-control {{ $errors->has('inicio_inscricao') ? 'is-invalid' : '' }}" 
                    name="inicio_inscricao" 
                    id="inicio_inscricao"
                    value="{{ isset($resultado->inicio_inscricao) ? $resultado->inicio_inscricao : old('inicio_inscricao') }}"
                />
                @if($errors->has('inicio_inscricao'))
                <div class="invalid-feedback">
                {{ $errors->first('inicio_inscricao') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="termino_inscricao">Dia e Hora de Término das Inscrições</label>
                <input type="datetime-local" 
                    class="form-control {{ $errors->has('termino_inscricao') ? 'is-invalid' : '' }}" 
                    name="termino_inscricao"
                    id="termino_inscricao"
                    value="{{ isset($resultado->termino_inscricao) ? $resultado->termino_inscricao : old('termino_inscricao') }}"
                />
                <span><i><small>(limite de até 2 horas antes de começar o curso)</small></i></span>
                @if($errors->has('termino_inscricao'))
                <div class="invalid-feedback">
                {{ $errors->first('termino_inscricao') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="lfm">Imagem principal</label>
                <div class="input-group">
                <span class="input-group-prepend">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                        <i class="fas fa-picture-o"></i> Inserir imagem
                    </a>
                </span>
                <input id="thumbnail"
                    class="form-control {{ $errors->has('img') ? 'is-invalid' : '' }}"
                    type="text"
                    name="img"
                    value="{{ isset($resultado->img) ? $resultado->img : old('img') }}"
                />
                @if($errors->has('img'))
                <div class="invalid-feedback">
                {{ $errors->first('img') }}
                </div>
                @endif
                </div>
            </div>
            <div class="col-sm-2">
                <label for="acesso">Acesso</label>
                <select name="acesso" class="form-control {{ $errors->has('acesso') ? 'is-invalid' : '' }}" required>
                @foreach($acessos as $acesso)
                    <option value="{{ $acesso }}" {{ isset($resultado) && ($resultado->acesso == $acesso) ? 'selected' : '' }}>{{ $acesso }}</option>
                @endforeach
                </select>
                @if($errors->has('acesso'))
                <div class="invalid-feedback">
                    {{ $errors->first('acesso') }}
                </div>
                @endif
            </div>
            <div class="col-sm-2">
                <label for="publicado">Publicar agora?</label>
                <select name="publicado" class="form-control {{ $errors->has('publicado') ? 'is-invalid' : '' }}" required>
                @foreach(['Sim', 'Não'] as $opcao)
                    @if(old('publicado'))
                    <option value="{{ $opcao }}" {{ old('publicado') == $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                    @else
                    <option value="{{ $opcao }}" {{ isset($resultado->publicado) && ($resultado->publicado == $opcao) ? 'selected' : '' }}>{{ $opcao }}</option>
                    @endif
                @endforeach
                </select>
            </div>
        </div>
        <div class="form-group mt-3">
            <label for="resumo">Resumo</label>
            <textarea name="resumo"
                class="form-control {{ $errors->has('resumo') ? 'is-invalid' : '' }}"
                id="resumo"
                placeholder="Resumo do curso"
                rows="3"
            >{!! empty(old('resumo')) && isset($resultado->resumo) ? $resultado->resumo : old('resumo') !!}</textarea>
            @if($errors->has('resumo'))
            <div class="invalid-feedback">
                {{ $errors->first('resumo') }}
            </div>
            @endif
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" 
                class="form-control my-editor {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                id="descricao"
                rows="25"
            >{!! empty(old('descricao')) && isset($resultado->descricao) ? $resultado->descricao : old('descricao') !!}</textarea>
            @if($errors->has('descricao'))
            <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('cursos.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($resultado) ? 'Salvar' : 'Publicar' }}
            </button>
        </div>
    </div>
</form>