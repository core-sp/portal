<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Cnpj implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $cnpj)
    {
        $cnpj = apenasNumerosLetras((string) $cnpj);
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;

        for($num = 0; $num <= 9; $num++){
            if(str_repeat($num, 14) == $cnpj)
                return false;
        }
            
        return $this->cnpjAlfaNumerico($cnpj);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'CNPJ inválido';
    }

    private function cnpjNumerico($cnpj)
    {
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    private function cnpjAlfaNumerico($cnpj)
    {
        $cnpj_sem_dv = substr($cnpj, 0, 12);
        $primeiro_dv = (string) $this->calcularDVAlfaNumerico($cnpj_sem_dv);
        $segundo_dv = (string) $this->calcularDVAlfaNumerico($cnpj_sem_dv . $primeiro_dv);

        return $cnpj == ($cnpj_sem_dv . $primeiro_dv . $segundo_dv);
    }

    private function calcularDVAlfaNumerico($cnpj_base)
    {
        $pesos = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $lenght_cnpj = strlen($cnpj_base);
        $soma = 0;

        for($indice = $lenght_cnpj - 1; $indice >= 0; $indice--)
        {
            $valor_digito = ord($cnpj_base[$indice]) - 48;
            $soma += $valor_digito * $pesos[(count($pesos) - $lenght_cnpj) + $indice];
        }

        $resto = $soma % 11;
        return $resto < 2 ? 0 : 11 - $resto;
    }
}
