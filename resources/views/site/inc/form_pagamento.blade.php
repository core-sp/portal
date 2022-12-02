<style>
    .ui-datepicker-calendar {
        display: none;
    }
</style>

@if(!$checkoutIframe)
<noscript>
    <iframe 
        style="width: 100px; height: 100px; border: 0; position:absolute; top: -5000px;" 
        src="https://h.online-metrix.net/fp/tags?org_id={{ config('app.url') != 'https://core-sp.org.br' ? '1snn5n9w' : 'k8vif92e' }}&session_id={{ $user->getSessionIdPagamento($boleto) }}">
    </iframe>
</noscript>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">{{ isset($cancelamento) ? 'Cancelamento do ' : null }} Pagamento On-line</h4>
        <div class="linha-lg-mini mb-1"></div>

            @if($errors->any())
            <ul class="list-group mb-3">
                @foreach($errors->all() as $error)
                    <li class="list-group-item list-group-item-danger pt-1"><i class="fas fa-times"></i><small>&nbsp;&nbsp;{{ $error }}</small></li>
                @endforeach
            </ul>
            @endif
            
    @if(!\Route::is('pagamento.cancelar.*') && !\Route::is('pagamento.visualizar'))
        <small class="form-text text-muted mb-3">
            <em><span class="text-danger">*</span> Preenchimento obrigatório</em>
        </small>
        <form action="{{ isset($pagamento) ? route('pagamento.cartao', $boleto) : route('pagamento.gerenti', $boleto) }}" method="POST" autocomplete="off" id="{{ isset($pagamento) ? 'formPagamento' : null }}">
            @csrf
            <input type="hidden" name="boleto" value="{{ $boleto }}" />

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="amount">Valor total <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="amount"
                        class="form-control capitalSocial pagamento"
                        id="amount"
                        value="{{ isset($boleto_dados['valor']) ? $boleto_dados['valor'] : old('amount') }}"
                        readonly
                        required
                    >
                </div>

                @php
                    $tiposPag = ['credit' => 'Crédito', 'credit_3ds' => 'Crédito com 3DS', 'combined' => 'Crédito com dois cartões', 'debit_3ds' => 'Débito com 3DS'];
                    if($checkoutIframe)
                        unset($tiposPag['combined']);
                @endphp
                <div class="col-sm mb-2-576">
                    <label for="tipo_pag">Forma de pagamento <span class="text-danger">*</span></label>
                    <select 
                        name="tipo_pag" 
                        class="form-control mb-2 mr-sm-3 pagamento"
                        id="tipo_pag" 
                        required
                        @if(isset($pagamento))
                        readonly
                        @endif
                    >
                        @if(!isset($pagamento))
                        <option value="">Selecione a forma de pagamento...</option>
                        @endif
                    @foreach($tiposPag as $tipo => $texto)
                        @if(!isset($pagamento) || (isset($pagamento) && ($boleto_dados['tipo_pag'] == $tipo)))
                        <option value="{{ $tipo }}">{{ $texto }}</option>
                        @endif
                    @endforeach
                    </select>
                    @if(!isset($pagamento) || (isset($pagamento) && $is_3ds))
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle text-primary"></i><em> 3DS é um protocolo de autenticação mais seguro para transações financeiras on-line</em>
                    </small>
                    @endif
                </div>
            </div>

            <div class="form-row mb-2 cadastroRepresentante">
                @if(!isset($pagamento) || (isset($pagamento) && isset($boleto_dados['amount_1']) && isset($boleto_dados['amount_2'])))
                <div class="col-sm mb-2-576" id="valor_combinado">
                    <label for="amount_1">Valor no primeiro cartão <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="amount_1"
                        class="form-control capitalSocial pagamento"
                        id="amount_1"
                        value="{{ isset($boleto_dados['amount_1']) ? $boleto_dados['amount_1'] : old('amount_1') }}"
                        @if(isset($pagamento))
                        readonly
                        @endif
                        required
                    >
                </div>
                @endif

                <div class="col-sm mb-2-576">
                    <label for="parcelas_1">Parcelas <span class="text-danger">*</span></label>
                    <select 
                        name="parcelas_1" 
                        class="form-control mb-2 mr-sm-3 pagamento" 
                        id="parcelas_1"
                        pattern="[0-9]{1,2}" 
                        title="Somente números e entre 1 e 2 dígitos"
                        required
                        @if(isset($pagamento))
                        readonly
                        @endif
                    >
                    @for($i = 1; $i < 11; $i++)
                        @if(!isset($pagamento) || (isset($pagamento) && ($boleto_dados['parcelas_1'] == $i)))
                        <option value="{{ $i }}">{{ $i == 1 ? 'à vista' : $i . 'x sem juros' }}</option>
                        @endif
                    @endfor
                    </select>
                </div>
            </div>

            @if(!isset($pagamento) || (isset($pagamento) && isset($boleto_dados['amount_1']) && isset($boleto_dados['amount_2'])))
            <div class="form-row mb-2 cadastroRepresentante" id="dados_combinado">
                <div class="col-sm mb-2-576">
                    <label for="amount_2">Valor no segundo cartão <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="amount_2"
                        class="form-control capitalSocial pagamento"
                        id="amount_2"
                        value="{{ isset($boleto_dados['amount_2']) ? $boleto_dados['amount_2'] : old('amount_2') }}"
                        @if(isset($pagamento))
                        readonly
                        @endif
                        required
                    >
                </div>

                <div class="col-sm mb-2-576">
                    <label for="parcelas_2">Parcelas <span class="text-danger">*</span></label>
                    <select 
                        name="parcelas_2" 
                        class="form-control mb-2 mr-sm-3 pagamento" 
                        id="parcelas_2"
                        pattern="[0-9]{1,2}" 
                        title="Somente números e entre 1 e 2 dígitos"
                        required
                        @if(isset($pagamento))
                        readonly
                        @endif
                    >
                    @for($i = 1; $i < 11; $i++)
                        @if(!isset($pagamento) || (isset($pagamento) && ($boleto_dados['parcelas_2'] == $i)))
                        <option value="{{ $i }}">{{ $i == 1 ? 'à vista' : $i . 'x sem juros' }}</option>
                        @endif
                    @endfor
                    </select>
                </div>
            </div>
            @endif

            @if(isset($pagamento) && !$checkoutIframe)
            <!-- <fieldset class="border rounded pl-3"> -->
            <div class="form-row mb-3 cadastroRepresentante mt-3">
                <div class="col-sm mb-2-576">
                    <img class="mr-3" src="{{ asset('img/visa.256x164.png') }}" width="48" height="31" alt="cartão visa"/>
                    <img class="mr-3" src="{{ asset('img/mastercard.256x164.png') }}" width="48" height="31" alt="cartão master"/>
                    <img class="mr-3" src="{{ asset('img/amex.256x168.png') }}" width="48" height="32" alt="cartão amex"/>
                    <img class="mr-3" src="{{ asset('img/elo.256x164.png') }}" width="48" height="31" alt="cartão elo"/>
                    @if(!$is_3ds)
                    <img class="mr-3" src="{{ asset('img/hipercard.256x112.png') }}" width="48" height="21" alt="cartão hipercard"/>
                    @endif
                </div>
            </div>
            <!-- </fieldset> -->

            <fieldset class="border rounded p-3">
                <legend class="m-0"><small>Dados do {{ $boleto_dados['tipo_pag'] == 'combined' ? 'primeiro' : null }} cartão:</small></legend>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="card_number_1">Número do cartão <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="card_number_1"
                            class="form-control form-control-sm cartao_credit pagamento"
                            id="card_number_1"
                            placeholder="XXXX  XXXX  XXXX  XXXX  XXX"
                            pattern="[0-9\s]{19,27}" 
                            title="Somente números e entre 13 e 19 dígitos"
                            required
                        >
                    </div>
                </div>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="cardholder_name_1">Nome do titular <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="cardholder_name_1"
                            class="form-control form-control-sm text-uppercase pagamento"
                            id="cardholder_name_1"
                            maxlength="26"
                            pattern="[A-z\s]{5,26}" 
                            title="Somente letras não acentuadas e entre 5 e 26 caracteres"
                            required
                        >
                        <small class="form-text text-muted">
                            <em>* Nome idêntico ao do cartão, sem acentos ou pontuações</em>
                        </small>
                    </div>
                </div>

                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="document_number_1">CPF / CNPJ <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="document_number_1"
                            class="form-control form-control-sm cpfOuCnpj pagamento"
                            id="document_number_1"
                            required
                        >
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="security_code_1">CVV / CVC <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="security_code_1"
                            class="form-control form-control-sm cvv pagamento"
                            id="security_code_1"
                            minlength="3"
                            maxlength="4"
                            pattern="[0-9]{3,4}" 
                            title="Somente números e entre 3 e 4 dígitos"
                            required
                        >
                        <small class="form-text text-muted">
                            <em>* Os 3 ou 4 números atrás do cartão</em>
                        </small>
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="expiration_1">Data de Expiração <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="expiration_1"
                            class="form-control form-control-sm pagamento expiracao"
                            id="expiration_1"
                            required
                        >
                    </div>
                </div>
            </fieldset>

            @if($boleto_dados['tipo_pag'] == 'combined')
            <fieldset class="border rounded p-3">
                <legend class="m-0"><small>Dados do segundo cartão:</small></legend>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="card_number_2">Número do cartão <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="card_number_2"
                            class="form-control form-control-sm cartao_credit pagamento"
                            id="card_number_2"
                            placeholder="XXXX  XXXX  XXXX  XXXX  XXX"
                            pattern="[0-9\s]{19,27}" 
                            title="Somente números e entre 13 e 19 dígitos"
                            required
                        >
                    </div>
                </div>
                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="cardholder_name_2">Nome do titular <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="cardholder_name_2"
                            class="form-control form-control-sm text-uppercase nome_cartao pagamento"
                            id="cardholder_name_2"
                            maxlength="26"
                            pattern="[A-z\s]{5,26}" 
                            title="Somente letras não acentuadas e entre 5 e 26 caracteres"
                            required
                        >
                        <small class="form-text text-muted">
                            <em>* Nome idêntico ao do cartão, sem acentos ou pontuações</em>
                        </small>
                    </div>
                </div>

                <div class="form-row mb-2 cadastroRepresentante">
                    <div class="col-sm mb-2-576">
                        <label for="document_number_2">CPF / CNPJ <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="document_number_2"
                            class="form-control form-control-sm cpfOuCnpj pagamento"
                            id="document_number_2"
                            required
                        >
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="security_code_2">CVV / CVC <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="security_code_2"
                            class="form-control form-control-sm cvv pagamento"
                            id="security_code_2"
                            minlength="3"
                            maxlength="4"
                            pattern="[0-9]{3,4}" 
                            title="Somente números e entre 3 e 4 dígitos"
                            required
                        >
                        <small class="form-text text-muted">
                            <em>* Os 3 ou 4 números atrás do cartão</em>
                        </small>
                    </div>

                    <div class="col-sm mb-2-576">
                        <label for="expiration_2">Data de Expiração <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="expiration_2"
                            class="form-control form-control-sm pagamento expiracao"
                            id="expiration_2"
                            required
                        >
                    </div>
                </div>
            </fieldset>
            @endif
            @endif

            <div class="form-group mt-4">
                @if(!$checkoutIframe)
                <button 
                    type="submit" 
                    class="btn btn-{{ isset($pagamento) ? 'success' : 'primary' }}"
                    @if(isset($pagamento))
                        data-toggle="modal" data-target="#modalPagamento" data-backdrop="static"
                    @endif
                >
                {{ isset($pagamento) ? 'Finalizar' : 'Confirmar dados para pagamento' }}
                </button>

                @else
                <input type="hidden" name="checkoutIframe" value="1">
                <input type="hidden" id="callbackURL" value="{{ route($user::NAME_ROUTE . '.dashboard') }}">
                
                <button 
                    type="{{ isset($pagamento) ? 'button' : 'submit' }}" 
                    class="btn btn-success {{ isset($pagamento) ? 'pay-button-getnet' : '' }}"
                >
                {{ isset($pagamento) ? 'Finalizar' : 'Confirmar dados para pagamento' }}
                </button>
                @endif

            </div>
        </form>
    @else
        @if(\Route::is('pagamento.cancelar.*'))
        <form action="{{ route('pagamento.cancelar', ['boleto' => $boleto, 'pagamento' => $id_pagamento]) }}" method="POST" autocomplete="off">
            @csrf
        @endif
            @foreach($dados as $dado)
                <p><strong>Boleto:</strong> {{ $dado->boleto_id }}</p>
                <p><strong>Status do pagamento:</strong> {!! $dado->getStatusLabel() !!}</p>
                <p><strong>Forma de pagamento:</strong> {{ $dado->getForma() }}</p>
                <p><strong>Parcelas:</strong> {{ $dado->getParcelas() . ' ' . $dado->getTipoParcelas() }}</p>
                <p><strong>Bandeira:</strong> {!! $dado->getBandeiraImg() !!}</p>
                <p><strong>Data do pagamento:</strong> {{ formataData($dado->created_at) }}</p>
                <br />
            @endforeach
            @if(\Route::is('pagamento.cancelar.*'))
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <button
                        type="submit"
                        value="Cancelar pagamento"
                        class="btn btn-danger"
                        data-toggle="modal" 
                        data-target="#modalPagamento" 
                        data-backdrop="static"
                    >
                    Cancelar pagamento
                    </button>
                </div>
            </div>
        </form>
        @endif
    @endif
    </div>
