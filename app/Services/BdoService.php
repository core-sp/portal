<?php

namespace App\Services;

use App\Contracts\BdoServiceInterface;
use App\Services\BdoAdminService;
use App\Repositories\GerentiRepositoryInterface;
use App\BdoRepresentante;
use Illuminate\Support\Str;

class BdoService implements BdoServiceInterface {

    public function __construct()
    {

    }

    public function admin()
    {
        return new BdoAdminService();
    }

    public function viewPerfilRC($rep, GerentiRepositoryInterface $gerentiRepository = null)
    {
        if(is_null($gerentiRepository))
            return $rep->bdoPerfis->whereIn('status->status_final', [
                '', BdoRepresentante::STATUS_ADMIN_FINAL, BdoRepresentante::STATUS_ACAO_ACEITO, BdoRepresentante::STATUS_ACAO_RECUSADO
            ])->last();

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
            'endereco' => $end,
            'seccional' => $gerentiRepository->gerentiDadosGerais($rep->tipoPessoa(), $rep->ass_id)["Regional"]
        ];
    }

    public function cadastrarPerfil($rep, $dados, GerentiRepositoryInterface $gerentiRepository)
    {
        $dados['regioes->seccional'] = $dados['regioes_seccional'];
        if(isset($dados['regioes_municipios']))
            $dados['regioes->municipios'] = $dados['regioes_municipios'];
        $dados['status'] = '{}';

        unset($dados['checkbox-tdu']);
        unset($dados['_token']);
        unset($dados['nome']);
        unset($dados['core']);
        unset($dados['regioes_seccional']);
        unset($dados['regioes_municipios']);

        $bdo_perfil = $rep->bdoPerfis()->create($dados);

        $segmento = $gerentiRepository->gerentiGetSegmentosByAssId($rep->ass_id);
        $dados['segmento_gerenti'] = !empty($segmento) ? mb_strtoupper($segmento[0]["SEGMENTO"]) : mb_strtoupper($segmento);
        $dados['seccional_gerenti'] = mb_strtoupper($gerentiRepository->gerentiDadosGerais($rep->tipoPessoa(), $rep->ass_id)["Regional"]);
        $dados['em_dia'] = Str::contains(trim($gerentiRepository->gerentiStatus($rep->ass_id)), 'Em dia');

        $criado = $bdo_perfil->setores($dados);

        return $criado ? $bdo_perfil->fresh() : collect();
    }
}