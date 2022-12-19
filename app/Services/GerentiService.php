<?php

namespace App\Services;

use App\Contracts\GerentiServiceInterface;
use App\Repositories\GerentiRepositoryInterface;

class GerentiService implements GerentiServiceInterface {

    private $gerentiRepository;

    public function __construct(GerentiRepositoryInterface $gerentiRepository)
    {
        $this->gerentiRepository = $gerentiRepository;
    }

    public function getEnderecoContatoPagamento($user)
    {
        $contatos = $this->gerentiRepository->gerentiContatos($user->ass_id);
        $enderecos = $this->gerentiRepository->gerentiEnderecos($user->ass_id);
        if(isset($enderecos['Logradouro'])){
            $cobranca_dados['street'] = strpos($enderecos['Logradouro'], ',') !== false ? 
            substr($enderecos['Logradouro'], 0, strpos($enderecos['Logradouro'], ',')) : substr($enderecos['Logradouro'], 0, 60);
            $cobranca_dados['street_number'] = strpos($enderecos['Logradouro'], ',') !== false ? 
            trim(substr($enderecos['Logradouro'], strpos($enderecos['Logradouro'], ',') + 1)) : '';
        }
        $cobranca_dados['complementary'] = isset($enderecos['Complemento']) ? substr(str_replace(',', ' ', $enderecos['Complemento']), 0, 60) : '';
        $cobranca_dados['neighborhood'] = isset($enderecos['Bairro']) ? substr(str_replace(',', ' ', $enderecos['Bairro']), 0, 40) : '';
        $cobranca_dados['city'] = isset($enderecos['Cidade']) ? substr(str_replace(',', ' ', $enderecos['Cidade']), 0, 20) : '';
        $cobranca_dados['state'] = isset($enderecos['UF']) ? substr($enderecos['UF'], 0, 20) : '';
        $cobranca_dados['zipcode'] = isset($enderecos['CEP']) ? substr(str_replace('-', '', $enderecos['CEP']), 0, 8) : '';
        foreach($contatos as $contato){
            if(in_array($contato['CXP_TIPO'], ['8', '7', '6', '1', '2', '4']) && (strlen($contato['CXP_VALOR']) > 5)){
                $cobranca_dados['phone_number'] = apenasNumeros($contato['CXP_VALOR']);
                break;
            }
        }

        return $cobranca_dados;
    }
}