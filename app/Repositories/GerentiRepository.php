<?php

namespace App\Repositories;

use PDO;
use App\Representante;
use App\Connections\FirebirdConnection;
use App\Repositories\GerentiRepositoryInterface;

class GerentiRepository implements GerentiRepositoryInterface
{
    private $gerentiConnection;

    protected function connect()
    {
        if($this->gerentiConnection == null) {
            $this->gerentiConnection = new FirebirdConnection();
        }
    }

    public function gerentiChecaLogin($registro, $cpfCnpj, $email = null)
    {
        $this->connect();

        $cpfCnpj = apenasNumeros($cpfCnpj);

        $run = $this->gerentiConnection->prepare("select SITUACAO, REGISTRONUM, ASS_ID, NOME, EMAILS from PROCLOGINPORTAL(:registro, :cpfCnpj)");

        $run->execute([
            'registro' => $registro,
            'cpfCnpj' => $cpfCnpj
        ]);
        $resultado = $run->fetchAll();

        $verificaEmail = explode(';', $resultado[0]['EMAILS']);

        if($resultado[0]['SITUACAO'] !== 'Ativo')
            return ['Error' => 'O cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique as informações inseridas.'];
        elseif(!in_array($email, $verificaEmail))
            return ['Error' => 'O email informado não corresponde ao cadastro informado. Por favor, insira o email correto.'];
        else
            return $resultado[0];
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
                $rtArray = explode('-', $dados['Responsável Técnico']);
                $rt = ['Responsável técnico' => $rtArray[1] . ' (' . $rtArray[0] . ')'];
    
                unset($dados['Responsável Técnico']);
    
                $novosDados = $rt + $dados;
            } else {
                $novosDados = $dados;
            }

