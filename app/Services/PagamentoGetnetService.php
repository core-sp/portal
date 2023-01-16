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
    const TEXTO_LOG_SISTEMA = '[Rotina Portal - Transação Getnet] - ';
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

    private function arrayBase($dados)
    {
        $temp = str_replace('_3ds', '', $dados['tipo_pag']);
        $base = [
            'cobranca_id' => $dados['cobranca'],
            'total' => amountCentavosToReal($dados['amount']),
            'forma' => mb_strtolower($temp),
            'parcelas' => $dados['parcelas_1'],
            'tipo_parcelas' => mb_strtoupper($dados['tipo_parcelas_1']),
        ];

        return $base;
    }

    private function arrayConfirmed($transacao, $status)
    {
        if(is_array($status) && (($status[0] == 'CONFIRMED') && ($status[1] == 'CONFIRMED')))
        {
            $base = [
                'transacao_temp' => null,
                'authorized_at' => $transacao['payments'][0]['credit_confirm']['confirm_date'],
                'status' => mb_strtoupper($status[0]),
            ];
            $base2 = [
                'transacao_temp' => null,
                'authorized_at' => $transacao['payments'][1]['credit_confirm']['confirm_date'],
                'status' => mb_strtoupper($status[1]),
            ];

            return [$base, $base2];
        }
    }

    private function arrayApproved($transacao, $status, $dados)
    {
        $base = $this->arrayBase($dados);
        $temp = $base['forma'];

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
    }

    private function arrayAuthorized($transacao, $status, $dados)
    {
        $base = $this->arrayBase($dados);

        $base['combined_id'] = $transacao['combined_id'];
        $base_2 = $base;
        $base['transacao_temp'] = json_encode($transacao, JSON_FORCE_OBJECT);
        $base['authorized_at'] = $transacao['payments'][0]['credit']['authorized_at'];
        $base['status'] = mb_strtoupper($status[0]);
        $base['payment_tag'] = $transacao['payments'][0]['payment_tag'];
        $base['payment_id'] = $transacao['payments'][0]['payment_id'];
        $base['total_combined'] = amountCentavosToReal($transacao['payments'][0]['amount']);
        $base['bandeira'] = mb_strtolower($transacao['payments'][0]['credit']['brand']);
        // cartão 2
        $base_2['tipo_parcelas'] = mb_strtoupper($dados['tipo_parcelas_2']);
        $base_2['parcelas'] = $dados['parcelas_2'];
        $base_2['authorized_at'] = $transacao['payments'][1]['credit']['authorized_at'];
        $base_2['status'] = mb_strtoupper($status[1]);
        $base_2['payment_tag'] = $transacao['payments'][1]['payment_tag'];
        $base_2['payment_id'] = $transacao['payments'][1]['payment_id'];
        $base_2['total_combined'] = amountCentavosToReal($transacao['payments'][1]['amount']);
        $base_2['bandeira'] = mb_strtolower($transacao['payments'][1]['credit']['brand']);

        return [$base, $base_2];
    }

    private function aposRotinaTransacao($user, $pagamentos, $status, $transacao = null, $errorCode = null, $descricao = null)
    {
        $pagamento = Pagamento::getFirst($pagamentos);
        
        $string = !$this->via_sistema ? Pagamento::logAposTransacaoByUser($user, $pagamento, $status, request()->all(), session()->all(), cache()) : 
        Pagamento::logAposTransacaoByNotification($user, $pagamento, $status, $errorCode, $descricao, self::TEXTO_LOG_SISTEMA);
        
        event(new ExternoEvent($string));

        Mail::to($user->email)->queue(new PagamentoMail($pagamentos));

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
    }

    private function createViaNotificacao($dados)
    {
        $this->via_sistema = true;
        $user = null;
        $cpf_cnpj = substr($dados['customer_id'], 0, strpos($dados['customer_id'], '_'));

        if(strpos($dados['customer_id'], 'rep') !== false)
            $user = \App\Representante::where('cpf_cnpj', $cpf_cnpj)->first();

        if(!isset($user))
            throw new \Exception('Usuário não encontrado no Portal pela customer_id *' . $dados['customer_id'] . '* ao receber a notificação da Getnet após transação.', 404);
        
        $pagamento = $user->pagamentos()->create([
            'payment_id' => $dados['payment_id'],
            'cobranca_id' => $dados['order_id'],
            'total' => amountCentavosToReal($dados['amount']),
            'forma' => $dados['payment_type'],
            'parcelas' => $dados['number_installments'],
            'is_3ds' => $dados['payment_type'] == 'debit',
            'tipo_parcelas' => $dados['tipo_parcelas'],
            'bandeira' => $dados['brand'],
            'status' => $dados['status'],
            'authorized_at' => $dados['authorization_timestamp'],
        ]);

        $this->aposRotinaTransacao($user, $pagamento, $dados['status'], $dados);
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

    private function confirmacaoPagamento($transacao, $status, $user = null, $dados = null)
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

            if(isset($user) && isset($dados))
            {
                $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') realizou a autorização de pagamento da cobrança ' . $transacao['order_id'] . ' do tipo *combined*';
                $string .= ' com a combined_id: ' . $transacao['combined_id'] . ', com as payment_ids: ' . json_encode($ids) . ' e payment_tags: ' . json_encode($tags) . '. Aguardando confirmação do pagamento via Portal.';
                event(new ExternoEvent($string));
    
                $arrayPag = $this->arrayAuthorized($transacao, $status, $dados);
                $pagamentos = $user->pagamentos()->createMany($arrayPag);
                Mail::to($user->email)->queue(new PagamentoMail($pagamentos));
            }

            $transacao = $this->api->confirmation($ids, $tags);

            return [
                'transacao' => $transacao,
                'pagamentos' => isset($pagamentos) ? $pagamentos : null
            ];
        }

        return [
            'transacao' => $transacao,
            'pagamentos' => null
        ];
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
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') tentou realizar o pagamento da cobrança ' . $dados['cobranca'] . ' do tipo *' . $dados['tipo_pag'] . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: ' . $transacao['retorno_getnet'];
            event(new ExternoEvent($string));
            return $transacao;
        }

        $status = $this->getStatusTransacao($transacao, $dados['tipo_pag']);
        $resultado = $this->confirmacaoPagamento($transacao, $status, $user, $dados);
        $transacao = $resultado['transacao'];
        $status = $this->getStatusTransacao($transacao, $dados['tipo_pag']);

        $combined = is_array($status) && (($status[0] != 'CONFIRMED') || ($status[1] != 'CONFIRMED'));
        $not_combined = !is_array($status) && ($status != 'APPROVED');

        if($combined || $not_combined)
        {
            $string = 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') tentou realizar o pagamento da cobrança ' . $dados['cobranca'] . ' do tipo *' . $dados['tipo_pag'] . '*';
            $string .= ', mas não foi possível. Retorno da Getnet: Cartão verificado, mas ao realizar o pagamento o status recebido foi ' . json_encode($status);
            event(new ExternoEvent($string));

            return [
                'message-cartao' => '<i class="fas fa-times"></i> Status da transação: ' . is_array($status) ? $status[0] . ' e ' . $status[1] : $status . '. Pagamento da cobrança ' . $dados['cobranca'] . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        $arrayPag = $dados['tipo_pag'] != 'combined' ? $this->arrayApproved($transacao, $status, $dados) : $this->arrayConfirmed($transacao, $status);
        $pagamento = $dados['tipo_pag'] != 'combined' ? $user->pagamentos()->create($arrayPag) : 
        $resultado['pagamentos']->each(function ($item, $key) use($arrayPag) {
           $item->update($arrayPag[$key]);
        })->collect();

        unset($dados);
        $this->aposRotinaTransacao($user, $pagamento, is_array($status) ? $status[0] : $status, $transacao);
        $pagamento = Pagamento::getFirst($pagamento);
        unset($transacao);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Pagamento realizado para a cobrança ' . $pagamento->cobranca_id . '. Detalhes do pagamento enviado para o e-mail: ' . $user->email,
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
            $string = $this->via_sistema ? self::TEXTO_LOG_SISTEMA . 'ID: ' : 'Usuário ' . $user->id . ' ("'. formataCpfCnpj($user->cpf_cnpj) .'", login como: '.$user::NAME_AREA_RESTRITA.') tentou realizar o cancelamento do pagamento da cobrança ' . $dados['cobranca'] . ' do tipo *' . $tipo_pag . '* com a ';
            $string .= $tipo_pag != 'combined' ? 'payment_id: ' . $pagamento->first()->payment_id : 'combined_id: ' . $pagamento->first()->combined_id;
            $string .= ', mas não foi possível. Retorno da Getnet: ' . json_encode($message['msg']);
            event(new ExternoEvent($string));

            return [
                'message-cartao' => '<i class="fas fa-times"></i> Status do cancelamento da transação: ' . is_array($status) ? $status[0] . ' e ' . $status[1] : $status . '. Cancelamento do pagamento da cobrança ' . $dados['cobranca'] . ' não realizado.',
                'class' => 'alert-danger'
            ];
        }

        unset($dados);
        foreach($pagamento as $pag)
            $pag->updateCancelamento($status, $message);
        
        $this->aposRotinaTransacao($user, $pagamento, is_array($status) ? $status[0] : $status, $transacao);
        unset($transacao);

        return [
            'message-cartao' => '<i class="fas fa-check"></i> Cancelamento do pagamento da cobrança ' . $pagamento->first()->cobranca_id . ' aprovado. Detalhes do cancelamento do pagamento enviado para o e-mail: ' . $user->email,
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
        if(isset($dados['checkoutIframe']) && $dados['checkoutIframe'])
        {
            $pagamento = Pagamento::where('cobranca_id', $dados['order_id'])->where('payment_id', $dados['payment_id'])->first();
            if(!isset($pagamento) && ($dados['status'] == 'APPROVED'))
                $this->createViaNotificacao($dados);
        }

        if(isset($dados['updatePagamento']))
        {
            $this->via_sistema = true;
            $dados = $dados['updatePagamento'];
            $status = $this->getStatusTransacao($dados['transacao'], 'combined');

            if(!$dados['pagamentos']->first()->canCancel())
            {
                $pagamento = $dados['pagamentos']->each(function ($item, $key) use($arrayPag) {
                    $item->updateCancelamento('CANCELED', ['dt' => now()->toIso8601ZuluString()]);
                })->first();
                $pagamento::logAposTransacaoByNotification($dados['user'], $pagamento, 'CANCELED', null, null, $this->via_sistema);
                event(new ExternoEvent($string));
                return [];
            }

            $transacao = $this->confirmacaoPagamento($dados['transacao'], $status, $dados['user'])['transacao'];
            $status = $this->getStatusTransacao($transacao, 'combined');
            $arrayPag = $this->arrayConfirmed($transacao, $status);
            $pagamentos = $dados['pagamentos']->each(function ($item, $key) use($arrayPag) {
               $item->update($arrayPag[$key]);
            })->collect();
            $this->aposRotinaTransacao($dados['user'], $pagamentos, $status[0], $transacao);
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
            ->where('cobranca_id','LIKE','%'.$busca.'%')
            ->orWhere('payment_id','LIKE','%'.$busca.'%')
            ->orWhere('combined_id','LIKE','%'.$busca.'%')
            ->orWhereHas('representante', function ($query) use($busca) {
                $query->where('nome','LIKE','%'.$busca.'%')
                ->orWhere('cpf_cnpj','LIKE','%'.apenasNumeros($busca).'%')
                ->orWhere('registro_core','LIKE','%'.apenasNumeros($busca).'%');
            })
            ->orderBy('id', 'DESC')
            ->paginate(25);

        return Pagamento::listar($resultados);
    }
}