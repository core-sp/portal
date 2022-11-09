<?php

namespace App\Services;

use App\Contracts\PagamentoServiceInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PagamentoMail;
use App\Pagamento;

class PagamentoGetnetService implements PagamentoServiceInterface {

    private $urlBase;
    private $client;
    private $auth;
    private $via_sistema;
    const TOTAL_CARTOES = 2;
    const TEXTO_LOG_SISTEMA = '[Rotina Portal - Transação Getnet] ';

    public function __construct()
    {
        $this->urlBase = config('app.url') != 'https://core-sp.org.br' ? 'https://api-homologacao.getnet.com.br' : '';
        $this->via_sistema = false;
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

    private function getStatusTransacao($transacao, $tipo_pag)
    {
        return $tipo_pag != 'combined' ? $transacao['status'] : [$transacao['payments'][0]['status'], $transacao['payments'][1]['status']];
    }

    private function getMsgDtCancelTransacao($transacao, $tipo_pag)
    {
        switch($tipo_pag) {
            case 'debit_3ds':
            case 'credit_3ds':
                return [
                    'msg' => $transacao['reason_message'], 
                    'dt' => $transacao['canceled_at']
                ];
            case 'credit':
                return [
                    'msg' => $transacao[$tipo_pag.'_cancel']['message'], 
                    'dt' => $transacao[$tipo_pag.'_cancel']['canceled_at']
                ];
            case 'combined':
                return [
                    'msg' => [
                        $transacao['payments'][0]['credit_cancel']['message'], 
                        $transacao['payments'][1]['credit_cancel']['message']
                    ],
                    'dt' => $transacao['payments'][0]['credit_cancel']['canceled_at']
                ];
        }
    }

    private function getArraySavePagamento($transacao, $status, $dados)
    {
        $base = [
            'boleto_id' => $dados['boleto'],
            'forma' => $dados['tipo_pag'],
            'parcelas' => $dados['parcelas_1'],
            'tipo_parcelas' => $dados['tipo_parcelas_1'],
        ];

        if($dados['tipo_pag'] != 'combined')
        {
            $base['payment_id'] = $transacao['payment_id'];
            $base['status'] = mb_strtoupper($status);
            $base['authorized_at'] = $transacao[$dados['tipo_pag']]['authorized_at'];
            $base['is_3ds'] = strpos($dados['tipo_pag'], '_3ds') !== false;
            return $base;
        }

        $base['combined_id'] = $transacao['combined_id'];
        $base_2 = $base;
        $base['authorized_at'] = $transacao['payments'][0]['credit']['authorized_at'];
        $base['status'] = mb_strtoupper($status[0]);
        $base['payment_tag'] = $transacao['payments'][0]['payment_tag'];
        $base['payment_id'] = $transacao['payments'][0]['payment_id'];
        // cartão 2
        $base_2['tipo_parcelas'] = $dados['tipo_parcelas_2'];
        $base_2['parcelas'] = $dados['parcelas_2'];
        $base_2['authorized_at'] = $transacao['payments'][1]['credit']['authorized_at'];
        $base_2['status'] = mb_strtoupper($status[1]);
        $base_2['payment_tag'] = $transacao['payments'][1]['payment_tag'];
        $base_2['payment_id'] = $transacao['payments'][1]['payment_id'];

        return [$base, $base_2];
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
        if($tipo != 'combined')
        {
            $expiration = Carbon::createFromFormat('Y-m', $dados['expiration_1']);
            $resultado['card'] = [
                "number_token" => $dados['number_token'],
                "cardholder_name" => $dados['cardholder_name_1'],
                "security_code" => $dados['security_code_1'],
                "brand" => $dados['brand'],
                "expiration_month" => $expiration->format('m'),
                "expiration_year" => $expiration->format('y')
            ];
        }else{
            for($i = 1; $i <= self::TOTAL_CARTOES; $i++)
            {
                $expiration = Carbon::createFromFormat('Y-m', $dados['expiration_'.$i]);
                $resultado['card_' . $i] = [
                    "number_token" => $dados['number_token_' . $i],
                    "cardholder_name" => $dados['cardholder_name_' . $i],
                    "security_code" => $dados['security_code_' . $i],
                    "brand" => $dados['brand_' . $i],
                    "expiration_month" => $expiration->format('m'),
                    "expiration_year" => $expiration->format('y')
                ];
            }
        }
        
        switch($tipo){
            case 'credit_3ds':
            case 'debit_3ds':
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
                $array = array();
                for($i = 1; $i <= self::TOTAL_CARTOES; $i++)
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
    }

    private function aposRotinaTransacao($user, $pagamentos, $status, $errorCode = null, $descricao = null)
    {
        // passa pro gerenti 
        // * caso dê erro (por motivos de conexão, etc), rotina de cancelamento do pagamento se status aprovado() com aviso no log com motivo de erro no gerenti

        $pagamento = $pagamentos->first();
        
        if(!$this->via_sistema)
        {
            if($status != 'CANCELED')
            {
                $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") realizou pagamento do boleto ' . $pagamento->boleto_id . ' do tipo *' . $pagamento->forma . '* com a ';
                $string .= !isset($pagamento->combined_id) ? 'payment_id: ' . $pagamento->payment_id : 'combined_id: '. $pagamento->combined_id;
                $string .= '. [Dados Request:' . json_encode(request()->all()) . ']; [Dados Session:' . json_encode(session()->all()) . ']; [Dados Cache:' . json_encode(cache()) . ']';
            }else{
                $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") realizou o cancelamento do pagamento do boleto ' . $pagamento->boleto_id . ' do tipo *' . $pagamento->forma . '* com a ';
                $string .= !isset($pagamento->combined_id) ? 'payment_id: ' . $pagamento->payment_id : 'combined_id: ' . $pagamento->combined_id;
            }
        }else{
            $string = self::TEXTO_LOG_SISTEMA . 'ID: ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") teve alteração de status do pagamento do boleto ' . $pagamento->boleto_id;
            $string .= ' do tipo *' . $pagamento->forma . ' *, com a payment_id: ' . $pagamento->payment_id . ' para: ' . $status;
            if(in_array($status, ['ERROR', 'DENIED']))
                $string .= '. Detalhes da Getnet: error code - [' . $errorCode . '], description details - [' . $descricao . '].';
        }
        
        event(new ExternoEvent($string));

        Mail::to($user->email)->queue(new PagamentoMail($pagamentos->fresh()));
    }

    private function createViaNotificacao($dados)
    {
        // $user = Buscar por customer_id / pelo gerenti saber qual usuario
        $user = \App\Representante::find(1);
        $pagamento = $user->pagamentos()->create([
            'payment_id' => $dados['payment_id'],
            'boleto_id' => $dados['order_id'],
            'forma' => $dados['payment_type'],
            'parcelas' => $dados['number_installments'],
            'is_3ds' => $dados['payment_type'] == 'debit',
            'tipo_parcelas' => $dados['tipo_parcelas'],
            'status' => $dados['status'],
            'authorized_at' => $dados['authorization_timestamp'],
        ]);

        $this->aposRotinaTransacao($user, $pagamento, $dados['status']);
    }

    private function updateViaNotificacao($dados, $pagamento, $user)
    {
        if($pagamento->status != $dados['status'])
        {
            $this->via_sistema = true;
            $pagamento->update([
                'status' => $dados['status'],
                'authorized_at' => in_array($dados['status'], ['APPROVED', 'AUTHORIZED']) ? $dados['authorization_timestamp'] : $pagamento->authorized_at,
                'canceled_at' => $dados['status'] == 'CANCELED' ? now()->toIso8601ZuluString() : $pagamento->canceled_at,
            ]);

            $error = isset($dados['error_code']) ? $dados['error_code'] : null;
            $descricao = isset($dados['description_detail']) ? $dados['description_detail'] : null;
            $this->aposRotinaTransacao($user, $pagamento, $dados['status'], $error, $descricao);

            if(isset($pagamento->combined_id) && in_array($dados['status'], ['CANCELED', 'DENIED', 'ERROR']))
            {
                $outroPagamento = Pagamento::where('boleto_id', $dados['order_id'])
                ->where('combined_id', $pagamento->combined_id)
                ->where('payment_id', '!=', $dados['payment_id'])
                ->whereIn('status', ['APPROVED', 'AUTHORIZED'])
                ->first();

                $outroPagamento->update([
                    'status' => $dados['status'],
                    'authorized_at' => in_array($dados['status'], ['APPROVED', 'AUTHORIZED']) ? $dados['authorization_timestamp'] : $outroPagamento->authorized_at,
                    'canceled_at' => $dados['status'] == 'CANCELED' ? now()->toIso8601ZuluString() : $outroPagamento->canceled_at,
                ]);
    
                $this->aposRotinaTransacao($user, $outroPagamento, $dados['status']);
            }
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
        }catch(RequestException $e){
            $this->formatError($e);
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
        }catch(RequestException $e){
            $this->formatError($e);
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
        }catch(RequestException $e){
            $this->formatError($e);
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
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function pagamentoCombinado($ip, $dados)
    {
        try{
            $this->getToken();
            $temp = array();
            for($i = 1; $i <= self::TOTAL_CARTOES; $i++)
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
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
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

    private function pagamento3DS($dados)
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

    private function cancelarPagamento($payment_id, $tipo_pag)
    {
        try{
            $temp = strpos($tipo_pag, '_3ds') !== false ? 'authenticated' : $tipo_pag;
            $this->getToken();
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/' . $temp . '/' . $payment_id . '/cancel', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Cache-control' => 'no-cache',
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
                ],
                'json' => $temp == 'authenticated' ? ['payment_method' => strtoupper(str_replace('_3ds', '', $tipo_pag))] : [],
            ]);
        }catch(RequestException $e){
            $this->formatError($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function cancelarPagamentoCombinado($payment_ids, $payment_tags)
    {
        try{
            $array = array();
            for($i = 0; $i < self::TOTAL_CARTOES; $i++)
                if(isset($payment_ids[$i]))
                    array_push($array, [
                        'payment_id' => $payment_ids[$i],
                        'payment_tag' => $payment_tags[$i],
                    ]);
            $this->getToken();
            $response = $this->client->request('POST', $this->urlBase . '/v1/payments/combined/cancel', [
                'headers' => [
                    'Content-type' => "application/json; charset=utf-8",
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
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
                    'Authorization' => $this->auth['token_type'] . ' ' . $this->auth['access_token'],
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
            $dados['token'] = $this->auth['token_type'] . ' ' . $this->auth['access_token'];
            $dados['token_principal'] = "Basic " . base64_encode(env('GETNET_CLIENT_ID') . ':' . env('GETNET_CLIENT_SECRET'));
            $brand = mb_strtolower($dados['brand']);
            $dados['card_type'] = isset($card_type[$brand]) ? $card_type[$brand] : '';
        }catch(RequestException $e){
            $this->formatError($e);
        }
        
        return $dados;
    }

    public function generateToken3DS($request)
    {
        
    }

    public function authentication3DS($request)
    {
        
    }

    public function authenticationResults3DS($request)
    {
        
    }

    public function checkout($ip, $dados, $user)
    {
        $transacao = null;
        switch($dados['tipo_pag']){
            case 'credit_3ds':
            case 'debit_3ds':
                $transacao = $this->pagamento3DS($dados);
                break;
            case 'credit':
                $transacao = $this->pagamento($ip, $dados);
                break;
            case 'combined':
                $transacao = $this->pagamentoCombinado($ip, $dados);
                break;
        }

        if(isset($transacao['message-cartao']))
        {
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") tentou realizar o pagamento do boleto ' . $dados['boleto'] . ' do tipo *' . $dados['tipo_pag'] . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: ' . $transacao['retorno_getnet'];
            event(new ExternoEvent($string));
            return $transacao;
        }

        $status = $this->getStatusTransacao($transacao, $dados['tipo_pag']);
        $combined = is_array($status) && (($status[0] != 'AUTHORIZED') || ($status[1] != 'AUTHORIZED'));
        $not_combined = !is_array($status) && ($status != 'APPROVED');

        if($combined || $not_combined)
        {
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") tentou realizar o pagamento do boleto ' . $dados['boleto'] . ' do tipo *' . $dados['tipo_pag'] . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: Cartão verificado, mas ao realizar o pagamento o status recebido foi' . json_encode($status);
            event(new ExternoEvent($string));

            return [
                'message-cartao' => '<i class="fas fa-ban"></i> Status da transação: ' . is_array($status) ? $status[0] . ' e ' . $status[1] : $status . '. Pagamento do boleto ' . $dados['boleto'] . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        $arrayPag = $this->getArraySavePagamento($transacao, $status, $dados);
        $pagamento = $dados['tipo_pag'] != 'combined' ? $user->pagamentos()->create($arrayPag) : $user->pagamentos()->createMany($arrayPag);

        unset($transacao);
        unset($dados);

        $this->aposRotinaTransacao($user, $pagamento, is_array($status) ? $status[0] : $status);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Pagamento realizado para o boleto ' . $pagamento->first()->boleto_id . '. Detalhes do pagamento enviado para o e-mail: ' . $user->email,
            'class' => 'alert-success'
        ];
    }

    public function cancelCheckout($dados, $user)
    {
        $pagamento = $dados['pagamento'];
        $tipo_pag = $pagamento->first()->getForma3DS();

        if($tipo_pag != 'combined')
            $transacao = $this->cancelarPagamento($pagamento->first()->payment_id, $tipo_pag);
        else{
            for($i = 0; $i < self::TOTAL_CARTOES; $i++)
            {
                $ids[$i] = $pagamento->get($i)->aprovado() ? $pagamento->get($i)->payment_id : null;
                $tags[$i] = $pagamento->get($i)->aprovado() ? $pagamento->get($i)->payment_tag : null;
            }
            $transacao = $this->cancelarPagamentoCombinado($ids, $tags);
        }

        $status = $this->getStatusTransacao($transacao, $tipo_pag);
        $combined = is_array($status) && (($status[0] != 'CANCELED') || ($status[1] != 'CANCELED'));
        $not_combined = !is_array($status) && ($status != 'CANCELED');
        $message = $this->getMsgDtCancelTransacao($transacao, $tipo_pag);
        
        if($combined || $not_combined)
        {
            $string = $this->via_sistema ? self::TEXTO_LOG_SISTEMA . ' ID: ' : 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") tentou realizar o cancelamento do pagamento do boleto ' . $dados['boleto'] . ' do tipo *' . $tipo_pag . '* com a ';
            $string .= $tipo_pag != 'combined' ? 'payment_id: ' . $pagamento->first()->payment_id : 'combined_id: ' . $pagamento->first()->combined_id;
            $string .= ', mas não foi possível. Retorno da Getnet: ' . json_encode($message['msg']);
            event(new ExternoEvent($string));

            return [
                'message-cartao' => '<i class="fas fa-ban"></i> Status do cancelamento da transação: ' . is_array($status) ? $status[0] . ' e ' . $status[1] : $status . '. Cancelamento do pagamento do boleto ' . $dados['boleto'] . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        unset($transacao);
        unset($dados);

        foreach($pagamento as $pag)
            $pag->update([
                'status' => is_array($status) ? $status[0] : $status,
                'canceled_at' => $message['dt'],
            ]);
        
        $this->aposRotinaTransacao($user, $pagamento, is_array($status) ? $status[0] : $status);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Cancelamento do pagamento do boleto ' . $pagamento->first()->boleto_id . ' aprovado. Detalhes do cancelamento do pagamento enviado para o e-mail: ' . $user->email,
            'class' => 'alert-success'
        ];
    }

    public function checkoutIframe($request, $user)
    {
        $this->getToken();

        $array_disabled = [
            'credit' => "credito-nao-autenticado", 
            'credit_3ds' => "credito-autenticado", 
            'debit_3ds' => "debito-autenticado", 
            "debito-nao-autenticado", 
            "boleto", 
            "qr-code",
            "pix"
        ];

        unset($array_disabled[$request['tipo_pag']]);
        $pagamento = $request;
        $pagamento['sellerid'] = env('GETNET_SELLER_ID');
        $pagamento['token'] = $this->auth['token_type'] . ' ' . $this->auth['access_token'];
        $pagamento['amount'] = substr_replace($request['valor'], '.', strlen($request['valor']) - 2, 0);
        $pagamento['customerid'] = $user->getCustomerId();
        $pagamento['orderid'] = $request['boleto'];
        $pagamento['installments'] = $request['parcelas_1'];
        $pagamento['disabled'] = implode(',', array_values($array_disabled));
        $pagamento['callback'] = route($user::NAME_ROUTE . '.dashboard');

        return $pagamento;
    }

    public function getException($erro_msg, $cod)
    {
        $generic = 'Erro desconhecido';
        $msg = 'Código de erro da prestadora: ' . $cod . '.<br>';

        $temp = json_decode($erro_msg, true);
        if(json_last_error() === JSON_ERROR_NONE)
        {
            $opcao = isset($temp['message']) ? $temp['message'] : $generic;
            if(isset($temp['details']) || isset($temp['payments']))
            {
                $campo = isset($temp['details']) ? 'details' : 'payments';
                foreach($temp[$campo] as $key => $value)
                    $msg .= isset($value['description']) ? 'Descrição (cartão ' . ++$key . '): ' . $value['description'] . '<br>' : $opcao . '<br>';
            }
            elseif(isset($temp['error']))
                $msg .= 'Erro: ' . $temp['error_description'];
            elseif(isset($temp['status']))
                $msg .= 'Status: ' . $temp['status'];
            else
                $msg .= 'Descrição: ' . $opcao;
        }
        else
            $msg = null;

        return $msg;
    }

    public function rotinaUpdateTransacao($dados)
    {
        // Buscar no gerenti se o boleto existe
        $pagamento = Pagamento::where('boleto_id', $dados['order_id'])->where('payment_id', $dados['payment_id'])->first();

        if(!isset($pagamento))
        {
            if($dados['checkoutIframe'])
                $this->createViaNotificacao($dados);
        }else{
            $user = $pagamento->getUser();
            $this->updateViaNotificacao($dados, $pagamento, $user);
        }
    }
}