</div>

<!-- The Modal -->
<div class="modal" id="modalPagamento">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <!-- Modal body -->
      <div class="modal-body text-center">
        <div class="spinner-grow text-success"></div> <strong>Aguarde... {!! isset($cancelamento) ? 'realizando o cancelamento... ' : 'finalizando o pagamento... <i class="fas fa-lock"></i>' !!}</strong>
      </div>

    </div>
  </div>
</div>

@if(isset($pagamento) && $is_3ds && !$checkoutIframe)

<script src="{{ asset('getnet_3ds220.js') }}" type="text/javascript"></script>

<!-- config -->
<input type="hidden" id="gn3ds_merchantBackEndUrl" name="gn3ds_merchantBackEndUrl" class="gn3ds_merchantBackEndUrl" value="{{ route('site.home') }}/">
<input type="hidden" id="gn3ds_merchantBackEndTokenBasic" name="gn3ds_merchantBackEndTokenBasic" class="gn3ds_merchantBackEndTokenBasic" value="">
<input type="hidden" id="gn3ds_merchantBackEndTokenOauth" name="gn3ds_merchantBackEndTokenOauth" class="gn3ds_merchantBackEndTokenOauth" value="">
<input type="hidden" id="gn3ds_environment" name="gn3ds_environment" class="gn3ds_environment" value="{{ config('app.url') != 'https://core-sp.org.br' ? 'SDB' : 'PRD' }}">
<input type="hidden" id="gn3ds_debug" name="gn3ds_debug" class="gn3ds_debug" value="{{ config('app.url') != 'https://core-sp.org.br' ? 'true' : 'false' }}">
<input type="hidden" id="gn3ds_debugPrefix" name="gn3ds_debugPrefix" class="gn3ds_debugPrefix" value="{{ config('app.url') != 'https://core-sp.org.br' ? '[GN3DS]' : null }}">
<input type="hidden" id="gn3ds_frameworkModal" name="gn3ds_frameworkModal" class="gn3ds_frameworkModal" value="default">
<input type="hidden" id="gn3ds_newApiVersion" name="gn3ds_newApiVersion" class="gn3ds_newApiVersion" value="true">

