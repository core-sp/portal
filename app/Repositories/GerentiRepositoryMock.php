<?php

namespace App\Repositories;

use App\Representante;
use App\Connections\FirebirdConnection;
use App\Repositories\GerentiRepositoryInterface;

class GerentiRepositoryMock implements GerentiRepositoryInterface{
    public function gerentiChecaLogin($registro, $cpfCnpj, $email = null)
    {
        $cpfCnpj = apenasNumeros($cpfCnpj);

        // RC Teste 1 (PF)
        if($cpfCnpj == '86294373085') {
            $resultado = [
                "SITUACAO" => "Ativo", 
                "REGISTRONUM" => "0000000001", 
                "ASS_ID" => "000001", 
                "NOME" => "RC Teste 1", 
                "EMAILS" => "desenvolvimento@core-sp.org.br;desenvolvimento2@core-sp.org.br"
            ];
        }
        // RC Teste 2 (PJ)
        elseif($cpfCnpj == '11748345000144') {
            $resultado = [
                "SITUACAO" => "Ativo", 
                "REGISTRONUM" => "0000000002", 
                "ASS_ID" => "000002", 
                "NOME" => "RC Teste 2", 
                "EMAILS" => "desenvolvimento@core-sp.org.br"
            ];
        }
        else {
            $resultado = [
                "SITUACAO" => "Inexistente", 
                "EMAILS" => ""
            ];
        }

        $verificaEmail = explode(';', $resultado['EMAILS']);

        if($resultado['SITUACAO'] !== 'Ativo')
            return ['Error' => 'O cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique as informações inseridas.'];
        elseif(!in_array($email, $verificaEmail))
            return ['Error' => 'O email informado não corresponde ao cadastro informado. Por favor, insira o email correto.'];
        else
            return $resultado;
    }

    public function gerentiCobrancas($ass_id)
    {
        $cobrancas = $this->gerentiBolestosLista($ass_id);
        $cobrancas = utf8_converter($cobrancas);
        
        $anuidades = [];
        $outros = [];

        foreach($cobrancas as $cobranca) {
            if (strpos($cobranca['DESCRICAO'], 'Anuidade') !== false) {
                array_push($anuidades, $cobranca);
            } else {
                array_push($outros, $cobranca);
            }
        }
        
        $resultado = [
            'anuidades' => $anuidades,
            'outros' => $outros
        ];

        return $resultado;
    }

    /**
     * Método para formatar os dados de endereço do GERENTI para emissão de Certidão
     */
    public function gerentiEnderecoFormatado($ass_id) 
    {
        $enderecoGerenti = $this->gerentiEnderecos($ass_id);

        $enderecoFormatado = $enderecoGerenti["Logradouro"];
        
        if(!empty($enderecoGerenti["Complemento"])) {
            $enderecoFormatado .= ", " . $enderecoGerenti["Complemento"];
        }

        $enderecoFormatado .= ", " . $enderecoGerenti["Bairro"];
        $enderecoFormatado .= " - " . $enderecoGerenti["Cidade"] . "/" . $enderecoGerenti["UF"];
        $enderecoFormatado .= " - CEP: " . $enderecoGerenti["CEP"];

        return $enderecoFormatado;
    }

    public function gerentiDadosGerais($tipoPessoa, $ass_id)
    {
        if($tipoPessoa == Representante::PESSOA_FISICA) {
            $dados = $this->gerentiDadosGeraisPF($ass_id);
            $dados = formataDataGerentiRecursive($dados);
            $rg = ['RG' => $dados['identidade'] . '<span class="light">, expedido em</span> ' . $dados['expedicao'] . '<span class="light">, por</span> ' . $dados['emissor']];
            unset($dados['expedicao'], $dados['emissor'], $dados['identidade']);
            $novoDados = $rg + $dados;
            
            return utf8_converter($novoDados);
        } else {
            $dados = $this->gerentiDadosGeraisPJ($ass_id);
            $dados = formataDataGerentiRecursive($dados);
            $dados = utf8_converter($dados);
    
            if(!empty($dados['Responsável Técnico'])) {
                $rtsArray = explode(';', $dados['Responsável Técnico']);
                $total = count($rtsArray) - 1;
                $rtArray = explode('-', $rtsArray[$total]);
                $rt = ['Responsável técnico' => $rtArray[1] . ' (' . $rtArray[0] . ')'];
    
                unset($dados['Responsável Técnico']);
    
                $novosDados = $rt + $dados;
            } else {
                $novosDados = $dados;
            }

            return $novosDados;
        }
    }

