<?php

namespace App\Services;

use App\Contracts\PagamentoServiceInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Events\ExternoEvent;

class PagamentoGetnetService implements PagamentoServiceInterface {

    private $urlBase;
    private $client;
    private $auth;

    public function __construct()
    {
        $this->urlBase = config('app.url') != 'https://core-sp.org.br' ? 'https://api-homologacao.getnet.com.br' : '';
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
        $expiration = Carbon::createFromFormat('Y-m', $dados['expiration_1']);
        $dados['expiration_month_1'] = $expiration->format('m');
        $dados['expiration_year_1'] = $expiration->format('y');

        if($tipo != 'combined')
            $resultado['card'] = [
                "number_token" => $dados['number_token'],
                "cardholder_name" => $dados['cardholder_name_1'],
                "security_code" => $dados['security_code_1'],
                "brand" => $dados['brand'],
                "expiration_month" => $dados['expiration_month_1'],
                "expiration_year" => $dados['expiration_year_1']
            ];
        else
        {
            $expiration = Carbon::createFromFormat('Y-m', $dados['expiration_2']);
            $dados['expiration_month_2'] = $expiration->format('m');
            $dados['expiration_year_2'] = $expiration->format('y');
            for($i = 1; $i < 3; $i++)
                $resultado['card_' . $i] = [
                    "number_token" => $dados['number_token_' . $i],
                    "cardholder_name" => $dados['cardholder_name_' . $i],
                    "security_code" => $dados['security_code_' . $i],
                    "brand" => $dados['brand_' . $i],
                    "expiration_month" => $dados['expiration_month_' . $i],
                    "expiration_year" => $dados['expiration_year_' . $i]
                ];
        }
        
        switch($tipo){
            case 'debit':
                return [
                    "cardholder_mobile" => strtolower($dados["brand"]) == 'visa' ? apenasNumeros($dados["cardholder_mobile"]) : '',
                    "soft_descriptor" => $dados["soft_descriptor"],
                    "dynamic_mcc" => $dados['dynamic_mcc'],
                    "authenticated" => false,
                    'card' => $resultado['card'],
                ];
            case 'credit':
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
            case 'combined':
                return [
                    [
                        "type" => "CREDIT",
                        "amount" => apenasNumeros($dados['amount_1']),
                        "currency" => "BRL",
                        "save_card_data" => false,
                        "transaction_type" => $dados['tipo_parcelas_1'],
                        "number_installments" => $dados['parcelas_1'],
                        "payment_tag" => "pay-1",
                        "soft_descriptor" => $dados["soft_descriptor"],
                        'card' => $resultado['card_1'],
                    ],
                    [
                        "type" => "CREDIT",
                        "amount" => apenasNumeros($dados['amount_2']),
                        "currency" => "BRL",
                        "save_card_data" => false,
                        "transaction_type" => $dados['tipo_parcelas_2'],
                        "number_installments" => $dados['parcelas_2'],
                        "payment_tag" => "pay-2",
                        "soft_descriptor" => $dados["soft_descriptor"],
                        'card' => $resultado['card_2'],
                    ],
                ];
        }
    }

