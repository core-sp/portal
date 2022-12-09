<?php

namespace App\Services;

use App\Contracts\PagamentoServiceInterface;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\PagamentoMail;
use App\Pagamento;
use App\Services\PagamentoGetnetApiService;

class PagamentoGetnetService implements PagamentoServiceInterface {

    private $api;
    private $via_sistema;
    const TOTAL_CARTOES = 2;
    const TEXTO_LOG_SISTEMA = '[Rotina Portal - Transação Getnet] ';
    const FORMAT_DT_EXP = 'm/Y';

    public function __construct()
    {
        $this->api = new PagamentoGetnetApiService(self::TOTAL_CARTOES, self::FORMAT_DT_EXP);
        $this->via_sistema = false;
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
        $temp = str_replace('_3ds', '', $dados['tipo_pag']);
        $base = [
            'boleto_id' => $dados['boleto'],
            'forma' => mb_strtolower($temp),
            'parcelas' => $dados['parcelas_1'],
            'tipo_parcelas' => mb_strtoupper($dados['tipo_parcelas_1']),
        ];

        if($dados['tipo_pag'] != 'combined')
        {
            $base['payment_id'] = $transacao['payment_id'];
            $base['status'] = mb_strtoupper($status);
            $base['authorized_at'] = strpos($dados['tipo_pag'], '_3ds') !== false ? $transacao['authorized_at'] : $transacao[$temp]['authorized_at'];
            $base['is_3ds'] = strpos($dados['tipo_pag'], '_3ds') !== false;
            $base['bandeira'] = strpos($dados['tipo_pag'], '_3ds') !== false ? mb_strtolower($transacao['brand']) : 
            mb_strtolower($transacao[$temp]['brand']);
            return $base;
        }

        $base['combined_id'] = $transacao['combined_id'];
        $base_2 = $base;
        $base['authorized_at'] = $transacao['payments'][0]['credit_confirm']['confirm_date'];
        $base['status'] = mb_strtoupper($status[0]);
        $base['payment_tag'] = $transacao['payments'][0]['payment_tag'];
        $base['payment_id'] = $transacao['payments'][0]['payment_id'];
        $base['bandeira'] = mb_strtolower($transacao['payments'][0]['brand']);
        // cartão 2
        $base_2['tipo_parcelas'] = mb_strtoupper($dados['tipo_parcelas_2']);
        $base_2['parcelas'] = $dados['parcelas_2'];
        $base_2['authorized_at'] = $transacao['payments'][1]['credit_confirm']['confirm_date'];
        $base_2['status'] = mb_strtoupper($status[1]);
        $base_2['payment_tag'] = $transacao['payments'][1]['payment_tag'];
        $base_2['payment_id'] = $transacao['payments'][1]['payment_id'];
        $base_2['bandeira'] = mb_strtolower($transacao['payments'][1]['brand']);

        return [$base, $base_2];
    }

    private function aposRotinaTransacao($user, $pagamentos, $status, $transacao = null, $errorCode = null, $descricao = null)
    {
        // passa pro gerenti 
        // se tudo certo, atualiza dados no bd
        // * caso dê erro (por motivos de conexão, etc) a transação é salva como json, o campo gerenti_ok update false, com aviso no log com motivo de erro no gerenti e a rotina no Kernel tentará novamente enviar ao Gerenti.
        
        // Código no serviço do gerenti
        // $pagamentos = Pagamento::getCollection($pagamentos);
        // foreach($pagamentos as $pag)
        //     $pag->updateAposSucessoGerenti();

        // $pagamentos = Pagamento::getCollection($pagamentos);
        // foreach($pagamentos as $pag)
        //     $pag->updateAposErroGerenti($transacao);

        $pagamento = Pagamento::getFirst($pagamentos);
        
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

        Mail::to($user->email)->queue(new PagamentoMail($pagamentos));
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

        $this->aposRotinaTransacao($user, $pagamento, $dados['status'], $dados);
    }

    private function updateViaNotificacao($dados, $pagamento, $user)
    {
        if($pagamento->status != $dados['status'])
        {
            $this->via_sistema = true;
            $pagamento->updateAposNotificacao($dados);

            $error = isset($dados['error_code']) ? $dados['error_code'] : null;
            $descricao = isset($dados['description_detail']) ? $dados['description_detail'] : null;
            $outroPagamento = $pagamento->getCombinadoAposNotificacao($dados);

            if(isset($outroPagamento))
            {
                $dados['boleto'] = $dados['order_id'];
                $dados['pagamento'] = collect([$pagamento, $outroPagamento]);
                $this->cancelCheckout($dados, $user);
            }

            $this->aposRotinaTransacao($user, $pagamento, $dados['status'], $dados, $error, $descricao);
        }
    }

    private function realizarPagamento($dados, $ip)
    {
        switch($dados['tipo_pag'])
        {
            case 'credit_3ds':
            case 'debit_3ds':
                return $this->api->pagamento3DS($dados);
            case 'credit':
                return $this->api->pagamento($ip, $dados);
            case 'combined':
                return $this->api->pagamentoCombinado($ip, $dados);
        }
    }

    private function confirmacaoPagamento($transacao, $status)
    {
        if(is_array($status) && ($status[0] == 'AUTHORIZED') && ($status[1] == 'AUTHORIZED'))
        {
            $ids = array();
            $tags = array();
            foreach($transacao['payments'] as $key => $pags)
            {
                $ids[$key] = $pags['payment_id'];
                $tags[$key] = $pags['payment_tag'];
            }
            $brand1 = $transacao['payments'][0]['credit']['brand'];
            $brand2 = $transacao['payments'][1]['credit']['brand'];
            $transacao = $this->api->confirmation($ids, $tags);
            $transacao['payments'][0]['brand'] = $brand1;
            $transacao['payments'][1]['brand'] = $brand2;
        }

        return $transacao;
    }

