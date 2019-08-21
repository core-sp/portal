<?php

namespace App\Connections;

use PDO;
use PDOException;

class FirebirdConnection
{
    private $connection;

    public function __construct()
    {
        try {
            $this->connection = new PDO('firebird:dbname='.$this->config()->host.':'.$this->config()->dbname.';charset=UTF8',
                                        $this->config()->username,
                                        $this->config()->password,
                                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            die(json_encode(['outcome' => true]));
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $this->connection;
    }

    protected function config()
    {
        return (object) [
            'host' => env('GERENTI_HOST'),
            'dbname' => env('GERENTI_DATABASE'),
            'username' => env('GERENTI_USERNAME'),
            'password' => env('GERENTI_PASSWORD')
        ];
    }

    public function prepare($query)
    {
        return $this->connection->prepare($query);
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}