            return $novosDados;
        }
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

    // public function gerentiSimualdorRefis($ass_id)
    // {
    //     $cobrancas = $this->gerentiBolestosLista($ass_id);
    //     $cobrancas = utf8_converter($cobrancas);

    //     $anuidades = [];
        
    //     foreach($cobrancas as $cobranca) {
    //         if (strpos($cobranca['DESCRICAO'], 'Anuidade') !== false && $cobranca['SITUACAO'] === 'Em aberto' && date('Y', strtotime($cobranca['VENCIMENTO'])) < date('Y')) {

    //             array_push($anuidades, $cobranca);
    //         } 
    //     }

    //     return $anuidades;
    // }

    public function gerentiValoresRefis($ass_id)
    {
        $total = 0;
        $total90 = 0;
        $total80 = 0;
        $total60 = 0;
        $nParcelas90 = 0;
        $nParcelas80 = 0;
        $nParcelas60 = 0;
        $anuidadesRefis = [];
        $statusRepresentante = null;

        $status = $this->gerentiStatus($assId);

        if($status !== Representante::PARCELAMENTO_EM_ABERTO || $status !== Representante::EM_DIA || $status !== Representante::EXECUÇÃO_FISCAL) {
            $cobrancas = $this->gerentiBolestosLista($ass_id);
            $cobrancas = utf8_converter($cobrancas);
          
            $totalAnuidadeIPCA = 0;
            $totalDebito = 0;
            $totalPrescricao = 0;
            $totalAnuidadeIPCAPrescricao = 0;
            $totalDebitoPrescricao = 0;
            $anuidadesRefisPrescricao = [];
            $contagemPrescricao = 0;
            
            foreach($cobrancas as $cobranca) {
                if (strpos($cobranca['DESCRICAO'], 'Anuidade') !== false && $cobranca['SITUACAO'] !== 'Pago' && date('Y', strtotime($cobranca['VENCIMENTO'])) < date('Y') && date('Y', strtotime($cobranca['VENCIMENTO'])) >= date('Y', strtotime('2012-01-01'))) {
                    if(date('Y', strtotime($cobranca['VENCIMENTO'])) < date('Y',strtotime('-4 year'))) {
                        $totalPrescricao += $cobranca['TOTAL'];
                        $totalAnuidadeIPCAPrescricao += ($cobranca['VALOR'] + $cobranca['CORRECAO']);
                        $totalDebitoPrescricao += ($cobranca['MULTA'] + $cobranca['JUROS']);
                        $contagemPrescricao++;
                        array_push($anuidadesRefisPrescricao, $cobranca['DESCRICAO']);
                    }
                    else {
                        $total += $cobranca['TOTAL'];
                        $totalAnuidadeIPCA += ($cobranca['VALOR'] + $cobranca['CORRECAO']);
                        $totalDebito += ($cobranca['MULTA'] + $cobranca['JUROS']);
                        array_push($anuidadesRefis, $cobranca['DESCRICAO']);
                    }
                }
            }
    
            if($contagemPrescricao <= 3) {
                $total += $totalPrescricao;
                $totalAnuidadeIPCA += $totalAnuidadeIPCAPrescricao;
                $totalDebito += $totalDebitoPrescricao;
                $anuidadesRefis = array_merge($anuidadesRefis, $anuidadesRefisPrescricao);
            }

            $total90 = $totalAnuidadeIPCA + ($totalDebito - $totalDebito * 0.9);
            $total80 = $totalAnuidadeIPCA + ($totalDebito - $totalDebito * 0.8);
            $total60 = $totalAnuidadeIPCA + ($totalDebito - $totalDebito * 0.6);
    
            $nParcelas90 = $this->checaNumeroParcelas(1, 12, $total90);
            $nParcelas80 = $this->checaNumeroParcelas(2, 6, $total80);
            $nParcelas60 = $this->checaNumeroParcelas(7, 12, $total60);
    
            $anuidadesRefis = $valores['anuidadesRefis'];
        }

        return ['total' => $total, 'total90' => $total90, 'total80' => $total80, 'total60' => $total60, 'nParcelas90' => $nParcelas90, 'nParcelas80' => $nParcelas80, 'nParcelas60' => $nParcelas60, 'anuidadesRefis' => $anuidadesRefis];
    }

    private function checaNumeroParcelas ($min, $max, $valor) 
    {
        $nParcelas = intval($valor/100);

        if($min > $nParcelas) {
            $min = 0;
            $max = 0;
        }
        elseif($max > $nParcelas) {
            $max = $nParcelas;
        }

        return range($min, $max);
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

    public function gerentiDadosGeraisPF($ass_id)
    {
        $this->connect();

        $query = 'select DATA_CADASTRO "Data de cadastro", DATA_NASCIMENTO "Data de nascimento",
            IDENTIDADE "identidade", DATA_EXPEDICAO "expedicao", ORGAO_EMISSOR "emissor", ESTADO_CIVIL "Estado civil", 
            NATURALIDADE "Naturalidade", NACIONALIDADE "Nacionalidade", NOME_PAI "Nome do pai", NOME_MAE "Nome da mãe", 
            DATAHOMOLOGACAO "Data de homologação", SEXO "Sexo", TIPOPESSOA "Tipo de pessoa", REGIONAL "Regional", 
            DT_INICIO "Data de início", REG_SECUNDARIO "Registro secundário", CORE_ORIGEM "Core de origem"
            from PROCPORTALDADOSGERAISPF(:ass_id)';

        $run = $this->gerentiConnection->prepare($query);
        
        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    public function gerentiDadosGeraisPJ($ass_id)
    {
        $this->connect();
        
        $query = 'select DATACADASTRO "Data de cadastro", DATAREGSOCIAL "Data do Registro Social", 
            DATAHOMOLOGACAO "Data de homologação", NIRE "Nire", TIPOPESSOA "Tipo de pessoa", 
            REGIONAL "Regional", DATA_JUNTA "Data do reg. na junta comercial", DT_INICIO "Data de início",
            REG_SECUNDARIO "Registro secundário", CORE_ORIGEM "Core de origem", INSCR_ESTADUAL "Inscrição estadual", 
            TIPO_EMPRESA "Tipo de empresa", INSCR_MUNICIPAL "Inscrição municipal", RESPTEC "Responsável Técnico"
            from PROCPORTALDADOSGERAISPJ(:ass_id)';

        $run = $this->gerentiConnection->prepare($query);
        
        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    public function gerentiEnderecos($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select ENDERECO "Logradouro", END_CONPLEMENTO "Complemento", 
            END_BAIRRO "Bairro", END_CEP "CEP", END_MUNICIPIO "Cidade", END_ESTADO "UF"
            from SP_PEGA_ENDER(:ass_id, 1)');

        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    public function gerentiInserirEndereco($ass_id, $infos)
    {
        $sequencia = $this->gerentiBuscarSequenciaEndereco($ass_id);

        $cep = apenasNumeros($infos['cep']);

        $this->connect();

        $run = $this->gerentiConnection->prepare("execute procedure SP_ENDERECOS_UI(
            ".$ass_id.", ".$sequencia.", '', :logradouro, :estado, :municipio, :numero, :complemento,
            :bairro, :cep, 'T', '', 'F', 210, CAST('NOW' AS DATE), 'T', 'F'
        )");

        $run->execute([
            'logradouro' => $infos['logradouro'],
            'estado' => $infos['estado'],
            'municipio' => $infos['municipio'],
            'numero' => $infos['numero'],
            'complemento' => $infos['complemento'],
            'bairro' => $infos['bairro'],
            'cep' => $cep
        ]);
    }

    public function gerentiBuscarSequenciaEndereco($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select Max(end_sequencia) + 1 "sequencia" from enderecos where ass_id=:ass_id');

        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll()[0]['sequencia'];
    }

    public function gerentiEnderecoInfos($ass_id, $sequencia)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select first 1 * from ENDERECOS where ASS_ID = :ass_id and END_SEQUENCIA = :sequencia');

        $run->execute([
            'ass_id' => $ass_id,
            'sequencia' => $sequencia
        ]);
        return $run->fetchAll();
    }

    public function gerentiContatos($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select * from CONTATOXPESS where CXP_ASS_ID =  :ass_id');

        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll();
    }

    public function gerentiInserirContato($ass_id, $conteudo, $tipo, int $id = 0)
    {
        $this->connect();

        $id !== 0 ? $nameProc = 'SP_CONTATOXPESS_U' : $nameProc = 'SP_CONTATOXPESS_I';

        // Adicionando 3 parâmetros finais (com valor 0) simbolizando respectivamente: SMS, WHATSAPP, TELEGRAM.
        // TODO: permitir informar esses valores na interface gráfica (uso apenas para celulares).
        $run = $this->gerentiConnection->prepare("execute procedure ".$nameProc."(:ass_id, :id, 0, 210, CAST('NOW' AS DATE), :conteudo, :tipo, '', 1, 0, 0, 0)");
        $run->execute([
            'ass_id' => $ass_id,
            'id' => $id,
            'conteudo' => $conteudo,
            'tipo' => $tipo
        ]);
    }

    public function gerentiDeletarContato($ass_id, $request)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare("execute procedure SP_CONTATO_STATUS(:ass_id, :id, :status, 210)");
        $run->execute([
            'ass_id' => $ass_id,
            'id' => $request->id,
            'status' => $request->status
        ]);
    }

    public function gerentiBolestosLista($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare("select descricao, vencimento, valor, multa, 
            juros, correcao, residuo, total, boleto, vencimentoboleto, link, situacao
            from PROCPORTALSITUACAOFINANCEIRA(:ass_id) order by vencimento desc");

        $run->execute([
            'ass_id' => $ass_id
        ]);

        return $run->fetchAll(PDO::FETCH_ASSOC);
    }

    public function gerentiStatus($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select SITUACAO from PROCSITUACAOATUAL(:ass_id)');

        $run->execute([
            'ass_id' => $ass_id
        ]);

        return utf8_encode($run->fetchAll()[0]['SITUACAO']);
    }

    public function gerentiAnuidadeVigente($cpfCnpj)
    {
        $this->connect();

        $ano = (int) date('Y');

        $run = $this->gerentiConnection->prepare('select BOL_ID, NOSSONUMERO from PROCPORTALBOLETOANO(:cpfCnpj, :ano)');

        $run->execute([
            'cpfCnpj' => $cpfCnpj,
            'ano' => $ano
        ]);

        return $run->fetchAll(PDO::FETCH_ASSOC);
    }

    public function gerentiBusca($registro, $nome, $cpfCnpj)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare("select ass_id, ass_ativo, ass_entidade, ass_tp_assoc,
            ass_registro, ass_nome, ass_cpf_cgc, ass_dt_admissao, ass_dt_reg_social, ass_tp_pessoa,
            ass_usu_cadastro, ass_tp_regiao, ass_dt_update, usu_codigo, sys_last_update, tipo,
            cancelado, excore
            from procbuscaassociado(:registro, :nome, :cpfCnpj, :email, :telefone)");
        $run->execute([
            'registro' => $registro,
            'nome' => $nome,
            'cpfCnpj' => $cpfCnpj,
            'email' => '',
            'telefone' => ''
        ]);

        return $run->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta o GERENTI para verificar se Representante Comercial está ativo ou não usando CPF/CNPJ (SITUACAO).
     * Retorna outras informações relacionadas ao Representante Comercial.
     */
    public function gerentiAtivo($cpfCnpj)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare("select SITUACAO, REGISTRONUM, ASS_ID, NOME, EMAILS from PROCSTATUSREGISTRO(:cpfCnpj)");
        $run->execute([
            'cpfCnpj' => $cpfCnpj
        ]);

        return $run->fetchAll();
    }

    /** 
     * Verifica no GERENTI se é possível emitir uma certidão para o Representante Comercial de acordo com o ASS_ID e o tipo de certidão. Em caso negativo, uma flag com o valor "0" será retornada.
     * Em caso positivo, uma flag com o valor "1" será retornada juntamente com informações da certidão (número, código, data e hora da emissão) e do Representante Comercial.
     * 
     * Códigos do tipo:
     *  11 - Regularidade
     *  12 - Parcelamento?
     */
    public function gerentiEmitirCertidao($ass_id, $tipo) 
    {
        $this->connect();

        $run = $this->gerentiConnection->getPDO()->beginTransaction();

        $run = $this->gerentiConnection->prepare('select EMISSAO, NUMERO, DATAEMISSAO, HORA, CODVALIDACAO, DATAVALIDADE, NOME, REGISTRO, CPFCNPJ, DATAREGISTRO, TIPOEMPRESA, RESPTECNICOS, REGISTROSRTS, ENDERECOCOMPLETO
        from PROCNOVACERTIDAO(:ASS_ID, 210, null, :TIPO)');
        
        $run->execute([
            'ASS_ID' => $ass_id,
            'TIPO' => $tipo
        ]);

        $resultado = $run->fetchAll(PDO::FETCH_ASSOC);

        $this->gerentiConnection->getPDO()->commit();

        return utf8_converter($resultado)[0];
    }

    /** 
     * Recupera no GERENTI as certidões que foram emitidas para o Representante Comercial de acordo com o ASS_ID e o tipo.
     * 
     * Códigos do tipo:
     *  11 - Regularidade
     *  12 - Parcelamento?
     */
    public function gerentiListarCertidoes($ass_id, $tipo) 
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select SITUACAO, NUMERO, DATAEMISSAO, HORAEMISSAO, CODVALIDACAO, VALIDADE from PROCLISTACERTIDOES(:ASS_ID, :TIPO)');
        
        $run->execute([
            'ASS_ID' => $ass_id,
            'TIPO' => $tipo
        ]);

        return utf8_converter($run->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Verifica no GERENTI a autenticidade e validade de uma certidão. Retorna uma flag que indica as seguintes situações: "0" (inexistente), "1" (válida), "2" (suspensa), "3" (vencida).
     * Caso a flag indique que certidão está válida, informações sobre o Representante Comercial serão retornadas (Nome, Registro, CPF_CNPJ, data de validade da certidão). 
     */
    public function gerentiAutenticaCertidao($numero, $codigo, $hora, $data) 
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select SITUACAO, DATAVALIDADE from PROCCONSULTACODVALIDACAO(:CODVALIDACAO, :NUMERO, :DATAEMISSAO, :HORAEMISSAO)');
        
        $run->execute([
            'CODVALIDACAO' => $codigo,
            'NUMERO' => $numero,
            'DATAEMISSAO' => $data,
            'HORAEMISSAO' => $hora
        ]);

        $resultado = $run->fetchAll(PDO::FETCH_ASSOC);

        return utf8_converter($resultado)[0];
    }
}