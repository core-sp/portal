@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light w-100">
        <h4 class="pt-0 pb-0">Cadastrar Perfil Público</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Preencha as informações abaixo para cadastrar seu <strong>perfil público</strong> no Portal.</p>
        <form action="{{ route('representante.bdo.perfil.cadastrar') }}" method="POST">
            @csrf
            
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="nome">Nome</label>
                    <input
                        type="text"
                        name="nome"
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
                        name="core"
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
                        value="{{-- $descricao --}}"
                        maxlength="700"
                        required
                    ></textarea>
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
                        <option value="{{ $email }}" {{ old('email') == $email ? 'selected' : '' }}>{{ $email }}</option>
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
                        <option value="{{ $telefone }}" {{ old('telefone') == $telefone ? 'selected' : '' }}>{{ $telefone }}</option>
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
                        <i class="fas fa-sync-alt text-primary ml-2"></i>&nbsp;
                        <em class="text-secondary">Alteração do SEGMENTO no sistema será solicitada após envio do cadastro</em>
                    </label>
                    <select name="segmento" class="form-control {{ $errors->has('segmento') ? 'is-invalid' : '' }}" required>
                        <option value="" {{ empty($segmento) ? 'selected' : '' }}>
                            Selecione um segmento:
                        </option>
                    @foreach(segmentos() as $segmentoAll)
                        <option value="{{ $segmentoAll }}" {{ !empty($segmento) && ($segmentoAll == $segmento) ? 'selected' : '' }}>
                            {{ $segmentoAll }}
                        </option>
                    @endforeach
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
                    <label for="regioes">Regiões de atuação
                        <i class="fas fa-sync-alt text-primary ml-2"></i>&nbsp;
                        <em class="text-secondary">Alteração da REGIONAL no sistema será solicitada após envio do cadastro</em>
                    </label>
                    <select name="regioes" class="form-control {{ $errors->has('regioes') ? 'is-invalid' : '' }}" required>
                        <option value="">---------------- Regionais ----------------</option>
                        @foreach($regionais as $regional)
                            <option value="{{ $regional->regional }}" {{ mb_strtolower($seccional) == mb_strtolower($regional->regional) ? 'selected' : '' }}>
                                {{ $regional->regional }}
                            </option>
                        @endforeach
                        <option value="">---------------- Municípios ----------------</option>
                    </select>
                    @if($errors->has('regioes'))
                    <div class="invalid-feedback">
                        {{ $errors->first('regioes') }}
                    </div>
                    @endif
                </div>
            </div>

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

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary loadingPagina">Enviar</button>
            </div>
        </form>
    </div>
</div>

@endsection