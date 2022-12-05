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
            $this->connect_opcional();
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

    protected function config_opcional()
    {
        // Objeto com as informações de conexão
        return (object) [
            'host' => env('GERENTI_HOST_OPCIONAL'),
            'dbname' => env('GERENTI_DATABASE'),
            'username' => env('GERENTI_USERNAME'),
            'password' => env('GERENTI_PASSWORD')
        ];
    }

    protected function connect_opcional()
    {   
        try {
            $message = 'Os servidores estão passando por manutenção. Por favor, tente dentro de alguns minutos.';
			if(strlen(env('GERENTI_HOST_OPCIONAL')) == 0)
                throw new \Exception('Não há ip opcional para realizar conexão com Gerenti', 500);
            $this->connection = null;
            $this->connection = new PDO('firebird:dbname='.$this->config_opcional()->host.':'.$this->config_opcional()->dbname.';charset=UTF8',
                $this->config_opcional()->username,
                $this->config_opcional()->password);
        } catch (PDOException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, $message);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, $message);
        }
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