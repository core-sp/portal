<?php

namespace App\Exceptions;

use Exception;
use Log;
use App\Events\ExternoEvent;

class PagamentoException extends Exception
{
    private $codes = [
        'CODE_401' => 'Rota não pode ser acessada devido ao uso do Checkout Iframe',
        'CODE_419' => 'Tentativa de pagamento por uma sessão que não é mais válida. Deve refazer o fluxo de pagamento.',
    ];

    private function getException($erro_msg, $cod)
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

    public function reportTransacao($request, $user)
    {
        $msg = in_array($this->getMessage(), array_keys($this->codes)) ? $this->codes[$this->getMessage()] : $this->getMessage();

        Log::error('[Erro: '.$msg.'], [Controller: ' . $request->route()->getAction()['controller'] . '], [Código: '.$this->getCode().'], [Arquivo: '.$this->getFile().'], [Linha: '.$this->getLine().']');
        
        $string = 'Usuário '.$user->id.' ("' . formataCpfCnpj($user->cpf_cnpj) . '", login como: '.$user::NAME_AREA_RESTRITA.') recebeu um código de erro *';
        $string .= $this->getCode().'* ao tentar realizar o pagamento da cobrança *'.$request->route()->parameter('cobranca').'*. Erro registrado no Log de Erros.';
        event(new ExternoEvent($string));
    }

    public function renderTransacao($request, $user)
    {
        $this->reportTransacao($request, $user);
        $temp = $this->getException($this->getMessage(), $this->getCode());
        $msg = isset($temp) ? $temp : 'Erro ao processar o pagamento. Código de erro: ' . $this->getCode();
        $this->message = $msg;

        return $this->getMessage();
    }

    public function render($request, $code = null)
    {
        $msg = isset($code) && in_array($code, array_keys($this->codes)) ? $this->codes[$code] : $this->getMessage();
        Log::error('[Erro: '.$this->getMessage().'], [Mensagem: ' . $msg . '], [Controller: ' . $request->route()->getAction()['controller'] . '], [Código: '.$this->getCode().'], [Arquivo: '.$this->getFile().'], [Linha: '.$this->getLine().']');
        
        abort($this->getCode(), $msg);
    }
}
