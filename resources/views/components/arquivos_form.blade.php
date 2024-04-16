<!-- Input do arquivo -->
<div class="Arquivo_{{ $nome }}">
    <div class="form-row mb-2">
        <div class="input-group col-sm mb-2-576">
            <div class="custom-file">
                <input 
                    type="file" 
                    class="{{ $classes }} custom-file-input files {{ $errors->has('path') ? 'is-invalid' : '' }}" 
                    id="anexos"
                    name="path"
                    value=""
                    accept="{{ $accept }}"
                    @if($multiple)
                        multiple
                    @endif
                />
                <label class="custom-file-label ml-0" for="anexos" data-clarity-mask="True"><span class="text-secondary">Escolher arquivo</span></label>
            </div>
            <div class="input-group-append">
                <button class="btn btn-danger limparFile" type="button"><i class="fas fa-trash-alt"></i></button>
            </div>
            @if($errors->has('path'))
            <div class="invalid-feedback" style="display:block">
                {{ $errors->first('path') }}
            </div>
            @endif
        </div>
    </div>
</div>