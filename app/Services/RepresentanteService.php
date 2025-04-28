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

    public function getRepresentanteByCpfCnpj($cpfCnpj)
    {
        $cpfCnpj = apenasNumeros($cpfCnpj);
        return Representante::where('cpf_cnpj', $cpfCnpj)->where('ativo', 1)->first();
    }

    public function getDadosInscricaoCurso($rep, GerentiRepositoryInterface $gerenti)
    {
        $situacao = trim($gerenti->gerentiStatus($rep->ass_id));
        $tel = $rep->getContatosTipoTelefone($gerenti);
        $rep->telefone = empty($tel) ? '' : $tel[array_keys($tel)[0]]['CXP_VALOR'];
        $rep->registro_core = $gerenti->gerentiAtivo(apenasNumeros($rep->cpf_cnpj))[0]['REGISTRONUM'];

        return [
            'situacao' => $situacao,
            'user_rep' => $rep,
        ];
    }

    public function registrarUltimoAcesso($cpfCnpj)
    {
        $cpfCnpj = apenasNumeros($cpfCnpj);
        return Representante::where('cpf_cnpj', $cpfCnpj)->first()->registrarUltimoAcesso();
    }
}