<form role="form" method="POST" action="{{ route('regionais.update', $resultado->idregional) }}">
    @csrf
    @if(isset($resultado))
        @method('PATCH')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="regional">Regional</label>
                <input type="text"
                    class="form-control {{ $errors->has('regional') ? 'is-invalid' : '' }}"
                    placeholder="Regional"
                    name="regional"
                    value="{{ isset($resultado->regional) ? $resultado->regional : old('regional') }}"
                    required
                />
                @if($errors->has('regional'))
                <div class="invalid-feedback">
                {{ $errors->first('regional') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="bairro">Bairro</label>
                <input type="text"
                    class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
                    placeholder="Bairro"
                    name="bairro"
                    value="{{ isset($resultado->bairro) ? $resultado->bairro : old('bairro') }}"
                    required
                />
                @if($errors->has('bairro'))
                <div class="invalid-feedback">
                {{ $errors->first('bairro') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="email">Email</label>
                <input type="text"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="Email"
                    name="email"
                    value="{{ isset($resultado->email) ? $resultado->email : old('email') }}"
                    required
                />
                @if($errors->has('email'))
                <div class="invalid-feedback">
                {{ $errors->first('email') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-sm-6">
                <label for="endereco">Endereço</label>
                <input type="text"
                    class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}"
                    placeholder="Endereço"
                    name="endereco"
                    value="{{ isset($resultado->endereco) ? $resultado->endereco : old('endereco') }}"
                    required
                />
                @if($errors->has('endereco'))
                <div class="invalid-feedback">
                {{ $errors->first('endereco') }}
                </div>
                @endif
            </div>
            <div class="col-sm-2">
                <label for="numero">Número</label>
                <input type="text"
                    class="form-control {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                    placeholder="Número"
                    name="numero"
                    value="{{ isset($resultado->numero) ? $resultado->numero : old('numero') }}"
                    required
                />
                @if($errors->has('numero'))
                <div class="invalid-feedback">
                {{ $errors->first('numero') }}
                </div>
                @endif
            </div>
            <div class="col-sm-4">
                <label for="complemento">Complemento</label>
                <input type="text"
                    class="form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
                    placeholder="Complemento"
                    name="complemento"
                    value="{{ isset($resultado->complemento) ? $resultado->complemento : old('complemento') }}"
                />
                @if($errors->has('complemento'))
                <div class="invalid-feedback">
                {{ $errors->first('complemento') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="cep">CEP</label>
                <input type="text"
                    class="form-control cepInput {{ $errors->has('cep') ? 'is-invalid' : '' }}"
                    placeholder="CEP"
                    name="cep"
                    value="{{ isset($resultado->cep) ? $resultado->cep : old('cep') }}"
                    required
                />
                @if($errors->has('cep'))
                <div class="invalid-feedback">
                {{ $errors->first('cep') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="telefone">Telefone</label>
                <input type="text"
                    class="form-control fixoInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
                    placeholder="Telefone"
                    name="telefone"
                    value="{{ isset($resultado->telefone) ? $resultado->telefone : old('telefone') }}"
                    required
                />
                @if($errors->has('telefone'))
                <div class="invalid-feedback">
                {{ $errors->first('telefone') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="fax">Fax</label>
                <input type="text"
                    class="form-control fixoInput {{ $errors->has('fax') ? 'is-invalid' : '' }}"
                    placeholder="Fax"
                    name="fax"
                    value="{{ isset($resultado->fax) ? $resultado->fax : old('fax') }}"
                />
                @if($errors->has('fax'))
                <div class="invalid-feedback">
                {{ $errors->first('fax') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="funcionamento">Horário de Funcionamento</label>
                <input type="text"
                    class="form-control {{ $errors->has('funcionamento') ? 'is-invalid' : '' }}"
                    placeholder="Descrição do horário de funcionamento"
                    name="funcionamento"
                    value="{{ isset($resultado->funcionamento) ? $resultado->funcionamento : old('funcionamento') }}"
                    required
                />
                @if($errors->has('funcionamento'))
                <div class="invalid-feedback">
                {{ $errors->first('funcionamento') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="responsavel">Responsável</label>
                <input type="text"
                    class="form-control {{ $errors->has('responsavel') ? 'is-invalid' : '' }}"
                    placeholder="Responsável"
                    name="responsavel"
                    value="{{ isset($resultado->responsavel) ? $resultado->responsavel : old('responsavel') }}"
                />
                @if($errors->has('responsavel'))
                <div class="invalid-feedback">
                {{ $errors->first('responsavel') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-sm-4">
                <label for="ageporhorario">Agendamentos p/ horário</label>
                <input type="text"
                    class="form-control {{ $errors->has('ageporhorario') ? 'is-invalid' : '' }}"
                    placeholder="Nº de agendamentos permitidos por horário"
                    name="ageporhorario"
                    id="ageporhorario"
                    value="{{ isset($resultado->ageporhorario) ? $resultado->ageporhorario : old('ageporhorario') }}"
                    required
                />
                @if($errors->has('ageporhorario'))
                <div class="invalid-feedback">
                {{ $errors->first('ageporhorario') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horariosage">Horários p/ agendamento</label>
                <select 
                    name="horariosage[]" 
                    class="form-control" 
                    size="4" 
                    multiple
                >
                    @foreach(todasHoras() as $hora)
                    <option value="{{ $hora }}" {{ in_array($hora, $resultado->horariosAge()) ? 'selected' : '' }}>{{ $hora }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <em>* Segure Ctrl para selecionar mais de um horário ou Shift para selecionar um grupo de horários</em>
                </small>
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="descricao">Descrição da regional</label>
            <textarea 
                name="descricao"
                class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }} my-editor"
                id="descricao"
                rows="10"
                required
            >
                @if(!empty(old('descricao')))
                    {{ old('descricao') }}
                @elseif(isset($resultado->descricao))
                    {!! $resultado->descricao !!}
                @endif
            </textarea>
            @if($errors->has('descricao'))
            <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('regionais.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                Salvar
            </button>
        </div>
    </div>
</form>