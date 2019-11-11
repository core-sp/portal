<?php

namespace App\Traits;

use App\Connections\FirebirdConnection;
use Illuminate\Support\Facades\Input;

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

        if($resultado[0]['SITUACAO'] !== 'Ativo' || !in_array($email, $verificaEmail))
            return false;
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

        $query = 'select TP_dados.tpd_descricao, dados.dad_valor FROM dados inner join TP_DADOS on dados.tpd_id = TP_DADOS.tpd_id WHERE ASS_ID = :ass_id';

        $run = $this->gerentiConnection->prepare($query);
        
        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll();
    }

    public function gerentiDadosGeraisPJ($ass_id)
    {
        $this->connect();
        
        $query = 'select first 1 ASS_DT_REG_SOCIAL, ASS_DT_ADMISSAO, REGIONAL, NIRE from ASSOCIADOS where ASS_ID = :ass_id';

        $run = $this->gerentiConnection->prepare($query);
        
        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll();
    }

    public function gerentiEnderecos($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select * from SP_ENDERECOS_SEL (:ass_id)');

        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll();
    }

    public function gerentiInserirEndereco($ass_id, $request)
    {
        $request->corresp === 'on' ? $corresp = 'T' : $corresp = 'F';

        if(isset($request->sequencia)) {
            $sequencia = intval($request->sequencia);
        } else {
            $sequencia = $this->gerentiBuscarSequenciaEndereco($ass_id);
        }

        $cep = preg_replace( '/[^0-9]/', '', $request->cep);

        $this->connect();

        $run = $this->gerentiConnection->prepare("execute procedure SP_ENDERECOS_UI(
            ".$ass_id.", ".$sequencia.", '', :logradouro, :estado, :municipio, :numero, :complemento,
            :bairro, :cep, '".$corresp."', '', 'F', 210, CAST('NOW' AS DATE), '".$corresp."', 'F'
        )");

        $run->execute([
            'logradouro' => $request->logradouro,
            'estado' => $request->estado,
            'municipio' => $request->municipio,
            'numero' => $request->numero,
            'complemento' => $request->complemento,
            'bairro' => $request->bairro,
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

    public function gerentiDeletarContato($ass_id, $id, $status)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare("execute procedure SP_CONTATO_STATUS(:ass_id, :id, :status)");
        $run->execute([
            'ass_id' => $ass_id,
            'id' => $id,
            'status' => $status
        ]);
    }

    public function gerentiBolestosLista($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare("select ASS_ID, REGISTRO, NOME, BOL_ID, NOSSONUMERO, DATADOCUMENTO, 
            DATAVENCIMENTO, CONTA_IDBOL, USERIDBOL, TPGERBOL_ID, LOGINUSER, ORIGEMBOL, VALORBOLETO, CONTADESCR, ITENSBOL, 
            ASS_CPF_CGC, TP_ASSOC, BOL_SEUNUMERO, BOL_NUMERODOCUMENTO, ENDERECO, END_CONPLEMENTO, END_BAIRRO, END_CEP, END_MUNICIPIO, 
            END_ESTADO, END_CORRESP,  END_CORR_DEVOLV from PROCBOLETOSREGIOSTRADOSEMABERTO (:ass_id, CAST('01.01.1970' AS DATE), CAST('NOW' AS DATE))");

        $run->execute([
            'ass_id' => $ass_id
        ]);
        return $run->fetchAll();
    }
}