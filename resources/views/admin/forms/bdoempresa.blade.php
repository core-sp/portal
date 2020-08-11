<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
        <div class="col">
            <label for="razaosocial">Razão Social</label>
            <input type="text"
            class="form-control {{ $errors->has('razaosocial') ? 'is-invalid' : '' }}"
            name="razaosocial"
            id="razaosocial"
            placeholder="Razão Social"
            @if(!empty(old('razaosocial')))
                value="{{ old('razaosocial') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->razaosocial }}"
                @endif
            @endif
            />
            @if($errors->has('razaosocial'))
            <div class="invalid-feedback">
            {{ $errors->first('razaosocial') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="fantasia">Nome Fantasia</label>
            <input type="text"
            class="form-control {{ $errors->has('fantasia') ? 'is-invalid' : '' }}"
            name="fantasia"
            id="fantasia"
            placeholder="Nome Fantasia"
            @if(!empty(old('fantasia')))
                value="{{ old('fantasia') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->fantasia }}"
                @endif
            @endif
            />
            @if($errors->has('fantasia'))
            <div class="invalid-feedback">
            {{ $errors->first('fantasia') }}
            </div>
            @endif
        </div>
        </div>
        <div class="form-row mt-2">
        <div class="col">
            <label for="segmento">Segmento</label>
            <select name="segmento" class="form-control" id="segmento">
            @foreach($segmentos as $segmento)
                @if(!empty(old('segmento')))
                    @if(old('segmento') === $segmento)
                        <option value="{{ $segmento }}" selected>{{ $segmento }}</option>
                    @else
                        <option value="{{ $segmento }}">{{ $segmento }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($segmento == $resultado->segmento)
                            <option value="{{ $segmento }}" selected>{{ $segmento }}</option>
                        @else
                            <option value="{{ $segmento }}">{{ $segmento }}</option>
                        @endif
                    @else
                        <option value="{{ $segmento }}">{{ $segmento }}</option>
                    @endif
                @endif
            @endforeach
            </select>
            @if($errors->has('segmento'))
            <div class="invalid-feedback">
            {{ $errors->first('segmento') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="cnpj">CNPJ</label>
            <input type="text"
                class="form-control cnpjInput {{ $errors->has('cnpj') ? 'is-invalid' : '' }}"
                placeholder="CNPJ"
                name="cnpj"
                id="cnpj"
                maxlength="191"
                @if(!empty(old('cnpj')))
                    value="{{ old('cnpj') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->cnpj }}"
                    @endif
                @endif
                />
            @if($errors->has('cnpj'))
            <div class="invalid-feedback">
            {{ $errors->first('cnpj') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="capitalsocial">Capital Social</label>
            <select name="capitalsocial" class="form-control" id="capitalsocial" />
            @foreach($capitais as $capital)
                @if(!empty(old('capitalsocial')))
                    @if(old('capitalsocial') === $capital)
                        <option value="{{ $capital }}" selected>{{ $capital }}</option>
                    @else
                        <option value="{{ $capital }}">{{ $capital }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($capital == $resultado->capitalsocial)
                            <option value="{{ $capital }}" selected="">{{ $capital }}</option>
                        @else
                            <option value="{{ $capital }}">{{ $capital }}</option>
                        @endif
                    @else
                        <option value="{{ $capital }}">{{ $capital }}</option>
                    @endif
                @endif
            @endforeach
            </select>
            @if($errors->has('capital'))
            <div class="invalid-feedback">
            {{ $errors->first('capital') }}
            </div>
            @endif
        </div>
        </div>
        <div class="form-group mt-2">
            <label for="descricao">Descrição</label>
            <textarea class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                name="descricao"
                id="descricao"
                rows="5"
                placeholder="Descrição da empresa">@if(!empty(old('descricao'))){{ old('descricao') }}@else @if(isset($resultado)){{ $resultado->descricao }}@endif @endif</textarea>
                @if($errors->has('descricao'))
                    <div class="invalid-feedback">
                     {{ $errors->first('descricao') }}
                    </div>
                @endif
        </div>
        <h5 class="mt-4 mb-2">Informações da empresa</h5>
        <div class="form-row">
        <div class="col">
            <label for="endereco">Endereço</label>
            <input type="text"
            class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}"
            name="endereco"
            id="endereco"
            placeholder="Endereço"
            @if(!empty(old('endereco')))
                value="{{ old('endereco') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->endereco }}"
                @endif
            @endif
            />
            @if($errors->has('endereco'))
            <div class="invalid-feedback">
            {{ $errors->first('endereco') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="site">Site</label>
            <input type="text"
            class="form-control {{ $errors->has('site') ? 'is-invalid' : '' }}"
            name="site"
            id="site"
            placeholder="Site"
            @if(!empty(old('site')))
                value="{{ old('site') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->site }}"
                @endif
            @endif
            />
            @if($errors->has('site'))
            <div class="invalid-feedback">
            {{ $errors->first('site') }}
            </div>
            @endif
        </div>
        </div>
        <div class="form-row mt-2">
        <div class="col">
            <label for="email">Email</label>
            <input type="text"
            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
            name="email"
            id="email"
            placeholder="Email da empresa"
            @if(!empty(old('email')))
                value="{{ old('email') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->email }}"
                @endif
            @endif
            />
            @if($errors->has('email'))
            <div class="invalid-feedback">
            {{ $errors->first('email') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="telefone">Telefone</label>
            <input type="text"
            class="form-control telefoneInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
            name="telefone"
            id="telefone"
            placeholder="Telefone da empresa"
            @if(!empty(old('telefone')))
                value="{{ old('telefone') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->telefone }}"
                @endif
            @endif
            />
            @if($errors->has('telefone'))
            <div class="invalid-feedback">
            {{ $errors->first('telefone') }}
            </div>
            @endif
        </div>
        </div>
        <h5 class="mt-4 mb-2">Contato principal da empresa</h5>
        <div class="form-row">
        <div class="col">
            <label for="contatonome">Nome</label>
            <input type="text"
            class="form-control {{ $errors->has('contatonome') ? 'is-invalid' : '' }}"
            name="contatonome"
            id="contatonome"
            placeholder="Nome"
            @if(!empty(old('contatonome')))
                value="{{ old('contatonome') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->contatonome }}"
                @endif
            @endif
            />
            @if($errors->has('contatonome'))
            <div class="invalid-feedback">
            {{ $errors->first('contatonome') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="contatotelefone">Telefone</label>
            <input type="text"
            class="form-control telefoneInput {{ $errors->has('contatotelefone') ? 'is-invalid' : '' }}"
            name="contatotelefone"
            id="contatotelefone"
            placeholder="Telefone do contato"
            @if(!empty(old('contatotelefone')))
                value="{{ old('contatotelefone') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->contatotelefone }}"
                @endif
            @endif
            />
            @if($errors->has('contatotelefone'))
            <div class="invalid-feedback">
            {{ $errors->first('contatotelefone') }}
            </div>
            @endif
        </div>
        </div>
        <div class="form-group mt-2">
            <label for="contatoemail">Email</label>
            <input type="text"
            class="form-control {{ $errors->has('contatoemail') ? 'is-invalid' : '' }}"
            name="contatoemail"
            id="contatoemail"
            placeholder="Email do contato"
            @if(!empty(old('contatoemail')))
                value="{{ old('contatoemail') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->contatoemail }}"
                @endif
            @endif
            />
            @if($errors->has('contatoemail'))
            <div class="invalid-feedback">
                {{ $errors->first('contatoemail') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/bdo/empresas" class="btn btn-default">Cancelar</a>
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