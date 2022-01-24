<div class="card-body">
    @if(auth()->user()->isAdmin() && auth()->user()->email == 'desenvolvimento@core-sp.org.br')
    <div class="row mb-4">
        <div class="col">
            <h5>Área de upload e download é restrita ao desenvolvedor, que será responsável para atualizar o arquivo</h5>
            <a class="btn btn-success mb-3 mt-3" href="{{ route('suporte.erros.file.get') }}">
                Arquivo Erros
            </a>
            <form action="{{ route('suporte.erros.file.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <p class="text-danger">Obs: o arquivo inserido irá substituir o existente.</p>
                <p class="text-danger">Obs2: mantenha o padrão do texto do arquivo com o modelo abaixo para evitar erros.</p>
                <p class="mb-1"><strong>Modelo: </strong></p>
                <span>texto erro*texto local*texto situação*texto sugestão</span>
                <br>
                <span>texto erro*texto local*texto situação*texto sugestão</span>
                <br>
                <span>...</span>
                <br>
                <br>
                <div class="form-group">
                    <label for="enviar-file">Enviar arquivo</label>
                    <input type="file" 
                        name="file" 
                        class="form-control-file border {{ $errors->has('file') ? 'is-invalid' : '' }}" 
                        id="enviar-file"
                        accept=".txt"
                    >
                    @if($errors->has('file'))
                    <div class="invalid-feedback">
                        {{ $errors->first('file') }}
                    </div>
                    @endif
                    <button class="btn btn-primary mt-2" type="submit">Enviar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(isset($erros))
    <div class="row">
        <div class="col">
            <label for="myInput">Buscar texto:</label>  
            <input class="form-control" id="myInput" type="text" placeholder="Buscar...">
            <br>
            <div class="table-responsive-lg">
                <table class="table table-hover mb-0">
                    <thead class="thead">
                        <tr>
                            <th>#</th>
                            <th>Erro</th>
                            <th>Área do site</th>
                            <th>Motivo(s) do disparo do erro</th>
                            <th>Sugestões de resolução</th>
                        </tr>
                    </thead>
                    <tbody id="myTable">
                    @foreach($erros as $key => $value)
                        @php
                            $campos = explode('*', $value);
                        @endphp
                        <tr>
                            <td>{{ $key }}</td>
                            <td class="text-danger">{{ isset($campos[0]) ? $campos[0] : 'Item vazio' }}</a></td>
                            <td>{{ isset($campos[1]) ? $campos[1] : 'Item vazio' }}</td>
                            <td>{{ isset($campos[2]) ? $campos[2] : 'Item vazio' }}</td>
                            <td class="text-primary">{{ isset($campos[3]) ? $campos[3] : 'Item vazio' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> 
    @else
    <p>Os erros ainda não foram tabelados</p>
    @endif
</div>