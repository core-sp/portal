<form role="form" method="POST" autocomplete="false">
    @csrf
    <div class="card-body">
        <div class="form-row mb-2">
            <div class="col">
                <label for="ano">Ano</label>
                <input type="text"
                    class="form-control anoInput"
                    name="periodo"
                    value="{{ $resultado->periodo }}"
                    readonly
                    />
            </div>
        </div>
        
        @foreach($resultado->dadoFiscalizacao as $r)
        <hr>
        <h3>{{ $r->regional->prefixo }} - {{ $r->regional->regional }} </h3>
        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Processos de Fiscalização PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('processofiscalizacaopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processofiscalizacaopf]"
                    @if(!empty(old('processofiscalizacaopf')))
                        value="{{ old('processofiscalizacaopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->processofiscalizacaopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('processofiscalizacaopf'))
                <div class="invalid-feedback">
                {{ $errors->first('processofiscalizacaopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Processos de Fiscalização PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('processofiscalizacaopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processofiscalizacaopj]"
                    @if(!empty(old('processofiscalizacaopj')))
                        value="{{ old('processofiscalizacaopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->processofiscalizacaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('processofiscalizacaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('processofiscalizacaopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Registros Convertidos PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('registroconvertidopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][registroconvertidopf]"
                    @if(!empty(old('registroconvertidopf')))
                        value="{{ old('registroconvertidopf') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->registroconvertidopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('registroconvertidopf'))
                <div class="invalid-feedback">
                {{ $errors->first('registroconvertidopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Registros Convertidos PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('registroconvertidopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][registroconvertidopj]"
                    @if(!empty(old('registroconvertidopj')))
                        value="{{ old('registroconvertidopj') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->registroconvertidopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('registroconvertidopj'))
                <div class="invalid-feedback">
                {{ $errors->first('registroconvertidopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Processos de Verificação</label>
                <input type="number"
                    class="form-control {{ $errors->has('processoverificacao') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processoverificacao]"
                    @if(!empty(old('processoverificacao')))
                        value="{{ old('processoverificacao') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->processoverificacao }}"
                        @endif
                    @endif
                    />
                @if($errors->has('processoverificacao'))
                <div class="invalid-feedback">
                {{ $errors->first('processoverificacao') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Dispensa de Registro</label>
                <input type="number"
                    class="form-control {{ $errors->has('dispensaregistro') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][dispensaregistro]"
                    @if(!empty(old('dispensaregistro')))
                        value="{{ old('dispensaregistro') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->dispensaregistro }}"
                        @endif
                    @endif
                    />
                @if($errors->has('dispensaregistro'))
                <div class="invalid-feedback">
                {{ $errors->first('dispensaregistro') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Notificações de RT</label>
                <input type="number"
                    class="form-control {{ $errors->has('notificacaort') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][notificacaort]"
                    @if(!empty(old('notificacaort')))
                        value="{{ old('notificacaort') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->notificacaort }}"
                        @endif
                    @endif
                    />
                @if($errors->has('notificacaort'))
                <div class="invalid-feedback">
                {{ $errors->first('notificacaort') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Orientações às representadas</label>
                <input type="number"
                    class="form-control {{ $errors->has('orientacaorepresentada') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][orientacaorepresentada]"
                    @if(!empty(old('orientacaorepresentada')))
                        value="{{ old('orientacaorepresentada') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->orientacaorepresentada }}"
                        @endif
                    @endif
                    />
                @if($errors->has('orientacaorepresentada'))
                <div class="invalid-feedback">
                {{ $errors->first('orientacaorepresentada') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Orientações aos RCs</label>
                <input type="number"
                    class="form-control {{ $errors->has('orientacaorepresentante') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][orientacaorepresentante]"
                    @if(!empty(old('orientacaorepresentante')))
                        value="{{ old('orientacaorepresentante') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->orientacaorepresentante }}"
                        @endif
                    @endif
                    />
                @if($errors->has('orientacaorepresentante'))
                <div class="invalid-feedback">
                {{ $errors->first('orientacaorepresentante') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Cooperação Institucional</label>
                <input type="number"
                    class="form-control {{ $errors->has('cooperacaoinstitucional') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][cooperacaoinstitucional]"
                    @if(!empty(old('cooperacaoinstitucional')))
                        value="{{ old('cooperacaoinstitucional') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->cooperacaoinstitucional }}"
                        @endif
                    @endif
                    />
                @if($errors->has('cooperacaoinstitucional'))
                <div class="invalid-feedback">
                {{ $errors->first('cooperacaoinstitucional') }}
                </div>
                @endif
            </div>
        </div>
        </br></br>
        @endforeach
    </div>

    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/fiscalizacao" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>