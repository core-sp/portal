<?php

namespace App\Repositories;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GerentiApiRepository
{
    private $bearerToken;

    // Função usada para gerar tokens nas chamadas das APIs do GERENTI
    protected function generateToken()
    {
        if(is_null($this->bearerToken)) {
            $client = new Client();

            try {
                $response = $client->request('POST', env('GERENTI_API_BASE_URL') . '/api/v1/auth', [
                    'json' => [
                        'appId' => env('GERENTI_API_APP_ID'),
                        'appSecret' => env('GERENTI_API_APP_SECRET')
                    ]
                ]);
    
                $this->bearerToken = json_decode($response->getBody()->getContents(), true)['data']['accessToken'];
            }
            catch (Exception $e) {
                Log::error('ERROR WHEN GENERATING TOKEN. ' . $e->getTraceAsString());

                abort(500, 'Estamos enfrentando problemas técnicos no momento. Por favor, tente dentro de alguns minutos.');
            }
        }
    }

    // API do GERENTI usada para emitir certidão
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

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para recuperar certidão
    public function gerentiGetCertidao($assId)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $assId . '/documentos', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Simulador
    public function gerentiSimulador($tipoAssociado = 1, $dataInicio = null, $capitalSocial = 0)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('POST', env('GERENTI_API_BASE_URL') . '/api/v1/simulacao', [
            'json' => [
                'tipoAssociado' => $tipoAssociado,
                'dataInicio' => !isset($dataInicio) ? now()->toISOString() : $dataInicio,
                'capitalSocial' => $capitalSocial,
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Tipos de Contatos
    public function gerentiTiposContatos()
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/tiposdecontato', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Contatos do Representante
    public function gerentiGetContatos($ass_id, $tipo = null)
    {
        $this->generateToken();

        $client = new Client();
        $query = isset($tipo) ? '?tipo=' . $tipo : '';

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $ass_id . '/contatos' . $query, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Endereços do Representante
    public function gerentiGetEnderecos($ass_id)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $ass_id . '/enderecos', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Extratos do Representante
    public function gerentiGetExtrato($ass_id)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $ass_id . '/extrato', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Segmentos
    public function gerentiSegmentos()
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/segmentos', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Dados do Representante
    public function gerentiDadosRepresentante($ass_id)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $ass_id . '/dados', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Representante Registrado
    public function gerentiRepresentanteRegistrado($registro, $cpf_cnpj, $email)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('POST', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/registrar', [
            'json' => [
                'registro' => apenasNumeros($registro),
                'cpf_cnpj' => apenasNumeros($cpf_cnpj),
                'email' => $email,
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // API do GERENTI usada para serviço de Validar Representante
    public function gerentiValidarRepresentante($ass_id)
    {
        $this->generateToken();

        $client = new Client();

        $response =  $client->request('GET', env('GERENTI_API_BASE_URL') . '/api/v1/representantes/' . $ass_id . '/validar', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}