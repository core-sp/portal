<form role="form" method="POST" autocomplete="false">
    @csrf
    @method('PUT')
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
        <p class="mb-4">
            <i>* Insira as imagens e arraste as caixas para definir a ordem de exibição<br>
            ** Para remover uma imagem, basta deixar seus campos vazios
            </i>
        </p>
        <input id="total-bannerprincipal" type="hidden" value="{{ $total }}" />
        <ul id="sortable" class="mb-0 pl-0">
            @php $i = 0; @endphp
            @foreach($resultado as $img)
            @php $i++; @endphp
            <li class="row homeimagens">
                <div class="col">
                    <div class="card card-default bg-light">
                        <div class="card-body">
                            <p class="text-danger mb-1">
                                <b>Item {{ $i }}</b>
                            </p>
                            <div class="form-row mb-2">
                                <div class="col">
                                    <label for="img-{{ $i }}">Imagem para desktop (1920 x 540 px)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-prepend">
                                            <a id="lfm-{{ $i }}" data-input="img-{{ $i }}" data-preview="holder-{{ $i }}" class="btn btn-default">
                                                <i class="fas fa-picture-o"></i> Alterar imagem
                                            </a>
                                        </span>
                                        <input id="img-{{ $i }}"
                                            class="form-control"
                                            type="text"
                                            name="img-{{ $i }}"
                                            value="{{ $img->url }}"
                                            />
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="preview-lfm-{{ $i }}" data-toggle="popover">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div id="holder-{{ $i }}" src="{{ isset($img->url) ? asset($img->url) : '' }}"></div>
                                </div>
                                <div class="col">
                                    <label for="img-m-{{ $i }}">Imagem para mobile (576 x 650 px)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-prepend">
                                            <a id="lfm-m-{{ $i }}" data-input="img-m-{{ $i }}" data-preview="holder-m-{{ $i }}" class="btn btn-default">
                                                <i class="fas fa-picture-o"></i> Alterar imagem
                                            </a>
                                        </span>
                                        <input id="img-m-{{ $i }}"
                                            class="form-control"
                                            type="text"
                                            name="img-mobile-{{ $i }}"
                                            value="{{ $img->url_mobile }}"
                                            />
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="preview-lfm-m-{{ $i }}" data-toggle="popover">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div id="holder-m-{{ $i }}" src="{{ isset($img->url_mobile) ? asset($img->url_mobile) : '' }}"></div>
                                </div>
                            </div>
                            <div class="form-row mb-2">
                                <div class="col">
                                    <label for="link-{{ $i }}">Link</label>
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        name="link-{{ $i }}"
                                        value="{{ $img->link }}"
                                        id="link-{{ $i }}"
                                        />
                                </div>
                                <div class="col">
                                    <label for="selectTarget-{{ $i }}">Destino</label>
                                    <select name="target-{{ $i }}" class="form-control form-control-sm" id="selectTarget-{{ $i }}">
                                        <option value="_self" {{ !in_array($img->target, ['_self']) ? 'selected' : '' }}>Abrir na mesma aba</option>
                                        <option value="_blank" {{ $img->target === '_blank' ? 'selected' : '' }}>Abrir em outra aba</option>
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
            <a href="{{ route('admin') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>