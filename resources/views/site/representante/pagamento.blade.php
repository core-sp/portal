@extends('site.representante.app')

@section('content-representante')

<noscript>
    <iframe 
        style="width: 100px; height: 100px; border: 0; position:absolute; top: -5000px;" 
        src="https://h.online-metrix.net/fp/tags?org_id=1snn5n9w&session_id=123456123456">
    </iframe>
</noscript>

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Pagamento On-line</h4>
        <div class="linha-lg-mini mb-1"></div>

        <form action="{{ isset($pagamento) ? route('representante.pagamentoCartao') : route('representante.pagamentoGerenti') }}" method="POST">
            @csrf
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="amount">Valor *</label>
                    <input
                        type="text"
                        name="amount"
                        class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                        id="nome"
                        value="{{ isset($pagamento) ? $pagamento : old('pagamento') }}"
                        {{ isset($pagamento) ? 'readonly' : '' }}
                        maxlength="10"

                    >
                    @if($errors->has('amount'))
                        <div class="invalid-feedback">
                            {{ $errors->first('amount') }}
                        </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="tipo_pag">Forma de pagamento *</label>
                    <select name="tipo_pag" class="form-control mb-2 mr-sm-3 {{ $errors->has('tipo_pag') ? 'is-invalid' : '' }}" {{ isset($pagamento) ? 'disabled' : '' }}>
                        <option value="Credito" {{ old('tipo_pag') == 'Credito' ? 'selected' : '' }}>Crédito</option>
                        <option value="Debito" {{ old('tipo_pag') == 'Debito' ? 'selected' : '' }}>Débito</option>
                        <option value="Combinado" {{ old('tipo_pag') == 'Combinado' ? 'selected' : '' }}>Com dois cartões (somente crédito)</option>
                    </select>
                    @if($errors->has('tipo_pag'))
                    <div class="invalid-feedback">
                        {{ $errors->first('tipo_pag') }}
                    </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="parcelas">Parcelas *</label>
                    <select name="parcelas" class="form-control mb-2 mr-sm-3 {{ $errors->has('parcelas') ? 'is-invalid' : '' }}" {{ isset($pagamento) ? 'disabled' : '' }}>
                        <option value="1" {{ old('parcelas') == '1' ? 'selected' : '' }}>à vista</option>
                        <option value="2" {{ old('parcelas') == '2' ? 'selected' : '' }}>2x sem juros</option>
                        <option value="3" {{ old('parcelas') == '3' ? 'selected' : '' }}>3x sem juros</option>
                    </select>
                    @if($errors->has('parcelas'))
                    <div class="invalid-feedback">
                        {{ $errors->first('parcelas') }}
                    </div>
                    @endif
                </div>
            </div>

            @if(isset($pagamento))
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="card_number">Número do cartão *</label>
                    <input
                        type="text"
                        name="card_number"
                        class="form-control {{ $errors->has('card_number') ? 'is-invalid' : '' }}"
                        id="nome"
                        value="{{ isset($card_number) ? $card_number : old('card_number') }}"
                    >
                    @if($errors->has('card_number'))
                        <div class="invalid-feedback">
                            {{ $errors->first('card_number') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="cardholder_name">Nome do titular *</label>
                    <input
                        type="text"
                        name="cardholder_name"
                        class="form-control {{ $errors->has('cardholder_name') ? 'is-invalid' : '' }}"
                        id="cardholder_name"
                        value="{{ isset($cardholder_name) ? $cardholder_name : old('cardholder_name') }}"
                    >
                    @if($errors->has('cardholder_name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cardholder_name') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="document_number">CPF / CNPJ *</label>
                    <input
                        type="text"
                        name="document_number"
                        class="form-control {{ $errors->has('document_number') ? 'is-invalid' : '' }}"
                        id="nome"
                        value="{{ isset($document_number) ? $document_number : old('document_number') }}"
                    >
                    @if($errors->has('document_number'))
                        <div class="invalid-feedback">
                            {{ $errors->first('document_number') }}
                        </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="security_code">CVV *</label>
                    <input
                        type="text"
                        name="security_code"
                        class="form-control {{ $errors->has('security_code') ? 'is-invalid' : '' }}"
                        id="security_code"
                        value="{{ isset($security_code) ? $security_code : old('security_code') }}"
                    >
                    @if($errors->has('security_code'))
                        <div class="invalid-feedback">
                            {{ $errors->first('security_code') }}
                        </div>
                    @endif
                </div>

                <div class="col-sm mb-2-576">
                    <label for="expiration">Data de Expiração *</label>
                    <input
                        type="month"
                        name="expiration"
                        class="form-control {{ $errors->has('expiration') ? 'is-invalid' : '' }}"
                        id="nome"
                        value="{{ isset($expiration) ? $expiration : old('expiration') }}"
                    >
                    @if($errors->has('expiration'))
                        <div class="invalid-feedback">
                            {{ $errors->first('expiration') }}
                        </div>
                    @endif
                </div>
            </div>
            @endif
            
            <div class="form-group mt-4">
                <button 
                    type="submit" 
                    class="btn btn-primary"
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