<!-- checkout -->
<input type="hidden" id="gn3ds_currency" name="gn3ds_currency" class="gn3ds_currency" value="BRL">
<input type="hidden" id="gn3ds_totalAmount" name="gn3ds_totalAmount" class="gn3ds_totalAmount" value="1000">
<input type="hidden" id="gn3ds_billToAddress1" name="gn3ds_billToAddress1" class="gn3ds_billToAddress1" value="">
<input type="hidden" id="gn3ds_billToAddress2" name="gn3ds_billToAddress2" class="gn3ds_billToAddress2" value="">
<input type="hidden" id="gn3ds_billToAdministrativeArea" name="gn3ds_billToAdministrativeArea" class="gn3ds_billToAdministrativeArea" value="SP">
<input type="hidden" id="gn3ds_billToCountry" name="gn3ds_billToCountry" class="gn3ds_billToCountry" value="BR">
<input type="hidden" id="gn3ds_billToLocality" name="gn3ds_billToLocality" class="gn3ds_billToLocality" value="Sao Paulo">
<input type="hidden" id="gn3ds_billToHomePhone" name="gn3ds_billToHomePhone" class="gn3ds_billToHomePhone" value="11999999999">
<input type="hidden" id="gn3ds_billToEmail" name="gn3ds_billToEmail" class="gn3ds_billToEmail" value="">
<input type="hidden" id="gn3ds_billToPostalCode" name="gn3ds_billToPostalCode" class="gn3ds_billToPostalCode" value="04746050">
<input type="hidden" id="gn3ds_billToMobilePhone" name="gn3ds_billToMobilePhone" class="gn3ds_billToMobilePhone" value="11999999999">
<input type="hidden" id="gn3ds_cardType" name="gn3ds_cardType" class="gn3ds_cardType" value="">
<input type="hidden" id="gn3ds_cardExpirationMonth" name="gn3ds_cardExpirationMonth" class="gn3ds_cardExpirationMonth" value="01">
<input type="hidden" id="gn3ds_cardExpirationYear" name="gn3ds_cardExpirationYear" class="gn3ds_cardExpirationYear" value="2025">
<input type="hidden" id="gn3ds_cardNumber" name="gn3ds_cardNumber" class="gn3ds_cardNumber" value="5200000000001005">
<input type="hidden" id="gn3ds_cardHolderName" name="gn3ds_cardHolderName" class="gn3ds_cardHolderName" value="TESTE CARTAO">
<input type="hidden" id="gn3ds_overridePaymentMethod" name="gn3ds_overridePaymentMethod" class="gn3ds_overridePaymentMethod" value="03">
<input type="hidden" id="gn3ds_httpBrowserColorDepth" name="gn3ds_httpBrowserColorDepth" class="gn3ds_httpBrowserColorDepth" value="32">
<input type="hidden" id="gn3ds_httpBrowserJavaEnabled" name="gn3ds_httpBrowserJavaEnabled" class="gn3ds_httpBrowserJavaEnabled" value="N">
<input type="hidden" id="gn3ds_httpBrowserJavaScriptEnabled" name="gn3ds_httpBrowserJavaScriptEnabled" class="gn3ds_httpBrowserJavaScriptEnabled" value="Y">
<input type="hidden" id="gn3ds_httpBrowserLanguage" name="gn3ds_httpBrowserLanguage" class="gn3ds_httpBrowserLanguage" value="pt-BR">
<input type="hidden" id="gn3ds_httpBrowserScreenHeight" name="gn3ds_httpBrowserScreenHeight" class="gn3ds_httpBrowserScreenHeight" value="">
<input type="hidden" id="gn3ds_httpBrowserScreenWidth" name="gn3ds_httpBrowserScreenWidth" class="gn3ds_httpBrowserScreenWidth" value="">
<input type="hidden" id="gn3ds_httpBrowserTimeDifference" name="gn3ds_httpBrowserTimeDifference" class="gn3ds_httpBrowserTimeDifference" value="">
<input type="hidden" id="gn3ds_userAgentBrowserValue" name="gn3ds_userAgentBrowserValue" class="gn3ds_userAgentBrowserValue" value="">

