<?php

namespace App\Traits;

trait Gerenti {

    public function getCodigoPF()
    {
        return 2;
    }

    public function getCodigoPJ()
    {
        return 1;
    }

    public function getCodigoRT()
    {
        return 5;
    }

    public function getTipoPessoaByCodigo($codigo)
    {
        switch ($codigo) {
            case '1':
                return "PJ";
            break;

            case '2':
                return "PF";
            break;

            case '5':
                return "RT";
            break;

            default:
                return 'Indefinida';
            break;
        }
    }
        
}