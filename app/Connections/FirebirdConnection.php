<?php

namespace App\Connections;

use PDO;
use PDOException;

class FirebirdConnection
{
    private $connection;

    public function __construct()
    {
        // Construtor da conexão ao banco
        try {
            $this->connection = new PDO('firebird:dbname='.$this->config()->host.':'.$this->config()->dbname.';charset=UTF8',
                $this->config()->username,
                $this->config()->password);
        } catch (PDOException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Os servidores estão passando por manutenção. Por favor, tente dentro de alguns minutos.'/*'Estamos enfrentando problemas técnicos no momento. Por favor, tente dentro de alguns minutos.'*/);
        }

        return $this->connection;
    }

    protected function config()
    {
        // Objeto com as informações de conexão
        return (object) [
            'host' => env('GERENTI_HOST'),
            'dbname' => env('GERENTI_DATABASE'),
            'username' => env('GERENTI_USERNAME'),
            'password' => env('GERENTI_PASSWORD')
        ];
    }

    public function prepare($query)
    {
        // Padrão de prepare para rodar comandos no Banco
        return $this->connection->prepare($query);
    }

    public function getPDO()
    {
        return $this->connection;
    }

    public function __destruct()
    {
        // Destrutor da conexão
        $this->connection = null;
    }
}