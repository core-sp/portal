<?php

namespace App\Services;

use App\Contracts\PreRegistroServiceInterface;

class PreRegistroService implements PreRegistroServiceInterface {

    public function __construct()
    {
        
    }

    public function verificacao()
    {
        $externo = auth()->guard('user_externo')->user();
        // Verificar via Gerenti se já existe o cpf ou cnpj como representante
        // Caso sim, dar uma mensagem mostrando o registro dele e a atual situação (em dia, bloqueado etc)
        // Caso não, permitr a solicitação de registro

        return 'dados caso possua registro ou mensagem que pode iniciar a solicitação de registro';
    }

    public function getPreRegistro()
    {
        $externo = auth()->guard('user_externo')->user();
        // Verificar com o metodo verificacao() para impedir de acessar o formulario
        // Caso não, verificar se já tem o pre registro salvo no banco

        $codigos = array();
        $models = [
            'App\UserExterno' => null,
            'App\PreRegistro' => null,
            'App\PreRegistroCpf' => null,
            'App\PreRegistroCnpj' => null,
            'App\Contabil' => null,
            'App\ResponsavelTecnico' => null,
            'App\Anexo' => null
        ];
        foreach($models as $key => $model)
            $codigos[$key] = $key::codigosPreRegistro();

        return [
            'resultado' => $externo,
            'codigos' => $codigos
        ];
    }
}