    private function getToken()
    {
        try{
            $this->client = new Client();
            $response = $this->client->request('POST', $this->urlBase . '/auth/oauth/v2/token', [
                'headers' => [
                    'Content-type' => "application/x-www-form-urlencoded",
                    'Authorization' => "Basic " . base64_encode(env('GETNET_CLIENT_ID') . ':' . env('GETNET_CLIENT_SECRET'))
                ],
                'form_params' => [
                    'scope' => "oob",
                    'grant_type' => "client_credentials",
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        $this->auth = json_decode($response->getBody()->getContents(), true);
    }

    private function tokenizacao($card_number, $customer_id)
    {
        try{
            $response = $this->client->request('POST', $this->urlBase . '/v1/tokens/card', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
                    'seller_id' => env('GETNET_SELLER_ID')
                ],
                'json' => [
                    'card_number' => $card_number,
                    'customer_id' => $customer_id,
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function verifyCard($number_token, $brand, $cardholder_name, $expiration, $security_code)
    {
        try{
            $expiration = Carbon::createFromFormat('Y-m', $expiration);
            $expiration_month = $expiration->format('m');
            $expiration_year = $expiration->format('y');

            $response = $this->client->request('POST', $this->urlBase . '/v1/cards/verification', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
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
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function pagamento($ip, $dados)
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
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
                ],
                'json' => $dadosFinais,
            ]);

            unset($dados);
            unset($dadosFinais);
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function pagamentoCombinado($ip, $dados)
    {
        try{
            $this->getToken();
            $dados['brand_1'] = $this->bin(substr($dados['card_number_1'], 0, 6))['results'][0]['brand'];
            $dados['number_token_1'] = $this->tokenizacao($dados['card_number_1'], $dados['customer_id'])['number_token'];
            $verify1 = $this->verifyCard($dados['number_token_1'], $dados['brand_1'], $dados['cardholder_name_1'], $dados['expiration_1'], $dados['security_code_1']);

            $dados['brand_2'] = $this->bin(substr($dados['card_number_2'], 0, 6))['results'][0]['brand'];
            $dados['number_token_2'] = $this->tokenizacao($dados['card_number_2'], $dados['customer_id'])['number_token'];
            $verify2 = $this->verifyCard($dados['number_token_2'], $dados['brand_2'], $dados['cardholder_name_2'], $dados['expiration_2'], $dados['security_code_2']);

            if(($verify1['status'] != 'VERIFIED') || ($verify2['status'] != 'VERIFIED'))
                return [
                    'message-cartao' => '<i class="fas fa-ban"></i> Cartão não é válido. Pagamento do boleto ' . $dados['boleto'] . ' não realizado.',
                    'class' => 'alert-danger',
                    'retorno_getnet' => 'Retorno da verificação do cartão 1: ' . $verify1['status'] . ' e do cartão 2: ' . $verify2['status']
                ];

            $dadosFinais = $this->dadosBasicosPag($ip, $dados);
            $dadosFinais['payments'] = $this->tipoPagamento($dados['tipo_pag'], $dados);
            unset($dados);

            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/combined', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => $dadosFinais
            ]);

            unset($dadosFinais);
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function cancelarPagamento($payment_id, $tipo_pag)
    {
        try{
            $this->getToken();
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/' . $tipo_pag . '/' . $payment_id . '/cancel', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
                ]
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function cancelarPagamentoCombinado($payment_id, $payment_tag)
    {
        try{
            $this->getToken();
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/combined/cancel', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
                    'seller_id' => env('GETNET_SELLER_ID'),
                ],
                'json' => [
                    'payments' => [
                        [
                            'payment_id' => $payment_id,
                            'payment_tag' => $payment_tag,
                        ],
                    ],
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function checkout($ip, $dados, $rep)
    {
        $boleto = $dados['boleto'];
        $tipo_pag = $dados['tipo_pag'];
        $transacao = $tipo_pag != 'combined' ? $this->pagamento($ip, $dados) : $this->pagamentoCombinado($ip, $dados);
        unset($dados);
        
        if(isset($transacao['message-cartao']))
        {
            $string = 'Usuário ' . $rep->id . ' ("'. $rep->registro_core .'") tentou realizar o pagamento do boleto ' . $boleto . ' do tipo *' . $tipo_pag . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: ' . $transacao['retorno_getnet'];
            event(new ExternoEvent($string));
            return $transacao;
        }

        if($transacao['status'] != 'APPROVED')
        {
            $string = 'Usuário ' . $rep->id . ' ("'. $rep->registro_core .'") tentou realizar o pagamento do boleto ' . $boleto . ' do tipo *' . $tipo_pag . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: Cartão verificado, mas ao realizar o pagamento o status recebido foi' . $transacao['status'];
            event(new ExternoEvent($string));
            return [
                'message-cartao' => '<i class="fas fa-ban"></i> Status da transação: ' . $transacao['status'] . '. Pagamento do boleto ' . $boleto . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        // Salvar dados
        // Enviar email com os detalhes do pagamento
        $string = 'Usuário ' . $rep->id . ' ("'. $rep->registro_core .'") realizou pagamento do boleto ' . $boleto . ' do tipo *' . $tipo_pag . '* com a payment_id: ' . $transacao['payment_id'];
        $string .= '. [Dados Request:' . json_encode(request()->all()) . ']; [Dados Session:' . json_encode(session()->all()) . ']; [Dados Cache:' . json_encode(cache()) . ']';
        event(new ExternoEvent($string));
        unset($transacao);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Pagamento Aprovado para o boleto ' . $boleto . '.<br>Detalhes do pagamento enviado por e-mail.',
            'class' => 'alert-success'
        ];
    }

    public function cancelCheckout($dados, $rep)
    {
        $boleto = $dados['boleto'];
        $tipo_pag = $dados['tipo_pag'];
        $transacao = $tipo_pag != 'combined' ? $this->cancelarPagamento($dados['payment_id'], $tipo_pag) : 
        $this->cancelarPagamentoCombinado($dados['payment_id'], $dados['payment_tag']);
        unset($dados);
        
        if($transacao['status'] != 'CANCELED')
        {
            $message = '';
            if($tipo_pag == 'combined')
                $message = isset($transacao['payments']['credit_cancel']['message']) ? $transacao['payments']['credit_cancel']['message'] : 'status ' . $transacao['status'];
            else
                $message = isset($transacao[$tipo_pag . '_cancel']['message']) ? $transacao[$tipo_pag . '_cancel']['message'] : 'status ' . $transacao['status'];
            
            $string = 'Usuário ' . $rep->id . ' ("'. $rep->registro_core .'") tentou realizar o cancelamento do pagamento do boleto ' . $boleto . ' do tipo *' . $tipo_pag . '* com a payment_id: ' . $dados['payment_id'];
            $string .= ', mas não foi possível. Retorno da Getnet: ' . $message;
            event(new ExternoEvent($string));
            return [
                'message-cartao' => '<i class="fas fa-ban"></i> Status do cancelamento da transação: ' . $transacao['status'] . '. Cancelamento do pagamento do boleto ' . $boleto . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        // Salvar dados
        // Enviar email com os detalhes do pagamento
        $string = 'Usuário ' . $rep->id . ' ("'. $rep->registro_core .'") realizou o cancelamento do pagamento do boleto ' . $boleto . ' do tipo *' . $tipo_pag . '* com a payment_id: ' . $transacao['payment_id'];
        event(new ExternoEvent($string));
        unset($transacao);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Cancelamento do pagamento do boleto ' . $boleto . ' aprovado.<br>Detalhes do cancelamento do pagamento enviado por e-mail.',
            'class' => 'alert-success'
        ];
    }

    public function bin($bin)
    {
        try{
            $this->getToken();
            $response = $this->client->request('GET', $this->urlBase . '/v1/cards/binlookup/' . $bin, [
                'headers' => [
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
                ]
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            $erroGetnet = $e->getMessage();
            if($e->hasResponse())
            {
                $codigo = $e->getResponse()->getStatusCode();
                $erroGetnet = $e->getResponse()->getBody()->getContents();
            }
                
            throw new \Exception('Retorno Erro Getnet: ' . $erroGetnet, $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function formatPagCheckoutIframe($request)
    {
        $this->getToken();

        $pagamento = $request;
        $pagamento['sellerid'] = env('GETNET_SELLER_ID');
        $pagamento['token'] = $this->auth['token_type'] . ' ' . $this->auth['access_token'];
        $pagamento['amount'] = '150.23';
        $pagamento['customerid'] = '12345';
        $pagamento['orderid'] = '12345';
        $pagamento['installments'] = '1';
        $pagamento['first_name'] = 'João';
        $pagamento['last_name'] = 'da Silva';
        $pagamento['document_type'] = 'CPF';
        $pagamento['document_number'] = '22233366638';
        $pagamento['email'] = 'teste@getnet.com.br';
        $pagamento['phone_number'] = '1134562356';
        $pagamento['address_street'] = 'Rua Alexandre Dumas';
        $pagamento['address_street_number'] = '1711';
        $pagamento['address_complementary'] = '';
        $pagamento['address_neighborhood'] = 'Chacara Santo Antonio';
        $pagamento['address_city'] = 'São Paulo';
        $pagamento['address_state'] = 'SP';
        $pagamento['address_zipcode'] = '04717004';
        $pagamento['country'] = 'BR';
        // $pagamento['our_number'] = '150.23';
        // $pagamento['document_number'] = '150.23';

        return $pagamento;
    }
}