    public function gerentiDadosGeraisPF($ass_id)
    {
        // RC Teste 1 (PF)
        if($ass_id == "000001") {
            $resultado = [
                "Data de cadastro" => "2006-04-12", 
                "Data de nascimento" => "30/09/1962",
                "identidade" => "11.111.111-1",
                "expedicao" => "05/03/2012",
                "emissor" => "SSP-SP",
                "Estado civil" => "CASADO(A)",
                "Naturalidade" => "SÃO PAULO- SP",
                "Nacionalidade" => "BRASILEIRA",
                "Nome do pai" => "PAI 1",
                "Nome da mãe" => "MAE 1",
                "Data de homologação" => "2006-04-18",
                "Sexo" => "MASCULINO",
                "Tipo de pessoa" => "Física RT",
                "Regional" => "SÃO PAULO",
                "Data de início" => "",
                "Registro secundário" => "Não",
                "Core de origem" => null
            ];
        }

        return $resultado;
    }

    public function gerentiDadosGeraisPJ($ass_id)
    {
        // RC Teste 2 (PJ)
        if($ass_id == "000002") {
            $resultado = [
                "Data de cadastro" => "2016-03-02",
                "Data do Registro Social" => "2016-03-02",
                "Data de homologação" => "2016-03-07",
                "Nire" => "",
                "Tipo de pessoa" => "Jurídica ",
                "Regional" => "SÃO PAULO",
                "Data do reg. na junta comercial" => null,
                "Data de início" => null,
                "Registro secundário" => null,
                "Core de origem" => null,
                "Inscrição estadual" => null,
                "Tipo de empresa" => "Empresa Individual",
                "Inscrição municipal" => null,
                "Responsável Técnico" => ""
            ];
        }

        return $resultado;
    }

    public function gerentiEnderecos($ass_id)
    {
        $resultado = [
            "Logradouro" => "AVENIDA BRIGADEIRO LUÍS ANTÔNIO, 613",
            "Complemento" => "TERREO",
            "Bairro" => "BELA VISTA",
            "CEP" => "01317000",
            "Cidade" => "SAO PAULO",
            "UF" => "SP"
        ];
  
        return $resultado;
    }

    /**
     * Método integrado realiza atualização o GERENTI. Sem integração esse método não deve fazer nada.
     */
    public function gerentiInserirEndereco($ass_id, $infos) {}

    /**
     * Método não é chamado em nenhum lugar.
     * TODO - verificar sia exclusão.
     */
    public function gerentiEnderecoInfos($ass_id, $sequencia)
    {
        abort(500, "Ambiente não integrado");
    }

    public function gerentiContatos($ass_id)
    {
        $resultado[0] = [
            "CXP_ASS_ID" => 211304,
            "CXP_CNT_ID" => 1,
            "CXP_SITUACAO" => 1,
            "USU_CODIGO" => 13,
            "SYS_LAST_UPDATE" => "2018-05-08 11:42:03",
            "CXP_VALOR" => "11999999999",
            "CXP_TIPO" => 8,
            "CXP_OBS" => "",
            "CXP_STATUS" => 1
        ] ;

        $resultado[1] = [
            "CXP_ASS_ID" => 52606, 
            "CXP_CNT_ID" => 5,
            "CXP_SITUACAO" => 0,
            "USU_CODIGO" => 178,
            "SYS_LAST_UPDATE" => "2019-11-21 00:00:00",
            "CXP_VALOR" => "desenvolvimento@core-sp.org.br",
            "CXP_TIPO" => 3,
            "CXP_OBS" => "E-mail de teste Gerenti x Portal",
            "CXP_STATUS" => 1
        ] ;

        return $resultado;
    }

    /**
     * Método integrado realiza atualização o GERENTI. Sem integração esse método não deve fazer nada.
     */
    public function gerentiInserirContato($ass_id, $conteudo, $tipo, int $id = 0) {}

    /**
     * Método integrado realiza atualização o GERENTI. Sem integração esse método não deve fazer nada.
     */
    public function gerentiDeletarContato($ass_id, $request) {}

