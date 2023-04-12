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
            for($cont = 1; $cont <= HomeImagem::TOTAL; $cont++)
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

    public function consultaSituacao($dados_gerenti)
    {
        if(isset($dados_gerenti) && count($dados_gerenti) === 1)
        {
            $dados_gerenti = utf8_converter($dados_gerenti[0]);
            $situacao = $dados_gerenti['SITUACAO'];
            $badge = '';
    
            switch ($situacao) {
                case 'Ativo':
                    $badge = '<span class="badge badge-success">'.$situacao.'</span>';
                break;
                case 'Cancelado':
                    $badge = '<span class="badge badge-danger">'.$situacao.'</span>';
                break;
                default:
                    $badge = '<span class="badge badge-secondary">'.$situacao.'</span>';
                break;
            }
    
            if($situacao === 'Não encontrado')
                return array();
    
            return [
                'nome' => $dados_gerenti['NOME'],
                'registro' => substr_replace($dados_gerenti['REGISTRONUM'], '/', -4, 0),
                'badge_situacao' => $badge,
            ];
        }

        return null;
    }

    public function anuidadeVigente($dados_gerenti)
    {
        if(isset($dados_gerenti[0]['NOSSONUMERO']))
            return [
                'nossonumero' => $dados_gerenti[0]['NOSSONUMERO']
            ];
        return [
            'notFound' => true
        ];
    }
}