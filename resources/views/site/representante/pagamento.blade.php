@extends('site.representante.app')

@section('content-representante')

<noscript>
    <iframe 
        style="width: 100px; height: 100px; border: 0; position:absolute; top: -5000px;" 
        src="https://h.online-metrix.net/fp/tags?org_id={{ config('app.url') != 'https://core-sp.org.br' ? '1snn5n9w' : '' }}&session_id={{ auth()->guard('representante')->user()->getSessionIdPagamento($boleto) }}">
    </iframe>
</noscript>

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Pagamento On-line</h4>
        <div class="linha-lg-mini mb-1"></div>

        <form action="{{ isset($pagamento) ? route('representante.pagamentoCartao', $boleto) : route('representante.pagamentoGerenti', $boleto) }}" method="POST" autocomplete="off">
            @csrf
            <input type="hidden" name="boleto" value="{{ $boleto }}" />

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="amount">Valor *</label>
                    <input
                        type="text"
                        name="amount"
                        class="form-control capitalSocial {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                        id="amount"
                        value="{{ isset($boleto_dados['valor']) ? $boleto_dados['valor'] : old('amount') }}"
                        readonly
                        required
                    >
                    @if($errors->has('amount'))
                        <div class="invalid-feedback">
                            {{ $errors->first('amount') }}
                        </div>
                    @endif
                </div>

                @php
                    $tiposPag = ['credit' => 'Crédito', 'debit' => 'Débito', 'combined' => 'Com dois cartões (somente crédito)'];
                @endphp
                <div class="col-sm mb-2-576">
                    <label for="tipo_pag">Forma de pagamento *</label>
                    <select name="tipo_pag" class="form-control mb-2 mr-sm-3 {{ $errors->has('tipo_pag') ? 'is-invalid' : '' }}" required>
                        @if(!isset($pagamento))
                        <option value="">Selecione a forma de pagamento...</option>
                        @endif
                    @foreach($tiposPag as $tipo => $texto)
                        @if(!isset($pagamento) || (isset($pagamento) && ($boleto_dados['tipo_pag'] == $tipo)))
                        <option value="{{ $tipo }}">{{ $texto }}</option>
                        @endif
                    @endforeach
                    </select>
                    @if($errors->has('tipo_pag'))
                    <div class="invalid-feedback">
                        {{ $errors->first('tipo_pag') }}
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                @if(!isset($pagamento) || (isset($pagamento) && isset($boleto_dados['amount_1']) && isset($boleto_dados['amount_2'])))
                <div class="col-sm mb-2-576" id="valor_combinado">
                    <label for="amount_1">Valor do primeiro cartão *</label>
                    <input
                        type="text"
                        name="amount_1"
                        class="form-control {{ isset($pagamento) ? '' : 'capitalSocial' }} {{ $errors->has('amount_1') ? 'is-invalid' : '' }}"
                        id="amount_1"
                        value="{{ isset($boleto_dados['amount_1']) ? $boleto_dados['amount_1'] : old('amount_1') }}"
                        @if(isset($pagamento))
                        readonly
                        @endif
                        required
                    >
                    @if($errors->has('amount_1'))
                        <div class="invalid-feedback">
                            {{ $errors->first('amount_1') }}
                        </div>
                    @endif
                </div>
                @endif

                <div class="col-sm mb-2-576">
                    <label for="parcelas_1">Parcelas *</label>
                    <select name="parcelas_1" class="form-control mb-2 mr-sm-3 {{ $errors->has('parcelas_1') ? 'is-invalid' : '' }}" required>
                    @for($i = 1; $i < 11; $i++)
                        @if(!isset($pagamento) || (isset($pagamento) && ($boleto_dados['parcelas_1'] == $i)))
                        <option value="{{ $i }}">{{ $i == 1 ? 'à vista' : $i . 'x sem juros' }}</option>
                        @endif
                    @endfor
                    </select>
                    @if($errors->has('parcelas_1'))
                    <div class="invalid-feedback">
                        {{ $errors->first('parcelas_1') }}
                    </div>
                    @endif
                </div>
            </div>

            @if(!isset($pagamento) || (isset($pagamento) && isset($boleto_dados['amount_1']) && isset($boleto_dados['amount_2'])))
            <div class="form-row mb-2 cadastroRepresentante" id="dados_combinado">
                <div class="col-sm mb-2-576">
                    <label for="amount_2">Valor do segundo cartão *</label>
                    <input
                        type="text"
                        name="amount_2"
                        class="form-control {{ isset($pagamento) ? '' : 'capitalSocial' }} {{ $errors->has('amount_2') ? 'is-invalid' : '' }}"
                        id="amount_2"
                        value="{{ isset($boleto_dados['amount_2']) ? $boleto_dados['amount_2'] : old('amount_2') }}"
                        @if(isset($pagamento))
                        readonly
                        @endif
                        required
                    >
                    @if($errors->has('amount_2'))
                        <div class="invalid-feedback">
                            {{ $errors->first('amount_2') }}
                        </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="parcelas_2">Parcelas *</label>
                    <select name="parcelas_2" class="form-control mb-2 mr-sm-3 {{ $errors->has('parcelas_2') ? 'is-invalid' : '' }}" required>
                    @for($i = 1; $i < 11; $i++)
                        @if(!isset($pagamento) || (isset($pagamento) && ($boleto_dados['parcelas_2'] == $i)))
                        <option value="{{ $i }}">{{ $i == 1 ? 'à vista' : $i . 'x sem juros' }}</option>
                        @endif
                    @endfor
                    </select>
                    @if($errors->has('parcelas_2'))
                    <div class="invalid-feedback">
                        {{ $errors->first('parcelas_2') }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if(isset($pagamento))
            <fieldset class="border p-3">
                <legend><small>Dados do {{ $boleto_dados['tipo_pag'] == 'combined' ? 'primeiro' : null }} cartão:</small></legend>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="card_number_1">Número do cartão *</label>
                        <input
                            type="text"
                            name="card_number_1"
                            class="form-control form-control-sm {{ $errors->has('card_number_1') ? 'is-invalid' : '' }}"
                            id="card_number_1"
                            minlength="13"
                            maxlength="19"
                            placeholder="Número de cartão válido"
                            pattern="[0-9]{13,19}" 
                            title="Somente números"
                            required
                        >
                        @if($errors->has('card_number_1'))
                        <div class="invalid-feedback">
                            {{ $errors->first('card_number_1') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="cardholder_name_1">Nome do titular *</label>
                        <input
                            type="text"
                            name="cardholder_name_1"
                            class="form-control form-control-sm text-uppercase {{ $errors->has('cardholder_name_1') ? 'is-invalid' : '' }}"
                            id="cardholder_name_1"
                            maxlength="26"
                            required
                        >
                        <small class="form-text text-muted">
                            <em>* Nome idêntico ao do cartão, sem acentos ou pontuações</em>
                        </small>
                        @if($errors->has('cardholder_name_1'))
                            <div class="invalid-feedback">
                                {{ $errors->first('cardholder_name_1') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="document_number_1">CPF / CNPJ *</label>
                        <input
                            type="text"
                            name="document_number_1"
                            class="form-control form-control-sm cpfOuCnpj {{ $errors->has('document_number_1') ? 'is-invalid' : '' }}"
                            id="document_number_1"
                            required
                        >
                        @if($errors->has('document_number_1'))
                            <div class="invalid-feedback">
                                {{ $errors->first('document_number_1') }}
                            </div>
                        @endif
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="security_code_1">CVV / CVC *</label>
                        <input
                            type="text"
                            name="security_code_1"
                            class="form-control form-control-sm {{ $errors->has('security_code_1') ? 'is-invalid' : '' }}"
                            id="security_code_1"
                            minlength="3"
                            maxlength="4"
                            required
                        >
                        @if($errors->has('security_code_1'))
                            <div class="invalid-feedback">
                                {{ $errors->first('security_code_1') }}
                            </div>
                        @endif
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="expiration_1">Data de Expiração *</label>
                        <input
                            type="month"
                            name="expiration_1"
                            class="form-control form-control-sm {{ $errors->has('expiration_1') ? 'is-invalid' : '' }}"
                            id="expiration_1"
                            required
                        >
                        @if($errors->has('expiration_1'))
                            <div class="invalid-feedback">
                                {{ $errors->first('expiration_1') }}
                            </div>
                        @endif
                    </div>
                </div>
            </fieldset>

            @if($boleto_dados['tipo_pag'] == 'combined')
            <fieldset class="border p-3">
                <legend><small>Dados do segundo cartão:</small></legend>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="card_number_2">Número do cartão *</label>
                        <input
                            type="text"
                            name="card_number_2"
                            class="form-control form-control-sm numero {{ $errors->has('card_number_2') ? 'is-invalid' : '' }}"
                            id="card_number_2"
                            placeholder="Número de cartão válido"
                            minlength="13"
                            maxlength="19"
                            pattern="[0-9]{13,19}" 
                            title="Somente números"
                            required
                        >
                        @if($errors->has('card_number_2'))
                        <div class="invalid-feedback">
                            {{ $errors->first('card_number_2') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="cardholder_name_2">Nome do titular *</label>
                        <input
                            type="text"
                            name="cardholder_name_2"
                            class="form-control form-control-sm text-uppercase {{ $errors->has('cardholder_name_2') ? 'is-invalid' : '' }}"
                            id="cardholder_name_2"
                            maxlength="26"
                            required
                        >
                        <small class="form-text text-muted">
                            <em>* Nome idêntico ao do cartão, sem acentos ou pontuações</em>
                        </small>
                        @if($errors->has('cardholder_name_2'))
                            <div class="invalid-feedback">
                                {{ $errors->first('cardholder_name_2') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="document_number_2">CPF / CNPJ *</label>
                        <input
                            type="text"
                            name="document_number_2"
                            class="form-control form-control-sm cpfOuCnpj {{ $errors->has('document_number_2') ? 'is-invalid' : '' }}"
                            id="document_number_2"
                            required
                        >
                        @if($errors->has('document_number_2'))
                            <div class="invalid-feedback">
                                {{ $errors->first('document_number_2') }}
                            </div>
                        @endif
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="security_code_2">CVV / CVC *</label>
                        <input
                            type="text"
                            name="security_code_2"
                            class="form-control form-control-sm {{ $errors->has('security_code_2') ? 'is-invalid' : '' }}"
                            id="security_code_2"
                            minlength="3"
                            maxlength="4"
                            required
                        >
                        @if($errors->has('security_code_2'))
                            <div class="invalid-feedback">
                                {{ $errors->first('security_code_2') }}
                            </div>
                        @endif
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="expiration_2">Data de Expiração *</label>
                        <input
                            type="month"
                            name="expiration_2"
                            class="form-control form-control-sm {{ $errors->has('expiration_2') ? 'is-invalid' : '' }}"
                            id="expiration_2"
                            required
                        >
                        @if($errors->has('expiration_2'))
                            <div class="invalid-feedback">
                                {{ $errors->first('expiration_2') }}
                            </div>
                        @endif
                    </div>
                </div>
            </fieldset>
            @endif
            @endif

            <div class="form-group mt-4">
                <button 
                    type="submit" 
                    class="btn btn-{{ isset($pagamento) ? 'success' : 'primary' }}"
                    @if(isset($pagamento))
                        data-toggle="modal" data-target="#modalPagamento" data-backdrop="static"
                    @endif
                >
                {{ isset($pagamento) ? 'Finalizar' : 'Confirmar dados para pagamento' }}
                </button>

                {{--
                <button 
                    type="{{ isset($pagamento) ? 'button' : 'submit' }}" 
                    class="btn btn-success {{ isset($pagamento) ? 'pay-button-getnet' : '' }}"
                >
                {{ isset($pagamento) ? 'Finalizar' : 'Confirmar dados para pagamento' }}
                </button>
                --}}
            </div>
        </form>
    </div>
</div>

<!-- The Modal -->
<div class="modal" id="modalPagamento">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <!-- Modal body -->
      <div class="modal-body text-center">
        <div class="spinner-grow text-success"></div> <strong>Aguarde... finalizando o pagamento...</strong>
      </div>

    </div>
  </div>
</div>

{{--
@if(isset($pagamento))
<script async src="https://checkout-homologacao.getnet.com.br/loader.js"
    data-getnet-sellerid="{{ isset($pagamento['sellerid']) ? $pagamento['sellerid'] : '' }}"
    data-getnet-token="{{ isset($pagamento['token']) ? $pagamento['token'] : '' }}"
    data-getnet-amount="{{ isset($pagamento['amount']) ? $pagamento['amount'] : '' }}"
    data-getnet-customerid="{{ isset($pagamento['customerid']) ? $pagamento['customerid'] : '' }}"
    data-getnet-orderid="{{ isset($pagamento['orderid']) ? $pagamento['orderid'] : '' }}"
    data-getnet-button-class="pay-button-getnet"
    data-getnet-installments="{{ isset($pagamento['installments']) ? $pagamento['installments'] : '' }}"
    data-getnet-customer-first-name="{{ isset($pagamento['first_name']) ? $pagamento['first_name'] : '' }}"
    data-getnet-customer-last-name="{{ isset($pagamento['last_name']) ? $pagamento['last_name'] : '' }}"
    data-getnet-customer-document-type="{{ isset($pagamento['document_type']) ? $pagamento['document_type'] : '' }}"
    data-getnet-customer-document-number="{{ isset($pagamento['document_number']) ? $pagamento['document_number'] : '' }}"
    data-getnet-customer-email="{{ isset($pagamento['email']) ? $pagamento['email'] : '' }}"
    data-getnet-customer-phone-number="{{ isset($pagamento['phone_number']) ? $pagamento['phone_number'] : '' }}"
    data-getnet-customer-address-street="{{ isset($pagamento['address_street']) ? $pagamento['address_street'] : '' }}"
    data-getnet-customer-address-street-number="{{ isset($pagamento['address_street_number']) ? $pagamento['address_street_number'] : '' }}"
    data-getnet-customer-address-complementary="{{ isset($pagamento['address_complementary']) ? $pagamento['address_complementary'] : '' }}"
    data-getnet-customer-address-neighborhood="{{ isset($pagamento['address_neighborhood']) ? $pagamento['address_neighborhood'] : '' }}"
    data-getnet-customer-address-city="{{ isset($pagamento['address_city']) ? $pagamento['address_city'] : '' }}"
    data-getnet-customer-address-state="{{ isset($pagamento['address_state']) ? $pagamento['address_state'] : '' }}"
    data-getnet-customer-address-zipcode="{{ isset($pagamento['address_zipcode']) ? $pagamento['address_zipcode'] : '' }}"
    data-getnet-customer-country="{{ isset($pagamento['country']) ? $pagamento['country'] : '' }}"
    data-getnet-items='[{"name": "","description": "", "value": 0, "quantity": 0,"sku": ""}]'
    data-getnet-url-callback="http://core.portal.local/"
    data-getnet-pre-authorization-credit="">
</script>
@endif
--}}

@endsection