    public function gerentiBolestosLista($ass_id)
    {
        // Uma anuidade.
        $resultado[0] = [
            "DESCRICAO" => "Anuidade 2020 (Parcela Única)(Jan a Mai)",
            "VENCIMENTO" => "2020-04-30",
            "VALOR" => "244.17",
            "MULTA" => "0.00",
            "JUROS" => "0.00",
            "CORRECAO" => "0.00",
            "RESIDUO" => "0.00",
            "TOTAL" => "244.17",
            "BOLETO" => null,
            "VENCIMENTOBOLETO" => null,
            "LINK" => null,
            "SITUACAO" => "Pago"
        ];
        // Parcelamento em 3x.
        $resultado[1] = [
            "DESCRICAO" => "Parcela 3/3 Acordo (01/2017 - 12/2019)",
            "VENCIMENTO" => "2021-05-27",
            "VALOR" => "137.03",
            "MULTA" => "2.74",
            "JUROS" => "28.43",
            "CORRECAO" => "7.65",
            "RESIDUO" => "0.00",
            "TOTAL" => "175.85",
            "BOLETO" => "12345",
            "VENCIMENTOBOLETO" => "2021-06-28",
            "LINK" => "https://www.core-sp.org.br/",
            "SITUACAO" => "Em aberto"
        ];
        $resultado[2] = [
            "DESCRICAO" => "Parcela 2/3 Acordo (01/2017 - 12/2019)",
            "VENCIMENTO" => "2021-04-27",
            "VALOR" => "137.03",
            "MULTA" => "2.74",
            "JUROS" => "28.43",
            "CORRECAO" => "7.65",
            "RESIDUO" => "0.00",
            "TOTAL" => "175.85",
            "BOLETO" => "12345",
            "VENCIMENTOBOLETO" => "2021-05-28",
            "LINK" => "https://www.core-sp.org.br/",
            "SITUACAO" => "Em aberto"
        ];
        $resultado[3] = [
            "DESCRICAO" => "Parcela 1/3 Acordo (01/2017 - 12/2019)",
            "VENCIMENTO" => "2021-03-27",
            "VALOR" => "137.03",
            "MULTA" => "2.74",
            "JUROS" => "28.43",
            "CORRECAO" => "7.65",
            "RESIDUO" => "0.00",
            "TOTAL" => "175.85",
            "BOLETO" => "12345",
            "VENCIMENTOBOLETO" => "2021-04-28",
            "LINK" => "https://www.core-sp.org.br/",
            "SITUACAO" => "Em aberto"
        ];
       
        return $resultado;
    }

    public function gerentiStatus($ass_id)
    {
        return "Situação: Em dia.";
    }

    /**
     * Sem relação com outras funcionalidades, retornando apenas nulo.
     */
    public function gerentiAnuidadeVigente($cpfCnpj)
    {
        return null;
    }

    /**
     * Método usado no Admin para realizar buscas no GERENTI, sem relação com outras funcionalidades. Aborta.
     */
    public function gerentiBusca($registro, $nome, $cpfCnpj)
    {
        $resultado[0] = [
            "ASS_ID" => "000001",
            "ASS_ATIVO" => "T",
            "ASS_ENTIDADE" => 0,
            "ASS_TP_ASSOC" => 2,
            "ASS_REGISTRO" => "0000000001",
            "ASS_NOME" => "RC Teste 1",
            "ASS_CPF_CGC" => "86294373085",
            "ASS_DT_ADMISSAO" => "2006-04-12",
            "ASS_DT_REG_SOCIAL" => "2006-04-12",
            "ASS_TP_PESSOA" => "F",
            "ASS_USU_CADASTRO" => 0,
            "ASS_TP_REGIAO" => " ",
            "ASS_DT_UPDATE" => "1899-12-30",
            "USU_CODIGO" => 121,
            "SYS_LAST_UPDATE" => "2019-05-15 12:26:32",
            "TIPO" => "Física RT",
            "CANCELADO" => "F",
            "EXCORE" => 0
        ];

        $resultado[1] = [
            "ASS_ID" => "000002",
            "ASS_ATIVO" => "T",
            "ASS_ENTIDADE" => 0,
            "ASS_TP_ASSOC" => 1,
            "ASS_REGISTRO" => "0000000002",
            "ASS_NOME" => "RC Teste 2",
            "ASS_CPF_CGC" => "11748345000144",
            "ASS_DT_ADMISSAO" => "2006-04-12",
            "ASS_DT_REG_SOCIAL" => "2006-04-12",
            "ASS_TP_PESSOA" => "J",
            "ASS_USU_CADASTRO" => 0,
            "ASS_TP_REGIAO" => " ",
            "ASS_DT_UPDATE" => "1899-12-30",
            "USU_CODIGO" => 121,
            "SYS_LAST_UPDATE" => "2019-05-15 12:26:32",
            "TIPO" => "Jurídica (Jurídica Ltda)",
            "CANCELADO" => "F",
            "EXCORE" => 0
        ];

        return $resultado;
    }

