@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Pagamento On-line</h4>
        <div class="linha-lg-mini mb-1"></div>

        <form action="{{ route('representante.pagamento') }}" method="POST">
            @csrf
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="nome">Nome *</label>
                    <input
                        type="text"
                        name="nome"
                        class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                        id="nome"
                        placeholder="Nome Completo"
                        value="{{ isset($nome) ? $nome : old('nome') }}"
                        {{ isset($pagamento) ? 'readonly' : ''}}
                        maxlength="191"
                    >
                    @if($errors->has('nome'))
                        <div class="invalid-feedback">
                            {{ $errors->first('nome') }}
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="form-group mt-4">
                <a 
                    href="{{ route('representante.dashboard') }}" 
                    class="btn btn-secondary text-white text-decoration-none mr-2"
                >
                    Voltar
                </a>
                <button 
                    type="{{ isset($pagamento) ? 'button' : 'submit' }}" 
                    class="btn btn-success {{ isset($pagamento) ? 'pay-button-getnet' : '' }}"
                >
                {{ isset($pagamento) ? 'Finalizar' : 'Confirmar dados para pagamento' }}
                </button>
            </div>
        </form>
    </div>
</div>

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

@endsection