<input type="hidden" id="gn3ds_personalId" name="gn3ds_personalId" class="gn3ds_personalId" value="00244376891">
<input type="hidden" id="gn3ds_personalType" name="gn3ds_personalType" class="gn3ds_personalType" value="CPF">
<input type="hidden" id="gn3ds_shipToAddress1" name="gn3ds_shipToAddress1" class="gn3ds_shipToAddress1" value="">
<input type="hidden" id="gn3ds_shipToAddress2" name="gn3ds_shipToAddress2" class="gn3ds_shipToAddress2" value="">
<input type="hidden" id="gn3ds_shipToAdministrativeArea" name="gn3ds_shipToAdministrativeArea" class="gn3ds_shipToAdministrativeArea" value="">
<input type="hidden" id="gn3ds_shipToCountry" name="gn3ds_shipToCountry" class="gn3ds_shipToCountry" value="">
<input type="hidden" id="gn3ds_shipToLocality" name="gn3ds_shipToLocality" class="gn3ds_shipToLocality" value="">
<input type="hidden" id="gn3ds_shipToFirstName" name="gn3ds_shipToFirstName" class="gn3ds_shipToFirstName" value="">
<input type="hidden" id="gn3ds_shipToLastName" name="gn3ds_shipToLastName" class="gn3ds_shipToLastName" value="">
<input type="hidden" id="gn3ds_shipToPostalCode" name="gn3ds_shipToPostalCode" class="gn3ds_shipToPostalCode" value="">
<input type="hidden" id="gn3ds_shipToDestinationCode" name="gn3ds_shipToDestinationCode" class="gn3ds_shipToDestinationCode" value="">
<input type="hidden" id="gn3ds_shipToMethod" name="gn3ds_shipToMethod" class="gn3ds_shipToMethod" value="">
<input type="hidden" id="gn3ds_item_#_totalAmount" name="gn3ds_item_#_totalAmount" class="gn3ds_item_#_totalAmount" value="1000">
<input type="hidden" id="gn3ds_item_#_unitPrice" name="gn3ds_item_#_unitPrice" class="gn3ds_item_#_unitPrice" value="1000">
<input type="hidden" id="gn3ds_item_#_quantity" name="gn3ds_item_#_quantity" class="gn3ds_item_#_quantity" value="1">
<input type="hidden" id="gn3ds_item_#_sku" name="gn3ds_item_#_sku" class="gn3ds_item_#_sku" value="1236AB">
<input type="hidden" id="gn3ds_item_#_description" name="gn3ds_item_#_description" class="gn3ds_item_#_description" value="testando">
<input type="hidden" id="gn3ds_item_#_name" name="gn3ds_item_#_name" class="gn3ds_item_#_name" value="boleto atrasado">
<input type="hidden" id="gn3ds_installmentTotalCount" name="gn3ds_installmentTotalCount" class="gn3ds_installmentTotalCount" value="1">
<input type="hidden" id="gn3ds_additionalData" name="gn3ds_additionalData" class="gn3ds_additionalData" value="">
<input type="hidden" id="gn3ds_additionalObject" name="gn3ds_additionalObject" class="gn3ds_additionalObject" value="">