    /**
     * Consulta o GERENTI para verificar se Representante Comercial está ativo ou não usando CPF/CNPJ (SITUACAO).
     * Retorna outras informações relacionadas ao Representante Comercial.
     */
    public function gerentiAtivo($cpfCnpj)
    {
        if($cpfCnpj == "86294373085") {
            $resultado[0] = [
                "SITUACAO" => "Ativo", 
                "REGISTRONUM" => "0000000001", 
                "ASS_ID" => "000001", 
                "NOME" => "RC Teste 1", 
                "EMAILS" => "desenvolvimento@core-sp.org.br"
            ];
        }
        elseif($cpfCnpj == "11748345000144") {
            $resultado[0] = [
                "SITUACAO" => "Ativo", 
                "REGISTRONUM" => "0000000002", 
                "ASS_ID" => "000002", 
                "NOME" => "RC Teste 2", 
                "EMAILS" => "desenvolvimento@core-sp.org.br"
            ];
        }
        elseif($cpfCnpj == "56983238010") {
            $resultado[0] = [
                "SITUACAO" => "Ativo", 
                "REGISTRONUM" => "0000000003", 
                "ASS_ID" => "000003", 
                "NOME" => "RC Teste Tres", 
                "EMAILS" => "novo_rc@teste.com"
            ];
        }else
            $resultado[0] = [
                "SITUACAO" => "Não encontrado", 
                "REGISTRONUM" => null, 
                "ASS_ID" => null, 
                "NOME" => null, 
                "EMAILS" => null
            ];

        return $resultado;
    }

    /**
     * Verifica no GERENTI se é possível emitir uma certidão para o Representante Comercial de acordo com o ASS_ID. Em caso negativo, uma flag com o valor "0" será retornada.
     * Em caso positivo, uma flag com o valor "1" será retornada juntamente com informações da certidão (número, código, data e hora da emissão)
     * 
     * Adicionar três parametros (web user, tericero parametro nulo e o tipo) na chamada da procedure original
     */
    public function gerentiEmitirCertidao($ass_id) 
    {
        return [
            'EMISSAO' => 1,
            'NUMERO' => 2,
            'CODVALIDACAO' => 'abcde',
            'DATAEMISSAO' => '01/01/2021',
            'HORA' => '00:00:00',
            'DATAVALIDADE' => '01/01/2022',
            'NOME' => 'RC Teste 1', 
            'CPFCNPJ' => '86294373085', 
            'REGISTRO' => '0000000001', 
            'DATAREGISTRO' => '01/01/1999',
            'TIPOEMPRESA' => 'Empresa Teste', 
            'RESPTECNICOS' => 'Nome do RT', 
            'REGISTROSRTS' => 'Registro do RT',
            'ENDERECOCOMPLETO' => 'Rua Teste'
        ];
    }

    /**
     * Recupera no GERENTI as certidões que foram emitidas para o Representante Comercial de acordo com o ASS_ID.
     * 
     * 11 - Regularidade?
     * 12 - Parcelamento?
     */
    public function gerentiListarCertidoes($ass_id, $tipo) 
    {
        $resultado[0] = [
            'NUMERO' => '1',
            'SITUACAO' => 'Ativa',
            'CODVALIDACAO' => '123456789',
            'DATAEMISSAO' => '01/01/2021',
            'HORAEMISSAO' => '00:00:00',
            'VALIDADE' => '01/01/2022',
        ];

        $resultado[1] = [
            'NUMERO' => '2',
            'SITUACAO' => 'Suspensa',
            'CODVALIDACAO' => '987654321',
            'DATAEMISSAO' => '02/02/2021',
            'HORAEMISSAO' => '00:00',
            'VALIDADE' => '02/02/2022',
        ];

        return $resultado;
    }

    /**
     * Verifica no GERENTI a autenticidade e validade de uma certidão. Retorna uma flag que indica as seguintes situações: "0" (inexistente), "1" (válida), "2" (suspensa), "3" (vencida).
     * Caso a flag indique que certidão está válida, informações sobre o Representante Comercial serão retornadas (Nome, Registro, CPF_CNPJ, data de validade da certidão). 
     */
    public function gerentiAutenticaCertidao($numero, $codigo, $data, $hora) 
    {
        return [
            'SITUACAO' => 'Válida',
            'DATAVALIDADE' => '02/02/2022'
        ];
    }

    public function gerentiGetSegmentosByAssId($ass_id) 
    {
        // Segmentos igual ao do BDO
        return [
            [
                "SEGMENTO" => "Alimentício",
                0 => "Alimentício",
            ],
        ];
    }
}