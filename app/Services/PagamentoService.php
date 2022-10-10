<?php

namespace App\Services;

use App\Contracts\PagamentoServiceInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;

class PagamentoService implements PagamentoServiceInterface {

    private $urlBase;

    public function __construct()
    {
        $this->urlBase = config('app.env') != 'production' ? 'https://api-homologacao.getnet.com.br/auth/oauth/v2/token' : '';
    }

    public function getToken()
    {
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

        if($response->getStatusCode() == 400)
            throw new Exception('Requisição Inválida ao gerar o token de autenticação', 400);
        if($response->getStatusCode() == 401)
            throw new Exception('Requisição não autorizada para gerar o token de autenticação', 401);
        
        return json_decode($response->getBody()->getContents(), true);
    }
}