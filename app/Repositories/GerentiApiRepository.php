<?php

namespace App\Repositories;

use GuzzleHttp\Client;

class GerentiApiRepository
{
    private $bearerToken;

    // Função usada para gerar tokens nas chamadas das APIs do GERENTI
    protected function generateToken()
    {
        $client = new Client();

        $response = $client->request('POST', env('GERENTI_API_BASE_URL') . '/api/v1/auth', [
            'json' => [
                'appId' => env('GERENTI_API_APP_ID'),
                'appSecret' => env('GERENTI_API_APP_SECRET')
            ]
        ]);

        $this->bearerToken = json_decode($response->getBody()->getContents(), true)['data']['accessToken'];
    }

    // API do GERENTI usada para emitir certidão
    public function gerentiGenerateCertidao($assId)
    {
        // API exige geração de token
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

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para recuperar certidão
    public function gerentiGetCertidao($assId)
    {
        // API exige geração de token
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $assId . '/documentos', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}