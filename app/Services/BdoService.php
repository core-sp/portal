<?php

namespace App\Services;

use App\Contracts\BdoServiceInterface;
use App\Repositories\GerentiRepositoryInterface;

class BdoService implements BdoServiceInterface {

    public function __construct()
    {

    }

    public function viewPerfilRC($rep, GerentiRepositoryInterface $gerentiRepository)
    {
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
        $segmento = $gerentiRepository->gerentiGetSegmentosByAssId($rep->ass_id);
        $segmento = !empty($segmento) ? $segmento[0]["SEGMENTO"] : $segmento;
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
            'rep' => $rep, 
            'emails' => $emails, 
            'telefones' => $telefones, 
            'segmento' => $segmento, 
            'endereco' => $end
        ];
    }
}