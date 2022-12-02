<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;

class PagamentoGetnetApiService {

    private $urlBase;
    private $client;
    private $auth;
    private $total_cartoes;
    private $formatDt;
    private $tokenPrincipal;

    public function __construct($total_cartoes, $formatDt)
    {
        $this->urlBase = config('app.url') != 'https://core-sp.org.br' ? 'https://api-homologacao.getnet.com.br' : '';
        $this->total_cartoes = $total_cartoes;
        $this->formatDt = $formatDt;
        $this->tokenPrincipal = "Basic " . base64_encode(env('GETNET_CLIENT_ID') . ':' . env('GETNET_CLIENT_SECRET'));
    }

    private function formatError(RequestException $e)
    {
        $codigo = 0;
        $erroGetnet = $e->getMessage();
        if($e->hasResponse())
        {
            $codigo = $e->getResponse()->getStatusCode();
            $erroGetnet = $e->getResponse()->getBody()->getContents();
        }
            
        throw new \Exception($erroGetnet, $codigo);
    }

    private function finalAutentication3ds($response)
    {
        $resultado = json_decode($response->getBody()->getContents(), true);
        $final = [
            'status' => $response->getStatusCode(),
            'message' => 'SUCCESSFUL',
            'error' => []
        ];

        $temp = array();
        foreach($resultado as $key => $value)
        {
            if(is_array($value))
                foreach($value as $key2 => $val2)
                    $temp[Str::camel($key)][Str::camel($key2)] = $val2;
            else
                $temp[Str::camel($key)] = $value;
        }
            
        $final['data'] = [$temp];
        return $final;
    }

    private function tipoPagamento3ds($dados, $resultado)
    {
        return [
            "order_id" => $dados["order_id"],
            "amount" => $dados["amount"],
            "currency" => "BRL",
            "transaction_type" => $dados["tipo_parcelas_1"],
            "number_installments" => $dados["parcelas_1"],
            "xid" => $dados["xid"],
            "ucaf" => $dados["ucaf"],
            "eci" => $dados["eci"],
            "tdsdsxid" => $dados["tdsdsxid"],
            "tdsver" => $dados["tdsver"],
            "payment_method" => $dados["tipo_pag"] == 'credit_3ds' ? 'CREDIT' : 'DEBIT',
            "soft_descriptor" => $dados["soft_descriptor"],
            "dynamic_mcc" => $dados['dynamic_mcc'],
            "customer_id" => $dados['customer_id'],
            "credentials_on_file_type" => '',
            'card' => $resultado['card'],
        ];
    }

    private function tipoPagamentoCombinado($dados, $resultado)
    {
        $array = array();
        for($i = 1; $i <= $this->total_cartoes; $i++)
            array_push($array, [
                "type" => "CREDIT",
                "amount" => apenasNumeros($dados['amount_' . $i]),
                "currency" => "BRL",
                "save_card_data" => false,
                "transaction_type" => $dados['tipo_parcelas_' . $i],
                "number_installments" => $dados['parcelas_' . $i],
                "payment_tag" => "pay-" . $i,
                "soft_descriptor" => $dados["soft_descriptor"],
                'card' => $resultado['card_' . $i],
            ]);
        return $array;
    }

    private function tipoPagamentoCredito($dados, $resultado)
    {
        return [
            "delayed" => false,
            "pre_authorization" => false,
            "save_card_data" => false,
            "transaction_type" => $dados['tipo_parcelas_1'],
            "number_installments" => $dados['parcelas_1'],
            "soft_descriptor" => $dados["soft_descriptor"],
            "dynamic_mcc" => $dados['dynamic_mcc'],
            'card' => $resultado['card'],
        ];
    }

    private function tipoPagamentoArrayCard($tipo, $dados)
    {
        $resultado = array();

        switch($tipo){
            case 'combined':
                for($i = 1; $i <= $this->total_cartoes; $i++)
                {
                    $expiration = Carbon::createFromFormat($this->formatDt, $dados['expiration_'.$i]);
                    $resultado['card_' . $i] = [
                        "number_token" => $dados['number_token_' . $i],
                        "cardholder_name" => $dados['cardholder_name_' . $i],
                        "security_code" => $dados['security_code_' . $i],
                        "brand" => $dados['brand_' . $i],
                        "expiration_month" => $expiration->format('m'),
                        "expiration_year" => $expiration->format('y')
                    ];
                }
                return $resultado;
            default:
                $expiration = Carbon::createFromFormat($this->formatDt, $dados['expiration_1']);
                $resultado['card'] = [
                    "number_token" => $dados['number_token'],
                    "cardholder_name" => $dados['cardholder_name_1'],
                    "security_code" => $dados['security_code_1'],
                    "brand" => $dados['brand'],
                    "expiration_month" => $expiration->format('m'),
                    "expiration_year" => $expiration->format('y')
                ];
                return $resultado;
        }
    }

