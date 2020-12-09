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
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.processofiscalizacaopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processofiscalizacaopf]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['processofiscalizacaopf'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->processofiscalizacaopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.processofiscalizacaopf'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.processofiscalizacaopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Processos de Fiscalização PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.processofiscalizacaopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processofiscalizacaopj]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['processofiscalizacaopj'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->processofiscalizacaopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.processofiscalizacaopj'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.processofiscalizacaopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Registros Convertidos PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.registroconvertidopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][registroconvertidopf]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['registroconvertidopf'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->registroconvertidopf }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.registroconvertidopf'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.registroconvertidopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Registros Convertidos PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.registroconvertidopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][registroconvertidopj]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['registroconvertidopj'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->registroconvertidopj }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.registroconvertidopj'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.registroconvertidopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Processos de Verificação</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.processoverificacao') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processoverificacao]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['processoverificacao'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->processoverificacao }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.processoverificacao'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.processoverificacao') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <label for="nome">Dispensa de Registro</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.dispensaregistro') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][dispensaregistro]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['dispensaregistro'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->dispensaregistro }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.dispensaregistro'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.dispensaregistro') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Notificações de RT</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.notificacaort') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][notificacaort]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['notificacaort'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->notificacaort }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.notificacaort'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.notificacaort') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Orientações às representadas</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.orientacaorepresentada') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][orientacaorepresentada]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['orientacaorepresentada'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->orientacaorepresentada }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.orientacaorepresentada'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.orientacaorepresentada') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Orientações aos RCs</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.orientacaorepresentante') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][orientacaorepresentante]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['orientacaorepresentante'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->orientacaorepresentante }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.orientacaorepresentante'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.orientacaorepresentante') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="nome">Cooperação Institucional</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.cooperacaoinstitucional') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][cooperacaoinstitucional]"
                    @if(count($errors) > 0)
                        value="{{ old('regional')[$r->idregional]['cooperacaoinstitucional'] }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $r->cooperacaoinstitucional }}"
                        @endif
                    @endif
                    />
                @if($errors->has('regional.' . $r->idregional . '.cooperacaoinstitucional'))
                <div class="invalid-feedback">
                {{ $errors->first('regional.' . $r->idregional . '.cooperacaoinstitucional') }}
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