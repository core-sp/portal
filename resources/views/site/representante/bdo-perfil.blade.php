@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light w-100">
        <h4 class="pt-0 pb-0">{{ isset($perfil) ? 'Editar' : 'Cadastrar' }} Perfil Público{{ isset($perfil) ? ' - ID ' . $perfil->id : '' }}</h4>
        <div class="linha-lg-mini mb-3"></div>

        @if(!isset($perfil))
        <p>Preencha as informações abaixo para cadastrar seu <strong>perfil público</strong> no Portal.</p>
        @endif

        <form action="{{ isset($perfil) ? route('representante.bdo.perfil.editar') : route('representante.bdo.perfil.cadastrar') }}" method="POST">
            @csrf
            @if(isset($perfil))
                @method('PATCH')
            @endif
            
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="nome">Nome</label>
                    <input
                        type="text"
                        class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                        value="{{ $rep->nome }}"
                        readonly
                    >
                    @if($errors->has('nome'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nome') }}
                    </div>
                    @endif
                </div>

                <div class="col-sm-4 mb-2-576">
                    <label for="core">Registro Core</label>
                    <input
                        type="text"
                        class="form-control {{ $errors->has('core') ? 'is-invalid' : '' }}"
                        value="{{ $rep->registro_core }}"
                        readonly
                    >
                    @if($errors->has('core'))
                    <div class="invalid-feedback">
                        {{ $errors->first('core') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="descricao">Descrição</label>
                    <textarea
                        rows="5"
                        name="descricao"
                        class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                        maxlength="700"
                        {{ isset($perfil) ? 'readonly' : 'required' }}
                    >{{ isset($perfil) ? $perfil->descricao : '' }}</textarea>
                    @if($errors->has('descricao'))
                    <div class="invalid-feedback">
                        {{ $errors->first('descricao') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="email">E-mail 
                        <span class="ml-2">
                            <a href="{{ route('representante.contatos.view') }}">
                                <i class="fas fa-edit text-primary"></i>
                            </a>
                        </span>
                    </label> 
                    <select name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" required>
                        @if(empty($emails))
                        <option value="" selected>
                            Deve incluir um e-mail clicando no ícone azul...
                        </option>
                        @endif
                    @foreach($emails as $email)
                        <option value="{{ $email }}" 
                            {{ (isset($perfil) && ($perfil->email == $email)) || (old('email') == $email) ? 'selected' : '' }}
                        >
                            {{ $email }}
                        </option>
                    @endforeach
                    </select>
                    @if($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="telefone">Telefone
                        <span class="ml-2">
                            <a href="{{ route('representante.contatos.view') }}">
                                <i class="fas fa-edit text-primary"></i>
                            </a>
                        </span>
                    </label>
                    <select name="telefone" class="form-control {{ $errors->has('telefone') ? 'is-invalid' : '' }}" required>
                        @if(empty($telefones))
                        <option value="" selected>
                            Deve incluir um telefone clicando no ícone azul...
                        </option>
                        @endif
                    @foreach($telefones as $telefone)
                        <option value="{{ $telefone }}" 
                            {{ (isset($perfil) && ($perfil->telefone == $telefone)) || old('telefone') == $telefone ? 'selected' : '' }}
                        >
                            {{ $telefone }}
                        </option>
                    @endforeach
                    </select>
                    @if($errors->has('telefone'))
                    <div class="invalid-feedback">
                        {{ $errors->first('telefone') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="endereco">Endereço
                        <span class="ml-2">
                            <a href="{{ route('representante.enderecos.view') }}">
                                <i class="fas fa-edit text-primary"></i>
                            </a>
                        </span>
                    </label>

                    @if(isset($perfil) && ($perfil->endereco != $endereco))
                    <p class="font-weight-normal mt-1" style="font-size: 0.85rem;">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        &nbsp;
                        <em>Endereço no perfil público:</em>&nbsp;{{ $perfil->endereco }}
                    </p>
                    @endif

                    @if(isset($perfil) && ($perfil->endereco != $endereco))
                    <span style="font-size: 0.85rem;">
                        <strong>
                            <i class="fas fa-thumbtack" style="color: #a8072aff;"></i>
                            &nbsp;
                            <em>Novo endereço no sistema! Salve para confirmar a alteração.</em>
                        </strong>
                    </span>
                    @endif
                    <input
                        type="text"
                        name="endereco"
                        class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}"
                        value="{{ $endereco }}"
                        placeholder="Deve incluir um endereço clicando no ícone azul..."
                        readonly
                        required
                    >
                    @if($errors->has('endereco'))
                    <div class="invalid-feedback">
                        {{ $errors->first('endereco') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="segmento">Segmento
                        @if(!isset($perfil))
                        <i class="fas fa-sync-alt text-primary ml-2"></i>&nbsp;
                        <em class="text-secondary">Alteração do SEGMENTO no sistema será solicitada após envio do cadastro</em>
                        @endif
                    </label>
                    <select name="segmento" class="form-control {{ $errors->has('segmento') ? 'is-invalid' : '' }}" {{ isset($perfil) ? 'disabled' : 'required' }}>
                    @if(isset($perfil))
                        <option value="{{ $perfil->segmento }}" selected>
                            {{ $perfil->segmento }}
                        </option>
                    @else
                        <option value="" {{ empty($segmento) ? 'selected' : '' }}>
                            Selecione um segmento:
                        </option>
                        @foreach(segmentos() as $segmentoAll)
                        <option value="{{ $segmentoAll }}" {{ !empty($segmento) && ($segmentoAll == $segmento) ? 'selected' : '' }}>
                            {{ $segmentoAll }}
                        </option>
                        @endforeach
                    @endif
                    </select>
                    @if($errors->has('segmento'))
                    <div class="invalid-feedback">
                        {{ $errors->first('segmento') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <fieldset class="form-group border rounded border-secondary px-2 pb-3">
                        <legend class="w-auto">
                            <small>&nbsp;Regiões de atuação&nbsp;&nbsp;</small>
                        </legend>
                        <label class="mb-2">Regional
                        @if(!isset($perfil))
                            <i class="fas fa-sync-alt text-primary ml-2"></i>&nbsp;
                            <em class="text-secondary">Alteração da REGIONAL no sistema será solicitada após envio do cadastro</em>
                        @endif
                        </label>

                        <br />

                        @foreach($regionais as $regional)
                            @if(!isset($perfil) ||
                                (isset($perfil) && mb_strtoupper(json_decode($perfil->regioes)->seccional) == mb_strtoupper($regional->regional)))
                            <div class="form-check-inline">
                                <label class="form-check-label font-weight-normal" for="regional_{{ mb_strtoupper($regional->regional) }}">
                                    <input type="radio" 
                                        class="form-check-input {{ $errors->has('regioes.seccional') ? 'is-invalid' : '' }}" 
                                        id="regional_{{ mb_strtoupper($regional->regional) }}" 
                                        name="regioes.seccional" 
                                        value="{{ mb_strtoupper($regional->regional) }}"
                                        {{ isset($perfil) || (mb_strtoupper($regional->regional) == mb_strtoupper($seccional)) ? 'checked' : '' }}
                                    />
                                    {{ mb_strtoupper($regional->regional) }}

                                    @if($errors->has('regioes.seccional'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('regioes.seccional') }}
                                    </div>
                                    @endif
                                </label>
                            </div>
                            @endif
                        @endforeach

                        <hr />

                        <!-- municípios carregados (old() ou $perfil) -->
                        <div style="display: none;" id="municipios_carregados">
                        @if(isset($perfil) && !empty(json_decode($perfil->regioes)->municipios))
                            @foreach(json_decode($perfil->regioes)->municipios as $municipio)
                                <span>{{ $municipio }}</span>
                            @endforeach
                        @endif
                        </div>

                        <!-- municípios adicionados -->
                        <div class="border rounded border-success p-2 mb-3">
                            <p class="font-weight-bolder">
                                <i class="fas fa-map-marked-alt fa-lg text-primary mr-2 mb-3"></i>
                                Municípios adicionados
                                <button type="button" class="btn btn-sm btn-danger float-right" style="font-size: 0.85rem;" id="remover_todos_municipios">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </p>
                            <div class="d-flex flex-wrap pt-3" id="municipios_escolhidos"></div>
                        </div>

                        <!-- buscar e adicionar municípios -->
                        <label>
                            Municípios <em class="text-secondary">(opcional)</em>
                        </label>
                        <input class="form-control mb-2" id="buscar_municipios" type="text" placeholder="Buscar.." />
                        <div class="scrollable-div bg-white" id="lista_municipios"></div>
                        
                        @if($errors->has('regioes'))
                        <div class="invalid-feedback">
                            {{ $errors->first('regioes') }}
                        </div>
                        @endif
                    </fieldset>
                </div>
            </div>

            @if(!isset($perfil))
            <div class="form-check mt-3">
                <input 
                    class="form-check-input {{ $errors->has('checkbox-tdu') ? 'is-invalid' : '' }}"
                    name="checkbox-tdu"
                    type="checkbox"
                    id="checkbox-termo-de-uso"
                    {{ old('checkbox-tdu') === 'on' ? 'checked' : '' }}
                    required
                />
                <label for="checkbox-termo-de-uso" class="textoTermo text-justify">
                    Li e concordo com os <a class="text-primary" href="/arquivos/Termo_de_Uso_e_Consentimento_Area_Restrita_rev.pdf" target="_blank">Termos de Uso</a> gfgfgfgfgfgfgfgfgf do Core-SP.
                </label>
                @if($errors->has('checkbox-tdu'))
                <div class="invalid-feedback">
                    {{ $errors->first('checkbox-tdu') }}
                </div>
                @endif
            </div>
            @endif

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary loadingPagina">{{ isset($perfil) ? 'Salvar' : 'Enviar' }}</button>
            </div>
        </form>
    </div>
</div>

{!! str_replace('aqui', $municipios['json'], $municipios['tag']) !!}

@endsection