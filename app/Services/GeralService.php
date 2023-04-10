<?php

namespace App\Services;

use App\Contracts\GeralServiceInterface;
use App\HomeImagem;
use App\Events\CrudEvent;

class GeralService implements GeralServiceInterface {

    public function carrossel($array = null)
    {
        $resultado = HomeImagem::select('idimagem','funcao','ordem','url','url_mobile','link','target')
            ->orderBy('ordem','ASC')
            ->get();
        $variaveis = [
            'singular' => 'banner',
            'singulariza' => 'o banner',
            'form' => 'bannerprincipal'
        ];

        if(isset($array))
        {
            $chunk = HomeImagem::validacao($array);
            for($cont = 1; $cont <= 7; $cont++)
            {
                $indice = $cont - 1;
                $banner = $resultado->where('ordem', $cont)
                ->where('funcao','bannerprincipal')->first();
                $banner->update([
                    'url' => $chunk[$indice][0],
                    'url_mobile' => $chunk[$indice][1],
                    'link' => $chunk[$indice][2],
                    'target' => $chunk[$indice][3]
                ]);
                event(new CrudEvent('banner principal', 'editou', $banner->idimagem));
            }
        }
        
        return [
            'resultado' => $resultado,
            'variaveis' => (object) $variaveis,
        ];
    }
}