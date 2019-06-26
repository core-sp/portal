<form role="form" method="POST" autocomplete="false">
    @csrf
    {{ method_field('PUT') }}
    <div class="card-body">
    <p class="mb-4"><i>* Insira as imagens e arraste as caixas para definir a ordem de exibição<br>
    ** Para remover uma imagem, basta deixar seus campos vazios
    </i></p>
        <ul id="sortable" class="mb-0 pl-0">
            @php $i = 0; @endphp
            @foreach($resultado as $img)
            @php $i++; @endphp
            <li class="row homeimagens">
                <div class="col">
                    <div class="card card-default bg-light">
                        <div class="card-body">
                            <div class="form-row mb-2">
                                <div class="col">
                                    <label for="lfm">Imagem para desktop (1920 x 540 px)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-prepend">
                                            <a id="lfm-{{ $i }}" data-input="img-{{ $i }}" data-preview="holder" class="btn btn-default">
                                                <i class="fas fa-picture-o"></i> Alterar imagem
                                            </a>
                                        </span>
                                        <input id="img-{{ $i }}"
                                            class="form-control"
                                            type="text"
                                            name="img-{{ $i }}"
                                            value="{{ $img->url }}"
                                            />
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="lfm">Imagem para mobile (576 x 650 px)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-prepend">
                                            <a id="lfm-m-{{ $i }}" data-input="img-m-{{ $i }}" data-preview="holder" class="btn btn-default">
                                                <i class="fas fa-picture-o"></i> Alterar imagem
                                            </a>
                                        </span>
                                        <input id="img-m-{{ $i }}"
                                            class="form-control"
                                            type="text"
                                            name="img-mobile-{{ $i }}"
                                            value="{{ $img->url_mobile }}"
                                            />
                                    </div>
                                </div>
                            </div>
                            <div class="form-row mb-2">
                                <div class="col">
                                    <label for="link">Link</label>
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        name="link-{{ $i }}"
                                        value="{{ $img->link }}"
                                        />
                                </div>
                                <div class="col">
                                    <label for="selectTarget">Destino</label>
                                    <select name="target-{{ $i }}" class="form-control form-control-sm" id="selectTarget">
                                    @if($img->target === '_self')
                                        <option value="_self" selected>Abrir na mesma aba</option>
                                        <option value="_blank">Abrir em outra aba</option>
                                    @else
                                        <option value="_self">Abrir na mesma aba</option>
                                        <option value="_blank" selected>Abrir em outra aba</option>
                                    @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/paginas" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>