    private function dadosBasicosPag($ip, $dados)
    {
        return [
            'seller_id' => env('GETNET_SELLER_ID'),
            'amount' => apenasNumeros($dados['amount']),
            'currency' => "BRL",
            'order' => [
                "order_id" => $dados['order_id'],
                "sales_tax" => $dados['sales_tax'],
                "product_type" => "service"
            ],
            'customer' => [
                "customer_id" => $dados['customer_id'],
                "first_name" => $dados['first_name'],
                "last_name" => $dados['last_name'],
                "name" => $dados['name'],
                "email" => $dados['email'],
                "document_type" => $dados['document_type'],
                "document_number" => apenasNumeros($dados['document_number']),
                "phone_number" => apenasNumeros($dados['phone_number']),
                'billing_address' => [
                    "street" => $dados['ba_street'],
                    "number" => $dados['ba_number'],
                    "complement" => $dados['ba_complement'],
                    "district" => $dados['ba_district'],
                    "city" => $dados['ba_city'],
                    "state" => $dados['ba_state'],
                    "country" => $dados['ba_country'],
                    "postal_code" => $dados['ba_postal_code']
                ],
            ],
            'device' => [
                "ip_address" => $ip,
                "device_id" => $dados['device_id']
            ],
            'shippings' => [
                // [
                //     "first_name" => "João",
                //     "name" => "João da Silva",
                //     "email" => "customer@email.com.br",
                //     "phone_number" => "5551999887766",
                //     "shipping_amount" => 0,
                //     "address" =>  [
                //         "street" => "Av. Brasil",
                //         "number" => "1000",
                //         "complement" => "Sala 1",
                //         "district" => "São Geraldo",
                //         "city" => "Porto Alegre",
                //         "state" => "RS",
                //         "country" => "Brasil",
                //         "postal_code" => "90230060"
                //     ],
                // ]
            ],
            'sub_merchant' => [
                "identification_code" => $dados['sm_identification_code'],
                "document_type" => "CNPJ",
                "document_number" => apenasNumeros($dados['sm_document_number']),
                "address" => $dados['sm_address'],
                "city" => $dados['sm_city'],
                "state" => $dados['sm_state'],
                "postal_code" => $dados['sm_postal_code']
            ],
        ];
    }

    private function tipoPagamento($tipo, $dados)
    {
        $resultado = $this->tipoPagamentoArrayCard($tipo, $dados);
        switch($tipo){
            case 'credit_3ds':
            case 'debit_3ds':
                return $this->tipoPagamento3ds($dados, $resultado);
            case 'credit':
                return $this->tipoPagamentoCredito($dados, $resultado);
            case 'combined':
                return $this->tipoPagamentoCombinado($dados, $resultado);
        }
    }

