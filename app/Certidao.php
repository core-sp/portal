<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certidao extends Model
{
    protected $table = "certidoes";
    protected $guarded = [];
    public $timestamps = false;

    // Formata o código da Certidão para XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
    public function codigoFormatado() 
    {
        if(isset($this->codigo)) {
            $codigoFormatado = substr($this->codigo, 0, 8);

            for ($i = 8; $i < strlen($this->codigo); $i = $i+8) {
                $codigoFormatado .= "-";

                $codigoFormatado .= substr($this->codigo, $i, 8);
            }
        }

        return $codigoFormatado;
    }
}
