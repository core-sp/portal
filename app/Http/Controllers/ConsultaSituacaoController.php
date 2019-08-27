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
        $this->connection = new FirebirdConnection();
    }

    public function consultaView()
    {
        return view('site.consulta');
    }

    protected function validateRequest()
    {
        return request()->validate([
            'cpfCnpj' => ['required', new CpfCnpj],
        ], [
            'cpfCnpj.required' => 'Por favor, informe o CPF!',
        ]);
    }

    protected function validateAndConnect()
    {
        $this->validateRequest();

        $this->connect();
    }

    public function consulta()
    {
        $this->validateAndConnect();

        $cpf = preg_replace('/[^0-9]+/', '', request('cpfCnpj'));

        $run = $this->connection->prepare("select SITUACAO, REGISTRONUM, ASS_ID, NOME, EMAILS from PROCSTATUSREGISTRO('" . $cpf . "')");
        $run->execute();

        $resultado = $run->fetchAll();

        return view('site.consulta', compact('resultado'));
    }
}
