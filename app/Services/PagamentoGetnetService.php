<?php

namespace App\Services;

use App\Contracts\PagamentoServiceInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

class PagamentoGetnetService implements PagamentoServiceInterface {

    private $urlBase;
    private $client;

    public function __construct()
    {
        $this->urlBase = config('app.env') != 'production' ? 'https://api-homologacao.getnet.com.br' : '';
    }

    public function getToken()
    {
        try{
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
            if($e->hasResponse())
                $codigo = $e->getResponse()->getStatusCode();
            throw new \Exception('Retorno Erro Getnet: ' . $e->getMessage(), $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function tokenizacao($auth)
    {
        try{
            $response = $this->client->request('POST', $this->urlBase . '/v1/tokens/card', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $auth['token_type'] . ' ' . $auth['access_token'],
                    'seller_id' => env('GETNET_SELLER_ID')
                ],
                'json' => [
                    'card_number' => "4012001037141112",
                    'customer_id' => "customer_21081826",
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            if($e->hasResponse())
                $codigo = $e->getResponse()->getStatusCode();
            throw new \Exception('Retorno Erro Getnet: ' . $e->getMessage(), $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function verifyCard($auth, $number_token)
    {
        try{
            $response = $this->client->request('POST', $this->urlBase . '/v1/cards/verification', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $auth['token_type'] . ' ' . $auth['access_token'],
                    'seller_id' => env('GETNET_SELLER_ID')
                ],
                'json' => [
                    'number_token' => $number_token,
                    'brand' => "Visa",
                    'cardholder_name' => "JOAO DA SILVA",
                    'expiration_month' => "12",
                    'expiration_year' => "28",
                    'security_code' => "123",
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            if($e->hasResponse())
                $codigo = $e->getResponse()->getStatusCode();
            throw new \Exception('Retorno Erro Getnet: ' . $e->getMessage(), $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function pagamentoDebito($ip)
    {
        try{
            $this->client = new Client();
            $auth = $this->getToken();
            $number_token = $this->tokenizacao($auth)['number_token'];
            $verify = $this->verifyCard($auth, $number_token);
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/debit', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $auth['token_type'] . ' ' . $auth['access_token'],
                ],
                'json' => [
                    'seller_id' => env('GETNET_SELLER_ID'),
                    'amount' => '1500',
                    'currency' => "BRL",
                    'order' => [
                        "order_id" => "6d2e4380-d8a3-4ccb-9138-c289182818a3",
                    ],
                    'customer' => [
                        "customer_id" => "customer_21081826",
                        "first_name" => "João",
                        "last_name" => "da Silva",
                        "name" => "João da Silva",
                        "email" => "customer@email.com.br",
                        "document_type" => "CPF",
                        "document_number" => "12345678912",
                        "phone_number" => "5551999887766",
                        'billing_address' => [
                            "street" => "Av. Brasil",
                            "number" => "1000",
                            "complement" => "Sala 1",
                            "district" => "São Geraldo",
                            "city" => "Porto Alegre",
                            "state" => "RS",
                            "country" => "Brasil",
                            "postal_code" => "90230060"
                        ],
                    ],
                    'device' => [
                        "ip_address" => $ip,
                        "device_id" => "123456123456"
                    ],
                    'shippings' => [
                    ],
                    'sub_merchant' => [
                        "identification_code" => "9058344",
                        "document_type" => "CNPJ",
                        "document_number" => "20551625000159",
                        "address" => "Torre Negra 44",
                        "city" => "Cidade",
                        "state" => "RS",
                        "postal_code" => "90520000"
                    ],
                    'debit' => [
                        "cardholder_mobile" => "5551999887766",
                        "soft_descriptor" => "LOJA*TESTE*COMPRA-123",
                        "authenticated" => false,
                        'card' => [
                            "number_token" => $number_token,
                            "cardholder_name" => "JOAO DA SILVA",
                            "security_code" => "123",
                            "brand" => "Visa",
                            "expiration_month" => "12",
                            "expiration_year" => "28"
                        ],
                    ],
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            if($e->hasResponse())
                $codigo = $e->getResponse()->getStatusCode();
            throw new \Exception('Retorno Erro Getnet: ' . $e->getMessage(), $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function pagamentoCredito($ip, $rep)
    {
        try{
            $this->client = new Client();
            $auth = $this->getToken();
            $number_token = $this->tokenizacao($auth)['number_token'];
            $verify = $this->verifyCard($auth, $number_token);
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/credit', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $auth['token_type'] . ' ' . $auth['access_token'],
                ],
                'json' => [
                    'seller_id' => env('GETNET_SELLER_ID'),
                    'amount' => '750000303',
                    'currency' => "BRL",
                    'order' => [
                        "order_id" => "6d2e4380-d8a3-4ccb-9138-c289182818a3",
                    ],
                    'customer' => [
                        "customer_id" => "customer_21081826",
                        "first_name" => "João",
                        "last_name" => "da Silva",
                        "name" => "João da Silva",
                        "email" => "customer@email.com.br",
                        "document_type" => $rep->tipoPessoa() == 'PF' ? "CPF" : "CNPJ",
                        "document_number" => apenasNumeros($rep->cpf_cnpj),
                        "phone_number" => "5551999887766",
                        'billing_address' => [
                            "street" => "Av. Brasil",
                            "number" => "1000",
                            "complement" => "Sala 1",
                            "district" => "São Geraldo",
                            "city" => "Porto Alegre",
                            "state" => "RS",
                            "country" => "Brasil",
                            "postal_code" => "90230060"
                        ],
                    ],
                    'device' => [
                        "ip_address" => $ip,
                        "device_id" => "123456123456"
                    ],
                    'shippings' => [
                    ],
                    'sub_merchant' => [
                        "identification_code" => "9058344",
                        "document_type" => "CNPJ",
                        "document_number" => "20551625000159",
                        "address" => "Torre Negra 44",
                        "city" => "Cidade",
                        "state" => "RS",
                        "postal_code" => "90520000"
                    ],
                    'credit' => [
                        "delayed" => false,
                        "pre_authorization" => false,
                        "save_card_data" => false,
                        "transaction_type" => "INSTALL_NO_INTEREST",
                        "number_installments" => 3,
                        "soft_descriptor" => "LOJA*TESTE*COMPRA-123",
                        'card' => [
                            "number_token" => $number_token,
                            "cardholder_name" => "JOAO DA SILVA",
                            "security_code" => "123",
                            "brand" => "Visa",
                            "expiration_month" => "12",
                            "expiration_year" => "28"
                        ],
                    ],
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            if($e->hasResponse())
                $codigo = $e->getResponse()->getStatusCode();
            throw new \Exception('Retorno Erro Getnet: ' . $e->getMessage(), $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function cancelarPagamentoCredito($ip)
    {
        try{
            $this->client = new Client();
            $auth = $this->getToken();
            $number_token = $this->tokenizacao($auth)['number_token'];
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/credit', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $auth['token_type'] . ' ' . $auth['access_token'],
                ],
                'json' => [
                    'seller_id' => env('GETNET_SELLER_ID'),
                    'amount' => '750000303',
                    'currency' => "BRL",
                    'order' => [
                        "order_id" => "6d2e4380-d8a3-4ccb-9138-c289182818a3",
                    ],
                    'customer' => [
                        "customer_id" => "customer_21081826",
                        "first_name" => "João",
                        "last_name" => "da Silva",
                        "name" => "João da Silva",
                        "email" => "customer@email.com.br",
                        "document_type" => "CPF",
                        "document_number" => "12345678912",
                        "phone_number" => "5551999887766",
                        'billing_address' => [
                            "street" => "Av. Brasil",
                            "number" => "1000",
                            "complement" => "Sala 1",
                            "district" => "São Geraldo",
                            "city" => "Porto Alegre",
                            "state" => "RS",
                            "country" => "Brasil",
                            "postal_code" => "90230060"
                        ],
                    ],
                    'device' => [
                        "ip_address" => $ip,
                        "device_id" => "123456123456"
                    ],
                    'shippings' => [
                    ],
                    'sub_merchant' => [
                        "identification_code" => "9058344",
                        "document_type" => "CNPJ",
                        "document_number" => "20551625000159",
                        "address" => "Torre Negra 44",
                        "city" => "Cidade",
                        "state" => "RS",
                        "postal_code" => "90520000"
                    ],
                    'credit' => [
                        "delayed" => false,
                        "pre_authorization" => false,
                        "save_card_data" => false,
                        "transaction_type" => "INSTALL_NO_INTEREST",
                        "number_installments" => 3,
                        "soft_descriptor" => "LOJA*TESTE*COMPRA-123",
                        'card' => [
                            "number_token" => $number_token,
                            "cardholder_name" => "JOAO DA SILVA",
                            "security_code" => "123",
                            "brand" => "Visa",
                            "expiration_month" => "12",
                            "expiration_year" => "28"
                        ],
                    ],
                ],
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            if($e->hasResponse())
                $codigo = $e->getResponse()->getStatusCode();
            throw new \Exception('Retorno Erro Getnet: ' . $e->getMessage(), $codigo);
        }

        $payment_id = json_decode($response->getBody()->getContents(), true)['payment_id'];

        try{
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/credit/' . $payment_id . '/cancel', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $auth['token_type'] . ' ' . $auth['access_token'],
                ]
            ]);
        }catch(ClientException $e){
            $codigo = 0;
            if($e->hasResponse())
                $codigo = $e->getResponse()->getStatusCode();
            throw new \Exception('Retorno Erro Getnet: ' . $e->getMessage(), $codigo);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    public function formatPagCheckout($request)
    {
        $this->client = new Client();
        $auth = $this->getToken();

        $pagamento = $request;
        $pagamento['sellerid'] = env('GETNET_SELLER_ID');
        $pagamento['token'] = $auth['token_type'] . ' ' . $auth['access_token'];
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