@elseif(isset($pagamento) && $checkoutIframe)
<script async src="https://checkout-homologacao.getnet.com.br/loader.js"
    data-getnet-sellerid="{{ $pagamento['sellerid'] }}"
    data-getnet-token="{{ $pagamento['token'] }}"
    data-getnet-payment-methods-disabled='[{{$pagamento["disabled"]}}]'
    data-getnet-amount="{{ $pagamento['amount'] }}"
    data-getnet-customerid="{{ $pagamento['customerid'] }}"
    data-getnet-orderid="{{ $pagamento['orderid'] }}"
    data-getnet-button-class="pay-button-getnet"
    data-getnet-installments="{{ $pagamento['installments'] }}"
    data-getnet-customer-first-name="{{ $pagamento['first_name'] }}"
    data-getnet-customer-last-name="{{ $pagamento['last_name'] }}"
    data-getnet-customer-document-type="{{ $pagamento['document_type'] }}"
    data-getnet-customer-document-number="{{ $pagamento['document_number'] }}"
    data-getnet-customer-email="{{ $pagamento['email'] }}"
    data-getnet-customer-phone-number="{{ $pagamento['phone_number'] }}"
    data-getnet-customer-address-street="{{ $pagamento['address_street'] }}"
    data-getnet-customer-address-street-number="{{ $pagamento['address_street_number'] }}"
    data-getnet-customer-address-complementary="{{ $pagamento['address_complementary'] }}"
    data-getnet-customer-address-neighborhood="{{ $pagamento['address_neighborhood'] }}"
    data-getnet-customer-address-city="{{ $pagamento['address_city'] }}"
    data-getnet-customer-address-state="{{ $pagamento['address_state'] }}"
    data-getnet-customer-address-zipcode="{{ $pagamento['address_zipcode'] }}"
    data-getnet-customer-country="{{ $pagamento['country'] }}"
    data-getnet-items="[{{ $pagamento['items'] }}]"
    data-getnet-document-number="{{ $pagamento['document_number_boleto'] }}"
    data-getnet-our-number="{{ $pagamento['our_number'] }}"
    data-getnet-soft-descriptor="{{ $pagamento['soft_descriptor'] }}"
    data-getnet-dynamic-mcc="{{ $pagamento['dynamic_mcc'] }}"
    data-getnet-url-callback="{{-- $pagamento['callback'] --}}"
    data-getnet-pre-authorization-credit="">
</script>
@endif
