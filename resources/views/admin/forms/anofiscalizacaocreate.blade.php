<form role="form" method="POST" autocomplete="false">
    @csrf
    <div class="card-body">
        <div class="form-row mb-2">
            <div class="col">
                <label for="ano">Ano</label>
                <input type="text"
                    class="form-control anoInput {{ $errors->has('ano') ? 'is-invalid' : '' }}"
                    placeholder="Digite o ano de fiscalização"
                    name="ano"
                    />
                @if($errors->has('ano'))
                <div class="invalid-feedback">
                {{ $errors->first('ano') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/fiscalizacao" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>