<?php

namespace App\Services;

use App\Representante;
use App\Contracts\RepresentanteServiceInterface;
use App\Repositories\GerentiRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class RepresentanteService implements RepresentanteServiceInterface {

    // private $variaveisLog;
    private $txt_anuidade;

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

    private function valorAnuidadeVigente($anuidades)
    {
        $filtered = Arr::where($anuidades, function ($value, $key) {
            return $value['DESCRICAO'] == $this->txt_anuidade;
        });

        if(empty($filtered))
            return null;

        $filtered = $filtered[0];

        if(isset($filtered['VENCIMENTO']) && (Carbon::parse($filtered['VENCIMENTO']) < now()->format('Y-m-d')))
            return null;

        return (float) $filtered['VALOR'];
    }

    public function anuidadeUnificada(GerentiRepositoryInterface $gerenti, $anuidades_pj, $tipoPessoa, $ass_id)
    {
        $this->txt_anuidade = 'Anuidade ' . date('Y') . ' (Parcela Única)';

        if($tipoPessoa != Representante::PESSOA_JURIDICA)
            return null;

        $pj = $this->valorAnuidadeVigente($anuidades_pj);
        if(is_null($pj))
            return null;

        // -------------- RT ----------------------

        $dg = utf8_converter($gerenti->gerentiDadosGeraisPJ($ass_id));
        if(!isset($dg['Responsável Técnico']) || empty($dg['Responsável Técnico']))
            return null;

        $rtsArray = explode(';', $dg['Responsável Técnico']);
        $total = count($rtsArray) - 1;
        $rtArray = explode('-', $rtsArray[$total]);
        
        if(!isset($rtArray[2]))
            return null;

        $rt_ass_id = $rtArray[2];

        $anuidades_rt = utf8_converter($gerenti->gerentiBolestosLista($rt_ass_id));

        $rt = $this->valorAnuidadeVigente($anuidades_rt);
        if(is_null($rt))
            return null;
        
        return ["total" => $pj + $rt, "texto" => $this->txt_anuidade];
    }
}