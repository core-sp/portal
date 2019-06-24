<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="cidade">Cidade</label>
                <input type="text"
                    class="form-control {{ $errors->has('cidade') ? 'is-invalid' : '' }}"
                    placeholder="Cidade"
                    name="cidade"
                    @if(isset($resultado))
                    value="{{ $resultado->regional }}"
                    @endif
                    />
                @if($errors->has('cidade'))
                <div class="invalid-feedback">
                {{ $errors->first('cidade') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="email">Email</label>
                <input type="text"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="Email"
                    name="email"
                    @if(isset($resultado))
                    value="{{ $resultado->email }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->endereco }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->numero }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->complemento }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->cep }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->telefone }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->fax }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->funcionamento }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->responsavel }}"
                    @endif
                    />
                @if($errors->has('responsavel'))
                <div class="invalid-feedback">
                {{ $errors->first('responsavel') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="ageporhorario">Agendamentos p/ horário</label>
                <input type="text"
                    class="form-control {{ $errors->has('ageporhorario') ? 'is-invalid' : '' }}"
                    placeholder="Nº de agendamentos permitidos por horário"
                    name="ageporhorario"
                    id="ageporhorario"
                    @if(isset($resultado))
                    value="{{ $resultado->ageporhorario }}"
                    @endif
                    />
                @if($errors->has('ageporhorario'))
                <div class="invalid-feedback">
                {{ $errors->first('ageporhorario') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="descricao">Descrição da regional</label>
            <textarea name="descricao"
                class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }} my-editor"
                id="descricao"
                rows="10">
                @if(isset($resultado))
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
            <a href="/admin/regionais" class="btn btn-default">Cancelar</a>
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