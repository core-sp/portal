<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Connections\FirebirdConnection;
use App\Rules\CpfCnpj;

class ConsultaSituacaoController extends Controller
{
    private $connection;

    protected function connect()
    {
        // Conexão e criação do objeto de conexão na variável $connection
        $this->connection = new FirebirdConnection();
    }

    public function consultaView()
    {
        return view('site.consulta');
    }

    protected function validateRequest()
    {
        // Validação dos campos do formulário
        return request()->validate([
            'cpfCnpj' => ['required', new CpfCnpj],
        ], [
            'cpfCnpj.required' => 'Por favor, informe o CPF!',
        ]);
    }

    protected function validateAndConnect()
    {
        // Validação e conexão
        $this->validateRequest();

        $this->connect();
    }

    public function consulta()
    {
        $this->validateAndConnect();

        // Regex para CPF
        $cpf = preg_replace('/[^0-9]+/', '', request('cpfCnpj'));

        $run = $this->connection->prepare("select SITUACAO, REGISTRONUM, ASS_ID, NOME, EMAILS from PROCSTATUSREGISTRO(:cpf)");
        $run->execute([
            'cpf' => $cpf
        ]);

        $resultado = $run->fetchAll();

        return view('site.consulta', compact('resultado'));
    }
}
