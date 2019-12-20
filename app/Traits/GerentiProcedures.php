<?php

namespace App\Traits;

use App\Connections\FirebirdConnection;
use Illuminate\Support\Facades\Input;
use PDO;

trait GerentiProcedures
{
    private $gerentiConnection;

    protected function connect()
    {
        $this->gerentiConnection = new FirebirdConnection();
    }

    protected function checaAtivo($registro, $cpfCnpj, $email = null)
    {
        $this->connect();

        $cpfCnpj = preg_replace('/[^0-9]+/', '', $cpfCnpj);

        $run = $this->gerentiConnection->prepare("select SITUACAO, REGISTRONUM, ASS_ID, NOME, EMAILS from PROCLOGINPORTAL(:registro, :cpfCnpj)");

        $run->execute([
            'registro' => $registro,
            'cpfCnpj' => $cpfCnpj
        ]);
        $resultado = $run->fetchAll();

        $verificaEmail = $this->gerentiEmails($resultado[0]['EMAILS']);

        if($resultado[0]['SITUACAO'] !== 'Ativo')
            return ['Error' => 'O cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique as informações inseridas.'];
        elseif(!in_array($email, $verificaEmail))
            return ['Error' => 'O email informado não corresponde ao cadastro informado. Por favor, insira o email correto.'];
        else
            return $resultado[0];
    }

    protected function gerentiEmails($emails)
    {
        $emailsArray = explode(';', $emails);

        $array = [];

        foreach($emailsArray as $email)
        {
            array_push($array, $email);
        }

        return $array;
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

        $cep = preg_replace( '/[^0-9]/', '', $infos['cep']);

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

    protected function gerentiBuscarSequenciaEndereco($ass_id)
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

        $run = $this->gerentiConnection->prepare("execute procedure ".$nameProc."(:ass_id, :id, 0, 210, CAST('NOW' AS DATE), :conteudo, :tipo, '', 1)");
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
}