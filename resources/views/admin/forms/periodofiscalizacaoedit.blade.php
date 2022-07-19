<form role="form" method="POST" autocomplete="false">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="form-row mb-2">
            <div class="col">
                <label for="ano">Ano</label>
                <input type="text"
                    class="form-control anoInput"
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
                <label for="processofiscalizacaopf">Processos de Fiscalização PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.processofiscalizacaopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processofiscalizacaopf]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['processofiscalizacaopf'] : $r->processofiscalizacaopf }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.processofiscalizacaopf'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.processofiscalizacaopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="processofiscalizacaopj">Processos de Fiscalização PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.processofiscalizacaopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processofiscalizacaopj]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['processofiscalizacaopj'] : $r->processofiscalizacaopj }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.processofiscalizacaopj'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.processofiscalizacaopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="registroconvertidopf">Registros Convertidos PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.registroconvertidopf') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][registroconvertidopf]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['registroconvertidopf'] : $r->registroconvertidopf }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.registroconvertidopf'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.registroconvertidopf') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="registroconvertidopj">Registros Convertidos PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.registroconvertidopj') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][registroconvertidopj]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['registroconvertidopj'] : $r->registroconvertidopj }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.registroconvertidopj'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.registroconvertidopj') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="processoverificacao">Processos de Verificação</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.processoverificacao') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][processoverificacao]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['processoverificacao'] : $r->processoverificacao }}"
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
                <label for="dispensaregistro">Dispensa de Registro</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.dispensaregistro') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][dispensaregistro]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['dispensaregistro'] : $r->dispensaregistro }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.dispensaregistro'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.dispensaregistro') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="notificacaort">Notificações de RT</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.notificacaort') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][notificacaort]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['notificacaort'] : $r->notificacaort }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.notificacaort'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.notificacaort') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="orientacaorepresentada">Orientações às representadas</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.orientacaorepresentada') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][orientacaorepresentada]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['orientacaorepresentada'] : $r->orientacaorepresentada }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.orientacaorepresentada'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.orientacaorepresentada') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="orientacaorepresentante">Orientações aos RCs</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.orientacaorepresentante') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][orientacaorepresentante]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['orientacaorepresentante'] : $r->orientacaorepresentante }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.orientacaorepresentante'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.orientacaorepresentante') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="cooperacaoinstitucional">Cooperação Institucional</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.cooperacaoinstitucional') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][cooperacaoinstitucional]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['cooperacaoinstitucional'] : $r->cooperacaoinstitucional }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.cooperacaoinstitucional'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.cooperacaoinstitucional') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <label for="autoconstatacao">Autos de Constatação</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.autoconstatacao') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][autoconstatacao]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['autoconstatacao'] : $r->autoconstatacao }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.autoconstatacao'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.autoconstatacao') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="autosdeinfracao">Autos de Infração</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.autosdeinfracao') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][autosdeinfracao]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['autosdeinfracao'] : $r->autosdeinfracao }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.autosdeinfracao'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.autosdeinfracao') }}
                </div>
                @endif
            </div>

            <div class="col">
                <label for="multaadministrativa">Multa Administrativa</label>
                <input type="number"
                    class="form-control {{ $errors->has('regional.' . $r->idregional . '.multaadministrativa') ? 'is-invalid' : '' }}"
                    name="regional[{{ $r->idregional }}][multaadministrativa]"
                    value="{{ count($errors) > 0 ? old('regional')[$r->idregional]['multaadministrativa'] : $r->multaadministrativa }}"
                />
                @if($errors->has('regional.' . $r->idregional . '.multaadministrativa'))
                <div class="invalid-feedback">
                    {{ $errors->first('regional.' . $r->idregional . '.multaadministrativa') }}
                </div>
                @endif
            </div>
        </div>
        </br></br>
        @endforeach
    </div>

    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('fiscalizacao.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>