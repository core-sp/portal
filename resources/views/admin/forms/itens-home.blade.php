<form role="form" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    <div class="card-body">
        @if(\Session::has('message'))
        <div class="row">
            <div class="col">
                <div class="alert alert-dismissible {{ \Session::get('class') }}">
                    {!! \Session::get('message') !!}
                </div>
            </div>
        </div>
        @endif

        <!-- header-logo -->
        <fieldset class="form-group border p-3 mt-2">
            <legend class="w-auto">
                <small>Logo principal</small>
            </legend>

            <div class="form-row">
                <div class="col mr-2">
                    <!-- opção de usar a imagem padrão -->
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox"
                                name="header_logo_default"
                                class="form-check-input {{ $errors->has('header_logo_default') ? 'is-invalid' : '' }}"
                                id="header_logo_default"
                                value="header_logo_default"
                                {{ !empty(old('header_logo_default')) || (isset($header_logo) && $header_logo->itemDefault()) ? 'checked' : '' }}
                            /> Usar logo principal padrão
                        </label>
                        <div class="col-sm-2 pl-0">
                            <a href="{{ '/' . $header_logo_default }}" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home">
                                <img src="{{ asset($header_logo_default) }}" class="img-thumbnail" alt="Logo padrão">
                            </a>
                        </div>
                        @if($errors->has('header_logo_default'))
                        <div class="invalid-feedback">
                            {{ $errors->first('header_logo_default') }}
                        </div>
                        @endif
                    </div>
                    <label for="header_logo" class="mt-3">Nova imagem do logo principal <i>(tamanho recomendado: 380 x 99 px)</i>:</label>
                    <div class="custom-file">
                        <input type="file"
                            id="header_logo" 
                            class="custom-file-input {{ $errors->has('header_logo') ? 'is-invalid' : '' }}"
                            accept="image/png, image/jpeg, image/jpg"
                            name="header_logo" 
                        />
                        <label class="custom-file-label" for="header_logo">Selecionar arquivo...</label>
                        @if($errors->has('header_logo'))
                        <div class="invalid-feedback">
                            {{ $errors->first('header_logo') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col ml-2">
                    <!-- opção de usar uma imagem já armazenada -->
                    <label for="header_logo_texto">Carregar imagem já armazenada do logo principal:</label>
                    <div class="input-group mb-3">
                        <input type="text" 
                            id="header_logo_texto" 
                            class="form-control {{ $errors->has('header_logo_texto') ? 'is-invalid' : '' }}"
                            name="header_logo_texto" 
                            value="{{ !empty(old('header_logo_texto')) || (isset($header_logo) && !$header_logo->itemDefault()) ? $header_logo->url : '' }}"
                            placeholder="{{ !empty(old('header_logo_texto')) || (isset($header_logo) && !$header_logo->itemDefault()) ? '' : 'Imagem padrão escolhida' }}"
                        />
                        <div class="input-group-append">
                            <button id="pathHeaderLogo" class="btn btn-primary openStorage" type="button" data-toggle="modal" data-target="#armazenamento">Escolher</button>
                        </div>
                    </div>
                    @if(isset($header_logo) && !$header_logo->itemDefault())
                    <div class="col-sm-3 mt-3 pl-0">
                        <a href="{{ '/' . $header_logo->url }}" 
                            target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home"
                        >
                            <img src="{{ asset($header_logo->url) }}" class="img-thumbnail" alt="Logo principal customizado">
                        </a>
                    </div>
                    @endif
                    @if($errors->has('header_logo_texto'))
                    <div class="invalid-feedback">
                        {{ $errors->first('header_logo_texto') }}
                    </div>
                    @endif
                </div>
            </div>
        </fieldset>

        <!-- header-fundo -->
        <fieldset class="form-group border p-3 mt-2">
            <legend class="w-auto">
                <small>Cor de fundo do logo principal</small>
            </legend>

            <div class="form-row">
                <div class="col mr-2">
                    <!-- opção de usar a imagem padrão -->
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox"
                                name="header_fundo_default"
                                class="form-check-input {{ $errors->has('header_fundo_default') ? 'is-invalid' : '' }}"
                                id="header_fundo_default"
                                value="header_fundo_default"
                                {{ !empty(old('header_fundo_default')) || (isset($header_fundo) && $header_fundo->itemDefault()) ? 'checked' : '' }}
                            /> Usar fundo do logo principal padrão
                        </label>
                        <div class="col-sm-2 pl-0">
                            <a href="{{ $header_fundo_default }}" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home">
                                <img src="{{ asset($header_fundo_default) }}" class="img-thumbnail" alt="Fundo do logo padrão">
                            </a>
                        </div>
                        @if($errors->has('header_fundo_default'))
                        <div class="invalid-feedback">
                            {{ $errors->first('header_fundo_default') }}
                        </div>
                        @endif
                    </div>
                    <label for="header_fundo" class="mt-3">Nova imagem do fundo do logo principal <i>(tamanho recomendado: 1920 x 360 px)</i>:</label>
                    <div class="custom-file">
                        <input type="file"
                            id="header_fundo" 
                            class="custom-file-input {{ $errors->has('header_fundo') ? 'is-invalid' : '' }}"
                            accept="image/png, image/jpeg, image/jpg"
                            name="header_fundo" 
                        />
                        <label class="custom-file-label" for="header_fundo">Selecionar arquivo...</label>
                        @if($errors->has('header_fundo'))
                        <div class="invalid-feedback">
                            {{ $errors->first('header_fundo') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col ml-2">
                    <!-- opção de usar uma imagem já armazenada -->
                    <label for="header_fundo_texto">Carregar imagem já armazenada do fundo do logo principal:</label>
                    <div class="input-group mb-3">
                        <input type="text" 
                            id="header_fundo_texto" 
                            class="form-control {{ $errors->has('header_fundo_texto') ? 'is-invalid' : '' }}"
                            name="header_fundo_texto" 
                            value="{{ !empty(old('header_fundo_texto')) || (isset($header_fundo) && !$header_fundo->itemDefault() && $header_fundo->possuiImagem()) ? $header_fundo->url : '' }}"
                            placeholder="{{ !empty(old('header_fundo_texto')) || (isset($header_fundo) && !$header_fundo->itemDefault()) ? '' : 'Imagem padrão escolhida' }}"
                        />
                        <div class="input-group-append">
                            <button id="pathHeaderLogo" class="btn btn-primary openStorage" type="button" data-toggle="modal" data-target="#armazenamento">Escolher</button>
                        </div>
                    </div>
                    @if(isset($header_fundo) && !$header_fundo->itemDefault() && $header_fundo->possuiImagem())
                    <div class="col-sm-3 mt-3 pl-0">
                        <a href="{{ $header_fundo->url }}" 
                            target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home"
                        >
                            <img src="{{ asset($header_fundo->url) }}" class="img-thumbnail" alt="Fundo do logo principal customizado">
                        </a>
                    </div>
                    @endif
                    @if($errors->has('header_fundo_texto'))
                    <div class="invalid-feedback">
                        {{ $errors->first('header_fundo_texto') }}
                    </div>
                    @endif

                    <label for="header_fundo_cor" class="mt-2">Nova cor do fundo do logo principal:</label>
                    <input type="color" 
                        id="header_fundo_cor" 
                        class="form-control {{ $errors->has('header_fundo_cor') ? 'is-invalid' : '' }}"
                        name="header_fundo_cor" 
                        value="{{ !empty(old('header_fundo_cor')) || (isset($header_fundo) && !$header_fundo->itemDefault() && !$header_fundo->possuiImagem()) ? $header_fundo->url : '' }}"
                    />
                    @if($errors->has('header_fundo_cor'))
                    <div class="invalid-feedback">
                        {{ $errors->first('header_fundo_cor') }}
                    </div>
                    @endif
                </div>
            </div>
        </fieldset>

        <!-- cards -->
        <fieldset class="form-group border p-3 mt-2">
            <legend class="w-auto">
                <small>Cards - Espaço do Representante</small>
            </legend>

            <div class="form-row">
                <div class="col mr-2">
                    <!-- opção de usar a cor padrão -->
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox"
                                name="cards_1_default"
                                class="form-check-input {{ $errors->has('cards_1_default') ? 'is-invalid' : '' }}"
                                id="cards_1_default"
                                value="cards_1_default"
                                {{ !empty(old('cards_1_default')) || (isset($cards_1) && $cards_1->itemDefault()) ? 'checked' : '' }}
                            /> Usar cor padrão do card escuro<i class="fas fa-square fa-border ml-1" style="color:{{ $padroes['cards_1_default'] }};"></i>
                        </label>
                        @if($errors->has('cards_1_default'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cards_1_default') }}
                        </div>
                        @endif
                    </div>
                    <label for="cards_1" class="mt-2">Nova cor do card escuro:</label>
                    <input type="color" 
                        id="cards_1" 
                        class="form-control {{ $errors->has('cards_1') ? 'is-invalid' : '' }}"
                        name="cards_1" 
                        value="{{ !empty(old('cards_1')) || (isset($cards_1) && !$cards_1->itemDefault()) ? $cards_1->url : '#000' }}"
                    />
                    @if($errors->has('cards_1'))
                    <div class="invalid-feedback">
                        {{ $errors->first('cards_1') }}
                    </div>
                    @endif
                </div>
                <div class="col ml-2">
                    <!-- opção de usar a cor padrão -->
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox"
                                name="cards_2_default"
                                class="form-check-input {{ $errors->has('cards_2_default') ? 'is-invalid' : '' }}"
                                id="cards_2_default"
                                value="cards_2_default"
                                {{ !empty(old('cards_2_default')) || (isset($cards_2) && $cards_2->itemDefault()) ? 'checked' : '' }}
                            /> Usar cor padrão do card claro<i class="fas fa-square fa-border ml-1" style="color:{{ $padroes['cards_2_default'] }};"></i>
                        </label>
                        @if($errors->has('cards_2_default'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cards_2_default') }}
                        </div>
                        @endif
                    </div>
                    <label for="cards_2" class="mt-2">Nova cor do card claro:</label>
                    <input type="color" 
                        id="cards_2" 
                        class="form-control {{ $errors->has('cards_2') ? 'is-invalid' : '' }}"
                        name="cards_2" 
                        value="{{ !empty(old('cards_2')) || (isset($cards_2) && !$cards_2->itemDefault()) ? $cards_2->url : '#FFF' }}"
                    />
                    @if($errors->has('cards_2'))
                    <div class="invalid-feedback">
                        {{ $errors->first('cards_2') }}
                    </div>
                    @endif
                </div>
            </div>
        </fieldset>

        <!-- calendário -->
        <fieldset class="form-group border p-3 mt-2">
            <legend class="w-auto">
                <small>Calendário</small>
            </legend>

            <div class="form-row">
                <div class="col mr-2">
                    <!-- opção de usar a imagem padrão -->
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox"
                                name="calendario_default"
                                class="form-check-input {{ $errors->has('calendario_default') ? 'is-invalid' : '' }}"
                                id="calendario_default"
                                value="calendario_default"
                                {{ !empty(old('calendario_default')) || (isset($calendario) && $calendario->itemDefault()) ? 'checked' : '' }}
                            /> Usar calendário padrão (2023)
                        </label>
                        <div class="col-sm-2 pl-0">
                            <a href="{{ '/' . $calendario_default }}" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home">
                                <img src="{{ asset($calendario_default) }}" class="img-thumbnail" alt="Calendário padrão">
                            </a>
                        </div>
                        @if($errors->has('calendario_default'))
                        <div class="invalid-feedback">
                            {{ $errors->first('calendario_default') }}
                        </div>
                        @endif
                    </div>
                    <label for="calendario" class="mt-3">Nova imagem do calendário <i>(tamanho recomendado: 1050 x 680 px)</i>:</label>
                    <div class="custom-file">
                        <input type="file"
                            id="calendario" 
                            class="custom-file-input {{ $errors->has('calendario') ? 'is-invalid' : '' }}"
                            accept="image/png, image/jpeg, image/jpg"
                            name="calendario" 
                        />
                        <label class="custom-file-label" for="calendario">Selecionar arquivo...</label>
                        @if($errors->has('calendario'))
                        <div class="invalid-feedback">
                            {{ $errors->first('calendario') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col ml-2">
                    <!-- opção de usar uma imagem já armazenada -->
                    <label for="calendario_texto">Carregar imagem já armazenada do calendário:</label>
                    <div class="input-group mb-3">
                        <input type="text" 
                            id="calendario_texto" 
                            class="form-control {{ $errors->has('calendario_texto') ? 'is-invalid' : '' }}"
                            name="calendario_texto" 
                            value="{{ !empty(old('calendario_texto')) || (isset($calendario) && !$calendario->itemDefault()) ? $calendario->url : '' }}"
                            placeholder="{{ !empty(old('calendario_texto')) || (isset($calendario) && !$calendario->itemDefault()) ? '' : 'Imagem padrão escolhida' }}"
                        />
                        <div class="input-group-append">
                            <button id="pathCalendario" class="btn btn-primary openStorage" type="button" data-toggle="modal" data-target="#armazenamento">Escolher</button>
                        </div>
                    </div>
                    @if(isset($calendario) && !$calendario->itemDefault())
                    <div class="col-sm-3 mt-3 pl-0">
                        <a href="{{ isset($calendario) && !$calendario->itemDefault() ? '/' . $calendario->url : '#' }}" 
                            target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home"
                        >
                            <img src="{{ asset($calendario->url) }}" class="img-thumbnail" alt="Calendário customizado">
                        </a>
                    </div>
                    @endif
                    @if($errors->has('calendario_texto'))
                    <div class="invalid-feedback">
                        {{ $errors->first('calendario_texto') }}
                    </div>
                    @endif
                </div>
            </div>
        </fieldset>

        <!-- footer -->
        <fieldset class="form-group border p-3 mt-2">
            <legend class="w-auto">
                <small>Rodapé</small>
            </legend>

            <div class="form-row">
                <div class="col">
                    <!-- opção de usar a cor padrão -->
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox"
                                name="footer_default"
                                class="form-check-input {{ $errors->has('footer_default') ? 'is-invalid' : '' }}"
                                id="footer_default"
                                value="footer_default"
                                {{ !empty(old('footer_default')) || (isset($footer) && $footer->itemDefault()) ? 'checked' : '' }}
                            /> Usar cor padrão do rodapé<i class="fas fa-square fa-border ml-1" style="color:{{ $padroes['footer_default'] }};"></i>
                        </label>
                        @if($errors->has('footer_default'))
                        <div class="invalid-feedback">
                            {{ $errors->first('footer_default') }}
                        </div>
                        @endif
                    </div>
                    <label for="footer" class="mt-2">Nova cor do rodapé:</label>
                    <input type="color" 
                        id="footer" 
                        class="form-control {{ $errors->has('footer') ? 'is-invalid' : '' }}"
                        name="footer" 
                        value="{{ !empty(old('footer')) || (isset($footer) && !$footer->itemDefault()) ? $footer->url : '#000' }}"
                    />
                    @if($errors->has('footer'))
                    <div class="invalid-feedback">
                        {{ $errors->first('footer') }}
                    </div>
                    @endif
                </div>
            </div>
        </fieldset>

    </div>

    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('admin') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>

<!-- The Modal -->
<div class="modal" id="armazenamento">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
      
            <!-- Modal Header -->
            <div class="modal-header">
            <h4 class="modal-title">Modal Heading</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            
            <!-- Modal body -->
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Arquivo</th>
                            <th>Visualizar</th>
                            <th>Excluir</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>