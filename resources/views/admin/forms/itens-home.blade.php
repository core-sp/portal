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
        <fieldset class="form-group border border-primary p-3 mt-2">
            <legend class="w-auto">
                <small>Logo principal</small>
            </legend>

            <div class="form-row">
                <div class="card-deck w-100">
                    <div class="card">
                        <div class="card-body">
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
                                    @if($errors->has('header_logo_default'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('header_logo_default') }}
                                    </div>
                                    @endif
                                </label>
                            </div>
                            <div class="card-img-bottom" style="width:30%">
                                <a href="{{ '/' . $header_logo_default }}" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home">
                                    <img src="{{ asset($header_logo_default) }}" class="img-thumbnail" alt="Logo padrão">
                                </a>
                            </div>
                        </div>
                    </div>
                
                    <div class="card">
                        <div class="card-body">
                            <!-- opção de usar uma imagem já armazenada -->
                            <label for="header_logo">Imagem do logo principal <span class="text-nowrap"><i>(tamanho recomendado: 380 x 99 px)</i></span></label>
                            <div class="input-group mb-3">
                                <input type="text" 
                                    id="header_logo" 
                                    class="form-control {{ $errors->has('header_logo') ? 'is-invalid' : '' }}"
                                    name="header_logo" 
                                    value="{{ !empty(old('header_logo')) || (isset($header_logo) && !$header_logo->itemDefault()) ? $header_logo->url : '' }}"
                                    placeholder="{{ !empty(old('header_logo')) || (isset($header_logo) && !$header_logo->itemDefault()) ? '' : 'Imagem padrão escolhida' }}"
                                />
                                <div class="input-group-append">
                                    <button id="pathHeaderLogo" class="btn btn-primary openStorage" type="button" data-toggle="modal" data-target="#armazenamento">Escolher</button>
                                </div>
                                @if($errors->has('header_logo'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('header_logo') }}
                                </div>
                                @endif
                            </div>
                            @if(isset($header_logo) && !$header_logo->itemDefault())
                            <div class="card-img-bottom" style="width:30%">
                                <a href="{{ $header_logo->getLinkHref() }}" 
                                    target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home"
                                >
                                    <img src="{{ asset($header_logo->url) }}" class="img-thumbnail" alt="Logo principal customizado">
                                    <small><em>Imagem atual na home</em></small>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- header-fundo -->
        <fieldset class="form-group border border-primary p-3 mt-2">
            <legend class="w-auto">
                <small>Cor de fundo do logo principal</small>
            </legend>

            <h6 class="text-danger"><i>* Pode escolher usar imagem ou cor.</i></h6>

            <div class="form-row">
                <div class="card-deck w-100">
                    <div class="card">
                        <div class="card-body">
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
                                    @if($errors->has('header_fundo_default'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('header_fundo_default') }}
                                    </div>
                                    @endif
                                </label>
                            </div>
                            <div class="card-img-bottom" style="width:30%">
                                <a href="{{ '/' . $header_fundo_default }}" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home">
                                    <img src="{{ asset($header_fundo_default) }}" class="img-thumbnail" alt="Fundo do logo padrão">
                                </a>
                            </div>
                        </div>
                    </div>
                
                    <div class="card">
                        <div class="card-body">
                            <!-- opção de usar uma imagem já armazenada -->
                            <label for="header_fundo">Imagem do fundo do logo principal <span class="text-nowrap"><i>(tamanho recomendado: 1920 x 360 px)</i></span></label>
                            <div class="input-group mb-3">
                                <input type="text" 
                                    id="header_fundo" 
                                    class="form-control {{ $errors->has('header_fundo') ? 'is-invalid' : '' }}"
                                    name="header_fundo" 
                                    value="{{ !empty(old('header_fundo')) || (isset($header_fundo) && !$header_fundo->itemDefault() && $header_fundo->possuiImagem()) ? $header_fundo->url : '' }}"
                                    placeholder="{{ !empty(old('header_fundo')) || (isset($header_fundo) && !$header_fundo->itemDefault()) ? '' : 'Imagem padrão escolhida' }}"
                                />
                                <div class="input-group-append">
                                    <button id="pathHeaderLogo" class="btn btn-primary openStorage" type="button" data-toggle="modal" data-target="#armazenamento">Escolher</button>
                                </div>
                                @if($errors->has('header_fundo'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('header_fundo') }}
                                </div>
                                @endif
                            </div>
                            @if(isset($header_fundo) && !$header_fundo->itemDefault() && $header_fundo->possuiImagem())
                            <div class="card-img-bottom" style="width:30%">
                                <a href="{{ $header_fundo->getLinkHref() }}" 
                                    target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home"
                                >
                                    <img src="{{ asset($header_fundo->url) }}" class="img-thumbnail" alt="Fundo do logo principal customizado">
                                    <small><em>Imagem atual na home</em></small>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="col">
                    <label for="header_fundo_cor" class="mt-2">Nova cor do fundo do logo principal:</label>
                    <input type="color" 
                        id="header_fundo_cor" 
                        class="form-control {{ $errors->has('header_fundo_cor') ? 'is-invalid' : '' }}"
                        name="header_fundo_cor" 
                        value="{{ !empty(old('header_fundo_cor')) || (isset($header_fundo) && !$header_fundo->itemDefault() && !$header_fundo->possuiImagem()) ? $header_fundo->url : '#000000' }}"
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
        <fieldset class="form-group border border-primary p-3 mt-2">
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
                            @if($errors->has('cards_1_default'))
                            <div class="invalid-feedback">
                                {{ $errors->first('cards_1_default') }}
                            </div>
                            @endif
                        </label>
                    </div>
                    <label for="cards_1" class="mt-2">Nova cor do card escuro:</label>
                    <input type="color" 
                        id="cards_1" 
                        class="form-control {{ $errors->has('cards_1') ? 'is-invalid' : '' }}"
                        name="cards_1" 
                        value="{{ (!empty(old('cards_1')) && (old('cards_1') != $padroes['cards_1_default'])) || (isset($cards_1) && !$cards_1->itemDefault()) ? $cards_1->url : '#000000' }}"
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
                            @if($errors->has('cards_2_default'))
                            <div class="invalid-feedback">
                                {{ $errors->first('cards_2_default') }}
                            </div>
                            @endif
                        </label>
                    </div>
                    <label for="cards_2" class="mt-2">Nova cor do card claro:</label>
                    <input type="color" 
                        id="cards_2" 
                        class="form-control {{ $errors->has('cards_2') ? 'is-invalid' : '' }}"
                        name="cards_2" 
                        value="{{ (!empty(old('cards_2')) && (old('cards_2') != $padroes['cards_2_default'])) || (isset($cards_2) && !$cards_2->itemDefault()) ? $cards_2->url : '#ffffff' }}"
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
        <fieldset class="form-group border border-primary p-3 mt-2">
            <legend class="w-auto">
                <small>Calendário</small>
            </legend>

            <div class="form-row">
                <div class="card-deck w-100">
                    <!-- opção de usar a imagem padrão -->
                    <div class="card">
                        <div class="card-body">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox"
                                        name="calendario_default"
                                        class="form-check-input {{ $errors->has('calendario_default') ? 'is-invalid' : '' }}"
                                        id="calendario_default"
                                        value="calendario_default"
                                        {{ !empty(old('calendario_default')) || (isset($calendario) && $calendario->itemDefault()) ? 'checked' : '' }}
                                    /> Usar calendário padrão
                                    @if($errors->has('calendario_default'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('calendario_default') }}
                                    </div>
                                    @endif
                                </label>
                            </div>
                            <div class="card-img-bottom" style="width:30%">
                                <a href="{{ '/' . $calendario_default }}" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home">
                                    <img src="{{ asset($calendario_default) }}" class="img-thumbnail" alt="Calendário padrão">
                                </a>
                            </div>
                        </div>
                    </div>
                
                    <!-- opção de usar uma imagem já armazenada -->
                    <div class="card">
                        <div class="card-body">
                            <label for="calendario">Imagem do calendário <span class="text-nowrap"><i>(tamanho recomendado: 1050 x 680 px)</i></span></label>
                            <div class="input-group mb-3">
                                <input type="text" 
                                    id="calendario" 
                                    class="form-control {{ $errors->has('calendario') ? 'is-invalid' : '' }}"
                                    name="calendario" 
                                    value="{{ !empty(old('calendario')) || (isset($calendario) && !$calendario->itemDefault()) ? $calendario->url : '' }}"
                                    placeholder="{{ !empty(old('calendario')) || (isset($calendario) && !$calendario->itemDefault()) ? '' : 'Imagem padrão escolhida' }}"
                                />
                                <div class="input-group-append">
                                    <button id="pathCalendario" class="btn btn-primary openStorage" type="button" data-toggle="modal" data-target="#armazenamento">Escolher</button>
                                </div>
                                @if($errors->has('calendario'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('calendario') }}
                                </div>
                                @endif
                            </div>
                            @if(isset($calendario) && !$calendario->itemDefault())
                            <div class="card-img-bottom" style="width:30%">
                                <a href="{{ $calendario->getLinkHref() }}" 
                                    target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home"
                                >
                                    <img src="{{ asset($calendario->url) }}" class="img-thumbnail" alt="Calendário customizado">
                                    <small><em>Imagem atual na home</em></small>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- footer -->
        <fieldset class="form-group border border-primary p-3 mt-2">
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
                        value="{{ (!empty(old('footer')) && (old('footer') != $padroes['footer_default'])) || (isset($footer) && !$footer->itemDefault()) ? $footer->url : '#000000' }}"
                    />
                    @if($errors->has('footer'))
                    <div class="invalid-feedback">
                        {{ $errors->first('footer') }}
                    </div>
                    @endif
                </div>
            </div>
        </fieldset>

        <!-- footer -->
        <fieldset class="form-group border border-primary p-3 mt-2">
            <legend class="w-auto">
                <small>Função Neve</small>
            </legend>

            <h6 class="text-danger"><i>* O fundo do logo principal não pode ser uma imagem.</i></h6>

            <div class="form-row">
                <div class="col">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox"
                                name="neve_default"
                                class="form-check-input {{ $errors->has('neve_default') ? 'is-invalid' : '' }}"
                                id="neve_default"
                                value="neve_default"
                                {{ !empty(old('neve_default')) || (isset($neve_default) && $neve_default->itemDefault()) ? 'checked' : '' }}
                            /> Sim, inserir neve
                            @if($errors->has('neve_default'))
                            <div class="invalid-feedback">
                                {{ $errors->first('neve_default') }}
                            </div>
                            @endif
                        </label>
                    </div>
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

    <!-- The Modal -->
    <div class="modal" id="armazenamento">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
        
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-folder-open"></i> Armazenamento - Adicionar / Selecionar arquivo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="alert alert-dismissible alert-success" id="msgStorage" style="display: none"></div>

                    <div>
                        <h5>Adicionar novo arquivo</h5>
                        <hr>
                        <label for="file_itens_home">Nova imagem</label>
                        <div class="custom-file">
                            <input type="file"
                                id="file_itens_home" 
                                class="custom-file-input {{ $errors->has('file_itens_home') ? 'is-invalid' : '' }}"
                                accept="image/png, image/jpeg, image/jpg"
                                name="file_itens_home" 
                            />
                            <label class="custom-file-label" for="file_itens_home">Selecionar arquivo...</label>
                            @if($errors->has('file_itens_home'))
                            <div class="invalid-feedback">
                                {{ $errors->first('file_itens_home') }}
                            </div>
                            @endif
                        </div>
                    </div>
                        
                    <div class="mt-4">
                        <hr>
                        <h5>Selecionar arquivos</h5>
                        <div class="card-columns" id="cards"></div>
                    </div>
                </div>
                
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

</form>