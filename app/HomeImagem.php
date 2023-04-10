<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HomeImagem extends Model
{
    protected $table = 'home_imagens';
    protected $primaryKey = 'idimagem';
    protected $guarded = [];

    public static function validacao($array)
    {
        foreach($array as $key => $value)
        {
            $teste = preg_match('/^(img|img-mobile|link|target)-([0-9]){1}$/', $key);
            if(($teste === false) || ($teste === 0))
                throw new \Exception('Campo (' . $key . ') não é válido ao atualizar o carrossel.', 400);
        }

        return array_chunk($array, 4);
    }
}
