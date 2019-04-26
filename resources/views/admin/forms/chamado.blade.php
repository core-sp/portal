<form role="form" method="POST">
    @csrf
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="tipo">Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="Dúvida">Dúvida</option>
                    <option value="Reporte de bugs">Reportar bug</option>
                    <option value="Sugestão">Sugestão</option>
                    <option value="Solicitação de funcionalidade">Solicitar funcionalidade</option>
                </select>
                @if($errors->has('tipo'))
                    <div class="invalid-feedback">
                        {{ $errors->first('tipo') }}
                    </div>
                @endif
            </div>
            <div class="col">
                <label for="prioridade">Prioridade</label>
                <select name="prioridade" class="form-control">
                    <option value="Muito baixa">Muito baixa</option>
                    <option value="Baixa">Baixa</option>
                    <option value="Normal">Normal</option>
                    <option value="Alta">Alta</option>
                    <option value="Muito alta">Muito alta</option>
                </select>
                @if($errors->has('prioridade'))
                    <div class="invalid-feedback">
                        {{ $errors->first('prioridade') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="mensagem">Mensagem</label>
                <textarea name="mensagem"
                    class="form-control {{ $errors->has('mensagem') ? 'is-invalid' : '' }}"
                    id="mensagem"
                    placeholder="Descreva com detalhes sua solicitação"
                    rows="3"></textarea>
                @if($errors->has('mensagem'))
                <div class="invalid-feedback">
                    {{ $errors->first('mensagem') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="lfm">Print</label>
                <div class="input-group">
                <span class="input-group-prepend">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                    <i class="fas fa-picture-o"></i> Inserir imagem
                    </a>
                </span>
                <input id="thumbnail"
                    class="form-control"
                    type="text"
                    name="img"
                    placeholder="Se necessário, anexe um print à solicitação"
                    />
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer float-right">
        <a href="/admin" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">Registrar</button>
    </div>
</form>