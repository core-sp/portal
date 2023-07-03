<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">

    @if(isset($resultado))
        <h4>CPF / CNPJ <strong>{{ $resultado->getCpfCnpj() }}</strong></h4>
        <h5>Situação: <strong>{!! $resultado->getSituacaoHTML() !!}</strong></h5>
        <h5>Período atual da suspensão: <strong>{{ $resultado->mostraPeriodo() }}</strong> ({{ $resultado->mostraPeriodoEmDias() }})</h5>
        @if($resultado->isExcecao())
        <h5>Período da exceção: <strong>{{ $resultado->mostraPeriodoExcecao() }}</strong> ({{ $resultado->mostraPeriodoExcecaoEmDias() }})</h5>
        @endif

        <hr> 

        @if($situacao == 'suspensao')
        <h5><strong>Período da suspensão a ser editado:</strong></h5>
        <p>
            <i>Caso a data final seja Tempo Indeterminado, será considerada a data inicial.</i>
        </p>
                
        <div class="form-row">
            <div class="col-sm mb-2-576">
                <label for="data_inicial">Data Inicial</label>
                <input type="date"
                    class="form-control"
                    id="data_inicial"
                    value="{{ $resultado->data_inicial }}"
                    readonly
                    disabled
                />
            </div>

            <div class="col-sm mb-2-576">
                <label for="data_final">Data Final - <em>{{ $resultado->getDataFinal() }}</em></label>
                <select 
                    name="data_final" 
                    class="form-control {{ $errors->has('data_final') ? 'is-invalid' : '' }}" 
                    id="data_final" 
                >
                    <option value="30">+ 30 dias: {{ onlyDate($resultado->addDiasDataFinal(30)) }}</option>
                    <option value="60">+ 60 dias: {{ onlyDate($resultado->addDiasDataFinal(60)) }}</option>
                    <option value="90">+ 90 dias: {{ onlyDate($resultado->addDiasDataFinal(90)) }}</option>
                    @if(isset($resultado->data_final))
                    <option value="00">Tempo Indeterminado</option>
                    @endif
                </select>

                @if($errors->has('data_final'))
                <div class="invalid-feedback">
                    {{ $errors->first('data_final') }}
                </div>
                @endif
            </div>
        </div>

        @elseif($situacao == 'excecao')
        <h5><strong>Período da exceção a ser editado:</strong></h5>
        <p>
            <i>Limite de até 15 dias de liberação dentro da suspensão.</i>
        </p>
                
        <div class="form-row">
            <div class="col-sm mb-2-576">
                <label for="data_inicial_excecao">Data Inicial da exceção</label>
                <input type="date"
                    name="data_inicial_excecao"
                    class="form-control {{ $errors->has('data_inicial_excecao') ? 'is-invalid' : '' }}"
                    id="data_inicial_excecao"
                    value="{{ $resultado->data_inicial_excecao }}"
                    required
                />
                @if($errors->has('data_inicial_excecao'))
                <div class="invalid-feedback">
                    {{ $errors->first('data_inicial_excecao') }}
                </div>
                @endif
            </div>

            <div class="col-sm mb-2-576">
                <label for="data_final_excecao">Data Final da exceção</label>
                <input type="date"
                    name="data_final_excecao"
                    class="form-control {{ $errors->has('data_final_excecao') ? 'is-invalid' : '' }}"
                    id="data_final_excecao"
                    value="{{ $resultado->data_final_excecao }}"
                    required
                />
                @if($errors->has('data_final_excecao'))
                <div class="invalid-feedback">
                    {{ $errors->first('data_final_excecao') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="form-row mt-3">
            <div class="col-sm mb-2-576">
                <label for="justificativa">Insira a justificativa:</label>
                <textarea 
                    name="justificativa" 
                    rows="3" 
                    class="form-control {{ $errors->has('justificativa') ? 'is-invalid' : '' }}"
                    id="justificativa"
                    maxlength="1000"
                >{{ old('justificativa') }}</textarea>

                @if($errors->has('justificativa'))
                <div class="invalid-feedback">
                    {{ $errors->first('justificativa') }}
                </div>
                @endif
            </div>
        </div>
    @else
    <!-- campo cpf / cnpj, sendo que o cpf / cnpj não pode ter suspensão válida -->
    <!-- campo data inicial -->
    <!-- campo data final -->
    <!-- campo justificativa -->

    @endif
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('sala.reuniao.suspensao.lista') }}" class="btn btn-default">Voltar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($resultado) ? 'Salvar' : 'Criar' }}
            </button>
        </div>
    </div>
</form>