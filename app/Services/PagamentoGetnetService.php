<?php

namespace App\Services;

use App\Contracts\PagamentoServiceInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

class PagamentoGetnetService implements PagamentoServiceInterface {

    private $urlBase;

    public function __construct()
    {
        $this->urlBase = config('app.env') != 'production' ? 'https://api-homologacao.getnet.com.br/auth/oauth/v2/token' : '';
    }

    public function getToken()
    {
        try{
            $client = new Client();
            $response = $client->request('POST', $this->urlBase, [
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

    public function formatPagCheckout($request)
    {
        $auth = $this->getToken();

        $pagamento = $request;
        $pagamento['sellerid'] = env('GETNET_SELLER_ID');
        $pagamento['token'] = $auth['token_type'] . ' ' . $auth['access_token'];
        $pagamento['amount'] = '150.23';
        $pagamento['customerid'] = '12345';
        $pagamento['orderid'] = '12345';
        $pagamento['installments'] = '3';
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