<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HomeImagem extends Model
{
    protected $table = 'home_imagens';
    protected $primaryKey = 'idimagem';
    protected $guarded = [];

    const TOTAL = 7;

    public static function validacao($array)
    {
        $total = count($array);
        $totalPermitido = 4 * self::TOTAL;

        // sendo 4 a quantidade de campos diferentes: img|img-mobile|link|target
        if($total != $totalPermitido)
            throw new \Exception('Possui total de campos (' .$total. ') diferente do permitido (' .$totalPermitido. '), então não é válido ao atualizar o carrossel.', 400);

        foreach($array as $key => $value)
        {
            $teste = preg_match('/^(img|img-mobile|link|target)-([1-' .self::TOTAL. ']){1}$/', $key);
            if(($teste === false) || ($teste === 0))
                throw new \Exception('Campo (' .$key. ') não é válido ao atualizar o carrossel devido não ser compatível com: img-1 ou img-mobile-1 ou link-1 ou target-1.', 400);
            if((strpos($key, 'target') !== false) && (!in_array($value, ['_blank', '_self'])))
                throw new \Exception('Campo (' .$key. ') não é válido ao atualizar o carrossel devido seu valor (' .$value. ') não ser aceito: _blank, _self.', 400);
        }

        return array_chunk($array, 4);
    }
}
