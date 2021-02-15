<form role="form" method="POST" autocomplete="false">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Notificação para Pessoas Física</label>
                <input type="text"
                    class="form-control {{ $errors->has('notificacaopf') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Física"
                    name="notificacaopf"
                    @if(!empty(old('notificacaopf')))
                        value="{{ old('notificacaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->notificacaopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('notificacaopf'))
                <div class="invalid-feedback">
                {{ $errors->first('notificacaopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Notificação para Pessoas Jurídica</label>
                <input type="text"
                    class="form-control {{ $errors->has('notificacaopj') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Jurídica"
                    name="notificacaopj"
                    @if(!empty(old('notificacaopj')))
                        value="{{ old('notificacaopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->notificacaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('notificacaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('notificacaopj') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Auto de Constatação para Pessoa Física</label>
                <input type="text"
                    class="form-control {{ $errors->has('constatacaopf') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Física"
                    name="constatacaopf"
                    @if(!empty(old('constatacaopf')))
                        value="{{ old('constatacaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->constatacaopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('constatacaopf'))
                <div class="invalid-feedback">
                {{ $errors->first('constatacaopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Auto de Constatação para Pessoa Jurídica</label>
                <input type="text"
                    class="form-control {{ $errors->has('constatacaopj') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Jurídica"
                    name="constatacaopj"
                    @if(!empty(old('constatacaopj')))
                        value="{{ old('constatacaopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->constatacaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('constatacaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('constatacaopj') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Auto de Infração para Pessoa Física</label>
                <input type="text"
                    class="form-control {{ $errors->has('infracaopf') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Física"
                    name="infracaopf"
                    @if(!empty(old('infracaopf')))
                        value="{{ old('infracaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->infracaopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('infracaopf'))
                <div class="invalid-feedback">
                {{ $errors->first('infracaopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Auto de Infração para Pessoa Jurídica</label>
                <input type="text"
                    class="form-control {{ $errors->has('infracaopj') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Jurídica"
                    name="infracaopj"
                    @if(!empty(old('infracaopj')))
                        value="{{ old('infracaopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->infracaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('infracaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('infracaopj') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Auto de Infração para Pessoa Física</label>
                <input type="text"
                    class="form-control {{ $errors->has('infracaopf') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Física"
                    name="infracaopf"
                    @if(!empty(old('infracaopf')))
                        value="{{ old('infracaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->infracaopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('infracaopf'))
                <div class="invalid-feedback">
                {{ $errors->first('infracaopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Registro Convertido para Pessoa Jurídica</label>
                <input type="text"
                    class="form-control {{ $errors->has('convertidopj') ? 'is-invalid' : '' }}"
                    placeholder="Notificação para Pessoas Jurídica"
                    name="convertidopj"
                    @if(!empty(old('convertidopj')))
                        value="{{ old('convertidopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->convertidopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('convertidopj'))
                <div class="invalid-feedback">
                {{ $errors->first('convertidopj') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/usuarios" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
            @if(isset($resultado))
                Salvar
            @else
                Publicar
            @endif
            </button>
        </div>
    </div>
</form>