<?php

namespace App\Repositories;

use GuzzleHttp\Client;

class GerentiApiRepository
{
    private $bearerToken;

    protected function generateToken()
    {
        $client = new Client();

        $response = $client->request('POST', env('GERENTI_API_BASE_URL') . '/api/v1/auth', [
            'json' => [
                'appId' => env('GERENTI_API_APP_ID'),
                'appSecret' => env('GERENTI_API_APP_SECRET')
            ]
        ]);

        $this->bearerToken = json_decode($response->getBody()->getContents())->data->accessToken;
    }

    public function gerentiGenerateCertidao($assId)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('POST', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $assId . '/documentos', [
            'json' => [
                'codigo' => 'CERTIDAO',
                'timbrado' => true,
                'cabecalho' => false,
                'rodape' => false,
                'marcadagua' => false
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }
}