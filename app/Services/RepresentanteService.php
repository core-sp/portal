<?php

namespace App\Services;

use App\Representante;
use App\Contracts\RepresentanteServiceInterface;
use App\Repositories\GerentiRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

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

    public function dadosBdoGerenti($rep, GerentiRepositoryInterface $gerentiRepository)
    {
        if(session()->exists('dados_bdo') && Arr::has(session('dados_bdo'), [
            'ativo', 'seccional', 'em_dia', 'emails', 'telefones', 'segmento', 'endereco'
        ])) 
            return session('dados_bdo');

        $segmento = $gerentiRepository->gerentiGetSegmentosByAssId($rep->ass_id);
        $segmento = !empty($segmento) ? mb_strtoupper($segmento[0]["SEGMENTO"]) : null;

        $endereco = $gerentiRepository->gerentiEnderecos($rep->ass_id);
        $end = '';
        foreach($endereco as $key => $campo)
            switch ($key) {
                case 'Logradouro':
                case 'UF':
                    $end .= $campo;
                    break;
                case 'Complemento':
                    $end .= empty($campo) ? '' : ', ' . $campo;
                    break;
                case 'Bairro':
                    $end .= ' - ' . $campo . '. ';
                    break;
                case 'CEP':
                    $end .= 'CEP: ' . $campo . '. ';
                    break;
                case 'Cidade':
                    $end .= $campo . ' - ';
                    break;
        }

        $contatos = $gerentiRepository->gerentiContatos($rep->ass_id);
        $emails = array();
        $telefones = array();

        foreach($contatos as $contato){
            switch ($contato['CXP_TIPO']) {
                case 3:
                    array_push($emails, $contato['CXP_VALOR']);
                    break;
                case 5:
                    break;
                default:
                    array_push($telefones, $contato['CXP_VALOR']);
                    break;
            }
        }

        return [
            'ativo' => utf8_converter($gerentiRepository->gerentiAtivo(apenasNumeros($rep->cpf_cnpj)))[0]['SITUACAO'] == 'Ativo',
            'seccional' => mb_strtoupper($gerentiRepository->gerentiDadosGerais($rep->tipoPessoa(), $rep->ass_id)["Regional"]),
            'em_dia' => Str::contains(trim($gerentiRepository->gerentiStatus($rep->ass_id)), 'Em dia'),
            'emails' => $emails, 
            'telefones' => $telefones, 
            'segmento' => $segmento, 
            'endereco' => $end,
        ];
    }
}