    private function cancelamentoPagamento($tipo_pag, $pagamento)
    {
        if($tipo_pag != 'combined')
        {
            $pagamento = Pagamento::getFirst($pagamento);
            return $this->api->cancelarPagamento($pagamento->payment_id, $tipo_pag);
        }
        
        for($i = 0; $i < self::TOTAL_CARTOES; $i++)
        {
            $ids[$i] = $pagamento->get($i)->payment_id;
            $tags[$i] = $pagamento->get($i)->payment_tag;
        }
        return $this->api->cancelarPagamentoCombinado($ids, $tags);
    }

    public function getTiposPagamento()
    {
        return [
            'credit' => 'Crédito', 
            'combined' => 'Crédito com dois cartões', 
            // 'credit_3ds' => 'Crédito com 3DS', 
            // 'debit_3ds' => 'Débito com 3DS',
        ];
    }

    public function getTiposPagamentoCheckout()
    {
        return [
            'credit' => 'Crédito',
            // 'credit_3ds' => 'Crédito com 3DS', 
            // 'debit_3ds' => 'Débito com 3DS',
        ];
    }

    public function getDados3DS($bin)
    {
        return $this->api->getDados3DS($bin);
    }

    public function autenticacao3DS($dados, $nome_rota)
    {
        switch($nome_rota)
        {
            case 'pagamento.generate.token':
                return $this->api->generateToken3DS($dados);
            case 'pagamento.authentications':
                return $this->api->authentication3DS($dados);
            case 'pagamento.authentications.results':
                return $this->api->authenticationResults3DS($dados);
            default:
                return [];
        }
    }

    public function checkout($ip, $dados, $user)
    {
        $transacao = $this->realizarPagamento($dados, $ip);

        if(isset($transacao['message-cartao']))
        {
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") tentou realizar o pagamento do boleto ' . $dados['boleto'] . ' do tipo *' . $dados['tipo_pag'] . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: ' . $transacao['retorno_getnet'];
            event(new ExternoEvent($string));
            return $transacao;
        }

        $status = $this->getStatusTransacao($transacao, $dados['tipo_pag']);
        $transacao = $this->confirmacaoPagamento($transacao, $status);
        $status = $this->getStatusTransacao($transacao, $dados['tipo_pag']);

        $combined = is_array($status) && (($status[0] != 'CONFIRMED') || ($status[1] != 'CONFIRMED'));
        $not_combined = !is_array($status) && ($status != 'APPROVED');

        if($combined || $not_combined)
        {
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'") tentou realizar o pagamento do boleto ' . $dados['boleto'] . ' do tipo *' . $dados['tipo_pag'] . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: Cartão verificado, mas ao realizar o pagamento o status recebido foi ' . json_encode($status);
            event(new ExternoEvent($string));

            return [
                'message-cartao' => '<i class="fas fa-times"></i> Status da transação: ' . is_array($status) ? $status[0] . ' e ' . $status[1] : $status . '. Pagamento do boleto ' . $dados['boleto'] . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        $arrayPag = $this->getArraySavePagamento($transacao, $status, $dados);
        $pagamento = $dados['tipo_pag'] != 'combined' ? $user->pagamentos()->create($arrayPag) : $user->pagamentos()->createMany($arrayPag);

        unset($dados);
        $this->aposRotinaTransacao($user, $pagamento, is_array($status) ? $status[0] : $status, $transacao);
        $pagamento = Pagamento::getFirst($pagamento);
        unset($transacao);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Pagamento realizado para o boleto ' . $pagamento->boleto_id . '. Detalhes do pagamento enviado para o e-mail: ' . $user->email,
            'class' => 'alert-success'
        ];
    }

    public function cancelCheckout($dados, $user)
    {
        $pagamento = $dados['pagamento'];
        $tipo_pag = $pagamento->first()->getForma3DS();

        $transacao = $this->cancelamentoPagamento($tipo_pag, $pagamento);
        $pagamento = Pagamento::getCollection($pagamento);

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
                'message-cartao' => '<i class="fas fa-times"></i> Status do cancelamento da transação: ' . is_array($status) ? $status[0] . ' e ' . $status[1] : $status . '. Cancelamento do pagamento do boleto ' . $dados['boleto'] . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        unset($dados);
        foreach($pagamento as $pag)
            $pag->updateCancelamento($status, $message);
        
        $this->aposRotinaTransacao($user, $pagamento, is_array($status) ? $status[0] : $status, $transacao);
        unset($transacao);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Cancelamento do pagamento do boleto ' . $pagamento->first()->boleto_id . ' aprovado. Detalhes do cancelamento do pagamento enviado para o e-mail: ' . $user->email,
            'class' => 'alert-success'
        ];
    }

    public function checkoutIframe($request, $user)
    {
        return $this->api->checkoutIframe($request, $user);
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

    // Admin
    public function listar()
    {
        $resultados = Pagamento::with(['representante'])->orderBy('updated_at', 'DESC')->paginate(25);

        return Pagamento::listar($resultados);
    }

    public function buscar($busca)
    {
        $resultados = Pagamento::with(['representante'])
            ->where('boleto_id','LIKE','%'.$busca.'%')
            ->orWhere('payment_id','LIKE','%'.$busca.'%')
            ->orWhere('combined_id','LIKE','%'.$busca.'%')
            ->orWhereHas('representante', function ($query) use($busca) {
                $query->where('nome','LIKE','%'.$busca.'%')
                ->orWhere('cpf_cnpj','LIKE','%'.$busca.'%')
                ->orWhere('registro_core','LIKE','%'.$busca.'%');
            })
            ->paginate(25);

        return Pagamento::listar($resultados);
    }
}