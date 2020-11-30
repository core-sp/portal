<form role="form" method="POST" autocomplete="false">
    @csrf
    <div class="card-body">
        <div class="form-row mb-2">
            <div class="col">
                <label for="ano">Ano</label>
                <input type="text"
                    class="form-control anoInput"
                    name="ano"
                    value="{{ $resultado->ano }}"
                    readonly
                    />
            </div>
        </div>
        
        @foreach($resultado->dadoFiscalizacao as $r)
        <hr>
            <h3>{{ $r->regional->prefixo }} - {{ $r->regional->regional }} </h3>
            <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Notificação PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('notificacaopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][notificacaopf]"
                    @if(!empty(old('notificacaopf')))
                        value="{{ old('notificacaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->notificacaopf }}"
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
                <label for="nome">Notificação PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('notificacaopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][notificacaopj]"
                    @if(!empty(old('notificacaopj')))
                        value="{{ old('notificacaopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->notificacaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('notificacaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('notificacaopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Auto de Constatação PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('constatacaopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][constatacaopf]"
                    @if(!empty(old('constatacaopf')))
                        value="{{ old('constatacaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->constatacaopf }}"
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
                <label for="nome">Auto de Constatação PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('constatacaopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][constatacaopj]"
                    @if(!empty(old('constatacaopj')))
                        value="{{ old('constatacaopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->constatacaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('constatacaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('constatacaopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Orientação</label>
                <input type="number"
                    class="form-control {{ $errors->has('orientacao') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][orientacao]"
                    @if(!empty(old('orientacao')))
                        value="{{ old('orientacao') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->orientacao }}"
                        @endif
                    @endif
                    />
                @if($errors->has('orientacao'))
                <div class="invalid-feedback">
                {{ $errors->first('orientacao') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Auto de Infração PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('infracaopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][infracaopf]"
                    @if(!empty(old('infracaopf')))
                        value="{{ old('infracaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->infracaopf }}"
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
                <label for="nome">Auto de Infração PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('infracaopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][infracaopj]"
                    @if(!empty(old('infracaopj')))
                        value="{{ old('infracaopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->infracaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('infracaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('infracaopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Registro Convertido PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('convertidopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][convertidopf]"
                    @if(!empty(old('convertidopf')))
                        value="{{ old('convertidopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->convertidopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('convertidopf'))
                <div class="invalid-feedback">
                {{ $errors->first('convertidopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Registro Convertido PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('convertidopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][convertidopj]"
                    @if(!empty(old('convertidopj')))
                        value="{{ old('convertidopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->convertidopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('convertidopj'))
                <div class="invalid-feedback">
                {{ $errors->first('convertidopj') }}
                </div>
                @endif
            </div>
            <div class="col">
            </div>
        </div>

        @endforeach
    </div>

    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/fiscalizacao" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>