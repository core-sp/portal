<?php

namespace App\Traits;

use App\Connections\FirebirdConnection;

trait GerentiProcedures
{
    private $gerentiConnection;

    protected function connect()
    {
        $this->gerentiConnection = new FirebirdConnection();
    }

    protected function checaAtivo($registro, $cpfCnpj)
    {
        $this->connect();

        $cpfCnpj = preg_replace('/[^0-9]+/', '', $cpfCnpj);

        $run = $this->gerentiConnection->prepare("select SITUACAO, REGISTRONUM, ASS_ID, NOME, EMAILS from PROCLOGINPORTAL('" . $registro . "', '" . $cpfCnpj . "')");

        $run->execute();
        $resultado = $run->fetchAll();

        if($resultado[0]['SITUACAO'] !== 'Ativo') 
            return false;
        else
            return $resultado[0];
    }

    public function gerentiDadosGeraisPF($id)
    {
        $this->connect();

        $query = 'select TP_dados.tpd_descricao, dados.dad_valor FROM dados inner join TP_DADOS on dados.tpd_id = TP_DADOS.tpd_id WHERE ASS_ID = '.$id.'';

        $run = $this->gerentiConnection->prepare($query);
        
        $run->execute();
        return $run->fetchAll();
    }

    public function gerentiDadosGeraisPJ($id)
    {
        $this->connect();
        
        $query = 'select first 1 ASS_DT_REG_SOCIAL, ASS_DT_ADMISSAO, REGIONAL, NIRE from ASSOCIADOS where ASS_ID = '.$id.'';

        $run = $this->gerentiConnection->prepare($query);
        
        $run->execute();
        return $run->fetchAll();
    }

    public function gerentiEnderecos($id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select * from SP_ENDERECOS_SEL ('.$id.')');

        $run->execute();
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
            ".$ass_id.", ".$sequencia.", '', '".$request->logradouro."', '".$request->estado."',
            '".$request->municipio."', '".$request->numero."', '".$request->complemento."', '".$request->bairro."',
            '".$cep."', '".$corresp."', '', 'F', 210, CAST('NOW' AS DATE), '".$corresp."', 'F'
        )");

        $run->execute();
    }

    protected function gerentiBuscarSequenciaEndereco($ass_id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select Max(end_sequencia) + 1 "sequencia" from enderecos where ass_id='.$ass_id.'');

        $run->execute();
        return $run->fetchAll()[0]['sequencia'];
    }

    public function gerentiEnderecoInfos($ass_id, $sequencia)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select first 1 * from ENDERECOS where ASS_ID = '.$ass_id.' and END_SEQUENCIA = '.$sequencia.'');

        $run->execute();
        return $run->fetchAll();
    }

    public function gerentiContatos($id)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare('select * from CONTATOXPESS where CXP_ASS_ID =  '.$id.'');

        $run->execute();
        return $run->fetchAll();
    }

    public function gerentiInserirContato($ass_id, $conteudo, $tipo, int $id = 0)
    {
        $this->connect();

        $id !== 0 ? $nameProc = 'SP_CONTATOXPESS_U' : $nameProc = 'SP_CONTATOXPESS_I';

        $run = $this->gerentiConnection->prepare("execute procedure ".$nameProc."(".$ass_id.", ".$id.", 0, 210, CAST('NOW' AS DATE), '".$conteudo."', ".$tipo.", '', 1)");
        $run->execute();
    }

    public function gerentiDeletarContato($ass_id, $id, $status)
    {
        $this->connect();

        $run = $this->gerentiConnection->prepare("execute procedure SP_CONTATO_STATUS(".$ass_id.", ".$id.", ".$status.")");
        $run->execute();
    }
}