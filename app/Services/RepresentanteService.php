<?php

namespace App\Services;

use App\Representante;
use App\Contracts\RepresentanteServiceInterface;
use App\Repositories\GerentiRepositoryInterface;
use Carbon\Carbon;

class RepresentanteService implements RepresentanteServiceInterface {

    // private $variaveisLog;

    public function __construct()
    {
    
    }

    private function verificaSeAtivo($cpf_cnpj)
    {
        $cpfCnpj = apenasNumeros($cpf_cnpj);
        $representante = Representante::where('cpf_cnpj', $cpfCnpj)->first();

        if(isset($representante)) 
        {
            if($representante->ativo === 0)
                return [
                    'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta.',
                    'class' => 'alert-warning'
                ];
            else 
                return [];
        } else
            return [
                'message' => 'Senha incorreta e/ou CPF/CNPJ não encontrado.',
                'class' => 'alert-danger'
            ];
    }

    private function verificaGerentiLogin($cpfCnpj, GerentiRepositoryInterface $gerenti)
    {
        $cpfCnpj = apenasNumeros($cpfCnpj);
        $registro = Representante::where('cpf_cnpj', $cpfCnpj)->first();

        if(isset($registro)) {
            $checkGerenti = $gerenti->gerentiChecaLogin($registro->registro_core, $cpfCnpj);

            if($checkGerenti === false) 
                return [
                    'message' => 'Desculpe, mas o cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique se todas as informações foram inseridas corretamente.',
                    'class' => 'alert-danger'
                ];
            return [];
        }
    }

    public function verificaAtivoAndGerenti($cpfCnpj, GerentiRepositoryInterface $gerenti)
    {
        $ativo = $this->verificaSeAtivo($cpfCnpj);

        if(!empty($ativo))
            return $ativo;
        return $this->verificaGerentiLogin($cpfCnpj, $gerenti);
    }
}