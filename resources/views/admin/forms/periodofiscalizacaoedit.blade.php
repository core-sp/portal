<form role="form" method="POST" autocomplete="false">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    @php
        $cont = 0;
    @endphp
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
        @php
            $contCampos = 0;
        @endphp
        <input type="hidden" name="dados[{{ $cont }}][id]" value="{{ $r->id }}" />
        <hr>
        <h3>{{ $r->regional->prefixo }} - {{ $r->regional->regional }} </h3>
        <div class="form-row mb-2">
            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="processofiscalizacaopf" />
                <label for="processofiscalizacaopf">Processos de Fiscalização PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->processofiscalizacaopf }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="processofiscalizacaopj" />
                <label for="processofiscalizacaopj">Processos de Fiscalização PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->processofiscalizacaopj }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="registroconvertidopf" />
                <label for="registroconvertidopf">Registros Convertidos PF</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->registroconvertidopf }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="registroconvertidopj" />
                <label for="registroconvertidopj">Registros Convertidos PJ</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->registroconvertidopj }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="processoverificacao" />
                <label for="processoverificacao">Processos de Verificação</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->processoverificacao }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="dispensaregistro" />
                <label for="dispensaregistro">Dispensa de Registro</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->dispensaregistro }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="notificacaort" />
                <label for="notificacaort">Notificações de RT</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->notificacaort }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="orientacaorepresentada" />
                <label for="orientacaorepresentada">Orientações às representadas</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $contCampos) : $r->orientacaorepresentada }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="orientacaorepresentante" />
                <label for="orientacaorepresentante">Orientações aos RCs</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $cont) : $r->orientacaorepresentante }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="cooperacaoinstitucional" />
                <label for="cooperacaoinstitucional">Cooperação Institucional</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $cont) : $r->cooperacaoinstitucional }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="autoconstatacao" />
                <label for="autoconstatacao">Autos de Constatação</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $cont) : $r->autoconstatacao }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="autosdeinfracao" />
                <label for="autosdeinfracao">Autos de Infração</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $cont) : $r->autosdeinfracao }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>

            <div class="col">
                <input type="hidden" name="dados[{{ $cont }}][campo][]" value="multaadministrativa" />
                <label for="multaadministrativa">Multa Administrativa</label>
                <input type="number"
                    class="form-control {{ $errors->has('dados.' . $cont . '.*') ? 'is-invalid' : '' }}"
                    name="dados[{{ $cont }}][valor][]"
                    value="{{ $errors->has('dados.' . $cont . '.*') ? old('dados.' . $cont . '.valor.' . $cont) : $r->multaadministrativa }}"
                />
                @if($errors->has('dados.' . $cont . '.*'))
                <div class="invalid-feedback">
                    @foreach($errors->get('dados.' . $cont . '.*') as $error)
                        {{ $error[0] }}
                        @if(count($errors->get('dados.' . $cont . '.*')) > 1)
                        <br>
                        @endif
                    @endforeach
                </div>
                @endif
                @php
                    $contCampos++;
                @endphp
            </div>
        </div>
        </br></br>
        @php
            $cont++;
        @endphp
        @endforeach
    </div>

    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('fiscalizacao.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>