    private function getToken()
    {
        try{
            $this->client = new Client();
            $response = $this->client->request('POST', $this->urlBase . '/auth/oauth/v2/token', [
                'headers' => [
                    'Content-type' => "application/x-www-form-urlencoded",
                    'Authorization' => $this->tokenPrincipal
                ],
                'form_params' => [
                    'scope' => "oob",
                    'grant_type' => "client_credentials",
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        $final = json_decode($response->getBody()->getContents(), true);
        $this->auth = $final['token_type'] . ' ' . $final['access_token'];
    }

    private function tokenizacao($card_number, $customer_id)
    {
        try{
            $response = $this->client->request('POST', $this->urlBase . '/v1/tokens/card', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    'seller_id' => env('GETNET_SELLER_ID')
                ],
                'json' => [
                    'card_number' => $card_number,
                    'customer_id' => $customer_id,
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function verifyCard($number_token, $brand, $cardholder_name, $expiration, $security_code)
    {
        try{
            $expiration = Carbon::createFromFormat($this->formatDt, $expiration);
            $expiration_month = $expiration->format('m');
            $expiration_year = $expiration->format('y');

            $response = $this->client->request('POST', $this->urlBase . '/v1/cards/verification', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    'seller_id' => env('GETNET_SELLER_ID')
                ],
                'json' => [
                    'number_token' => $number_token,
                    'brand' => $brand,
                    'cardholder_name' => $cardholder_name,
                    'expiration_month' => $expiration_month,
                    'expiration_year' => $expiration_year,
                    'security_code' => $security_code,
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function pagamento($ip, $dados)
    {
        try{
            $this->getToken();
            $dados['brand'] = $this->bin(substr($dados['card_number_1'], 0, 6))['results'][0]['brand'];
            $dados['number_token'] = $this->tokenizacao($dados['card_number_1'], $dados['customer_id'])['number_token'];
            $verify = $this->verifyCard($dados['number_token'], $dados['brand'], $dados['cardholder_name_1'], $dados['expiration_1'], $dados['security_code_1']);

            if($verify['status'] != 'VERIFIED')
                return [
                    'message-cartao' => '<i class="fas fa-ban"></i> Cartão não é válido. Pagamento do boleto ' . $dados['boleto'] . ' não realizado.',
                    'class' => 'alert-danger',
                    'retorno_getnet' => 'Retorno da verificação do cartão 1: ' . $verify['status']
                ];

            $dadosFinais = $this->dadosBasicosPag($ip, $dados);
            $dadosFinais[$dados['tipo_pag']] = $this->tipoPagamento($dados['tipo_pag'], $dados);

            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/' . $dados['tipo_pag'], [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                ],
                'json' => $dadosFinais,
            ]);

            unset($dados);
            unset($dadosFinais);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function pagamentoCombinado($ip, $dados)
    {
        try{
            $this->getToken();
            $temp = array();
            for($i = 1; $i <= $this->total_cartoes; $i++)
            {
                $dados['brand_'.$i] = $this->bin(substr($dados['card_number_'.$i], 0, 6))['results'][0]['brand'];
                $dados['number_token_'.$i] = $this->tokenizacao($dados['card_number_'.$i], $dados['customer_id'])['number_token'];
                $verify = $this->verifyCard($dados['number_token_'.$i], $dados['brand_'.$i], $dados['cardholder_name_'.$i], $dados['expiration_'.$i], $dados['security_code_'.$i]);
                array_push($temp, $verify['status']);
            }
            
            if(($temp[0] != 'VERIFIED') || ($temp[1] != 'VERIFIED'))
                return [
                    'message-cartao' => '<i class="fas fa-ban"></i> Cartão não é válido. Pagamento do boleto ' . $dados['boleto'] . ' não realizado.',
                    'class' => 'alert-danger',
                    'retorno_getnet' => 'Retorno da verificação do cartão 1: ' . $temp[0] . ' e do cartão 2: ' . $temp[1]
                ];

            $dadosFinais = $this->dadosBasicosPag($ip, $dados);
            unset($dadosFinais['sub_merchant']);
            unset($dadosFinais['currency']);
            $dadosFinais['payments'] = $this->tipoPagamento($dados['tipo_pag'], $dados);
            unset($dados);

            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/combined', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => $dadosFinais
            ]);

            unset($dadosFinais);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function pagamento3DS($dados)
    {
        try{
            $dadosFinais = $this->tipoPagamento($dados['tipo_pag'], $dados);
            $this->client = new Client();
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/authenticated', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => '$dadosHeader',
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => $dadosFinais,
            ]);

            unset($dados);
            unset($dadosFinais);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function confirmation($payment_ids, $payment_tags)
    {
        try{
            $array = array();
            for($i = 0; $i < $this->total_cartoes; $i++)
                if(isset($payment_ids[$i]))
                    array_push($array, [
                        'payment_id' => $payment_ids[$i],
                        'payment_tag' => $payment_tags[$i],
                    ]);
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/combined/confirm', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => [
                    'payments' => $array,
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function cancelarPagamento($payment_id, $tipo_pag)
    {
        try{
            $temp = strpos($tipo_pag, '_3ds') !== false ? 'authenticated' : 'credit';
            $this->getToken();
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/' . $temp . '/' . $payment_id . '/cancel', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Cache-control' => 'no-cache',
                    'Authorization' => $this->auth,
                ],
                'json' => $temp == 'authenticated' ? ['payment_method' => strtoupper(str_replace('_3ds', '', $tipo_pag))] : [],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function cancelarPagamentoCombinado($payment_ids, $payment_tags)
    {
        try{
            $array = array();
            for($i = 0; $i < $this->total_cartoes; $i++)
                if(isset($payment_ids[$i]))
                    array_push($array, [
                        'payment_id' => $payment_ids[$i],
                        'payment_tag' => $payment_tags[$i],
                    ]);
            $this->getToken();
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/combined/cancel', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => [
                    'payments' => $array,
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function bin($bin)
    {
        try{
            $this->getToken();
            $response = $this->client->request('GET', $this->urlBase . '/v1/cards/binlookup/' . $bin, [
                'headers' => [
                    'Authorization' => $this->auth,
                ]
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getDados3DS($bin)
    {
        $dados = null;
        $card_type = ['visa' => '001', 'mastercard' => '002', 'amex' => '003', 'elo' => '054'];

        try{
            $temp = $this->bin($bin);

            $dados['brand'] = $temp['results'][0]['brand'];
            $dados['token'] = $this->auth;
            $dados['token_principal'] = $this->tokenPrincipal;
            $brand = mb_strtolower($dados['brand']);
            $dados['card_type'] = isset($card_type[$brand]) ? $card_type[$brand] : '';
        }catch(RequestException $e){
            $this->formatError($e);
        }
        
        return $dados;
    }

    public function generateToken3DS($dados)
    {
        try{
            $this->client = new Client();
            $this->auth = $dados['Authorization'];
            $response = $this->client->request('POST', $this->urlBase . '/v1/3ds/tokens', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => [
                    'client_code' => '1111',
                    'currency' => 'BRL',
                    'items' => [],
                    'js_version' => $dados['version'],
                    'order_number' => '1234-2019',
                    'override_payment_method' => $dados['consumerAuthenticationInformation']['overridePaymentMethod'],
                    'total_amount' => (int) $dados['totalAmount'],
                    'additional_data' => $dados['additionalData'],
                    'additional_object' => $dados['additionalObject'],
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return $this->finalAutentication3ds($response);
    }

    public function authentication3DS($dados)
    {
        $billTo = array();
        if(isset($dados['orderInformation']['billTo']))
            foreach($dados['orderInformation']['billTo'] as $key => $val)
                $billTo[Str::snake($key)] = $val;

        unset($billTo['home_phone']);

        $shipTo = array();
        if(isset($dados['orderInformation']['shipTo']))
            foreach($dados['orderInformation']['shipTo'] as $key => $val)
                $shipTo[Str::snake($key)] = $val;

        try{
            $this->client = new Client();
            $this->auth = $dados['Authorization'];
            $number_token = $this->tokenizacao($dados['paymentInformation']['card']['number'], $dados['personalIdentification']['id']);
            $response = $this->client->request('POST', $this->urlBase . '/v1/3ds/authentications', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    // 'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => [
                    'customer_card_alias' => '',
                    'override_payment_method' => $dados['consumerAuthenticationInformation']['overridePaymentMethod'],
                    'alternate_authentication_method' => '',
                    'authentication' => [
                        'token' => $dados['token'],
                        'npa_code' => '',
                        'challenge_code' => '01',
                        'installment_total_count' => (int) $dados['consumerAuthenticationInformation']['installmentTotalCount'],
                        'message_category' => '',
                        'transaction_mode' => '',
                        'device_channel' => '',
                        'acs_window_size' => '02',
                    ],
                    'device' => [
                        'http_accept_browser_value' => '',
                        'http_accept_content' => '',
                        'user_agent_browser_value' => $dados['deviceInformation']['userAgentBrowserValue'],
                        'http_browser_color_depth' => $dados['deviceInformation']['httpBrowserColorDepth'],
                        'http_browser_java_enabled' => $dados['deviceInformation']['httpBrowserJavaEnabled'],
                        'http_browser_java_script_enabled' => $dados['deviceInformation']['httpBrowserJavaScriptEnabled'],
                        'http_browser_language' => $dados['deviceInformation']['httpBrowserLanguage'],
                        'http_browser_screen_height' => $dados['deviceInformation']['httpBrowserScreenHeight'],
                        'http_browser_screen_width' => $dados['deviceInformation']['httpBrowserScreenWidth'],
                        'http_browser_time_difference' => $dados['deviceInformation']['httpBrowserTimeDifference'],
                        'ip_address' => $dados['ip_address'],
                    ],
                    'costumer_risk_information' => [
                        'transaction_count_year' => 0,
                        'transaction_count_day' => 0,
                        'add_card_attempts' => 0,
                        'customer_id' => $dados['personalIdentification']['id'],
                        'customer_type_id' => $dados['personalIdentification']['type'],
                        'payment_account_history' => '',
                        'payment_account_date' => '',
                        'prior_suspicious_activity' => false,
                    ],
                    // 'recurring' => [
                    //     'end_date' => '',
                    //     'frequency' => 0,
                    //     'original_purchase_date' => '',
                    // ],
                    'card' => [
                        'number_token' => $number_token['number_token'],
                        'expiration_month' => $dados['paymentInformation']['card']['expirationMonth'],
                        'expiration_year' => $dados['paymentInformation']['card']['expirationYear'],
                        'default_card' => true,
                        'type_card' => $dados['paymentInformation']['card']['type'],
                    ],
                    'order' => [
                        'product_code' => '01',
                        'currency' => $dados['orderInformation']['amountDetails']['currency'],
                        'total_amount' => (int) $dados['orderInformation']['amountDetails']['totalAmount'],
                        'bill_to' => $billTo,
                        'ship_to' => $shipTo,
                        'items' => [],
                    ],
                    'additional_data' => $dados['additionalData'],
                    'additional_object' => $dados['additionalObject'],
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return $this->finalAutentication3ds($response);
    }

    public function authenticationResults3DS($dados)
    {
        try{
            \Log::error($dados);
            $this->client = new Client();
            $this->auth = $dados['Authorization'];
            $response = $this->client->request('POST', $this->urlBase . '/v1/3ds/results', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth,
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => [
                    'currency' => 'BRL',
                    'override_payment_method' => $dados['consumerAuthenticationInformation']['overridePaymentMethod'],
                    'token' => $dados['token'],
                    'token_challenge' => '',
                    'total_amount' => 0,
                    'card' => [
                        'number_token' => '',
                        'expiration_month' => $dados['paymentInformation']['card']['expirationMonth'],
                        'expiration_year' => $dados['paymentInformation']['card']['expirationYear'],
                        'default_card' => true,
                        'type_card' => $dados['paymentInformation']['card']['type'],
                    ],
                    'additional_data' => $dados['additionalData'],
                    'additional_object' => $dados['additionalObject'],
                ],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return $this->finalAutentication3ds($response);
    }

    public function checkoutIframe($request, $user)
    {
        $this->getToken();
        
        $array_disabled = [
            'credit' => strpos($request['tipo_pag'], 'credit_3ds') === false ? "credito-nao-autenticado" : "credito-autenticado", 
            'debit_3ds' => "debito-autenticado", 
        ];

        $tipo_pag = strpos($request['tipo_pag'], 'credit_3ds') === false ? $request['tipo_pag'] : 'credit';
        if(strpos($tipo_pag, 'debit') !== false)
            $array_disabled['credit'] = "credito";

        $disabled = '"debito-nao-autenticado","boleto","qr-code","pix",';
        unset($array_disabled[$tipo_pag]);

        $i = 0;
        foreach($array_disabled as $key => $valor)
            $disabled .= ++$i == count($array_disabled) ? '"' . $valor . '"' : '"' . $valor . '",';

        $pagamento = $request;
        $pagamento['sellerid'] = env('GETNET_SELLER_ID');
        $pagamento['token'] = $this->auth;
        $pagamento['amount'] = substr_replace($request['valor'], '.', strlen($request['valor']) - 2, 0);
        $pagamento['customerid'] = $user->getCustomerId();
        $pagamento['orderid'] = $request['boleto'];
        $pagamento['installments'] = $request['parcelas_1'];
        $pagamento['disabled'] = $disabled;
        $pagamento['callback'] = route($user::NAME_ROUTE . '.dashboard');

        return $pagamento;
    }
}