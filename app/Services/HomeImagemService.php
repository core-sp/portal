<?php

namespace App\Services;

use App\HomeImagem;
use App\Contracts\HomeImagemServiceInterface;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Storage;

class HomeImagemService implements HomeImagemServiceInterface {

    public function carrossel($array = null)
    {
        $variaveis = [
            'singular' => 'banner',
            'singulariza' => 'o banner',
            'form' => 'bannerprincipal'
        ];

        $resultado = HomeImagem::where('funcao', 'bannerprincipal')
            ->orderBy('ordem','ASC')
            ->get();

        if(isset($array) && is_array($array))
        {
            $chunk = HomeImagem::validacao($array);
            for($cont = 1; $cont <= HomeImagem::TOTAL; $cont++)
            {
                $indice = $cont - 1;
                $banner = $resultado->where('ordem', $cont)->first();
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

    public function itensHome($dados = null)
    {
        $variaveis = [
            'singular' => 'itens da home',
            'singulariza' => 'itens da home',
            'form' => 'itens-home'
        ];

        $resultado = HomeImagem::where('funcao', '!=', 'bannerprincipal')->get();
        
        if(isset($dados) && is_array($dados))
        {
            foreach($dados as $key => $dado)
            {
                $item = HomeImagem::getItemPorResultado($resultado, $key);
                if(isset($item))
                {
                    if($dado instanceof \Illuminate\Http\UploadedFile)
                        event(new CrudEvent('arquivo de imagem em itens da home no campo '.$key.' com upload do file: ' . $dado->getClientOriginalName(), 'está armazenando', $item->idimagem));
                    $dado = HomeImagem::getValor($key, $dado);
                    if($dado == $item->url)
                        continue;
                    $item->update(['url' => $dado, 'url_mobile' => $dado]);
                    event(new CrudEvent('item da home: '.$item->funcao, 'editou', $item->idimagem));
                }
            }
            return;
        }
            
        return [
            'cards_1' => HomeImagem::getItemPorResultado($resultado, 'cards_1'),
            'cards_2' => HomeImagem::getItemPorResultado($resultado, 'cards_2'),
            'footer' => HomeImagem::getItemPorResultado($resultado, 'footer'),
            'calendario' => HomeImagem::getItemPorResultado($resultado, 'calendario'),
            'header_logo' => HomeImagem::getItemPorResultado($resultado, 'header_logo'),
            'header_fundo' => HomeImagem::getItemPorResultado($resultado, 'header_fundo'),
            'calendario_default' => HomeImagem::padrao()['calendario_default'],
            'header_logo_default' => HomeImagem::padrao()['header_logo_default'],
            'header_fundo_default' => HomeImagem::padrao()['header_fundo_default'],
            'variaveis' => (object) $variaveis,
            'padroes' => HomeImagem::padrao(),
        ];
    }

    public function getItens()
    {
        $resultado = HomeImagem::where('funcao', '!=', 'bannerprincipal')->get();

        $rodape = HomeImagem::getItemPorResultado($resultado, 'footer');
        $cards_1 = HomeImagem::getItemPorResultado($resultado, 'cards_1');
        $cards_2 = HomeImagem::getItemPorResultado($resultado, 'cards_2');
        $calendario = HomeImagem::getItemPorResultado($resultado, 'calendario');
        $header_logo = HomeImagem::getItemPorResultado($resultado, 'header_logo');
        $header_fundo = HomeImagem::getItemPorResultado($resultado, 'header_fundo');

        return [
            'rodape' => isset($rodape) ? $rodape->url : null,
            'cards_1' => isset($cards_1) ? $cards_1->url : null,
            'cards_2' => isset($cards_2) ? $cards_2->url : null,
            'calendario' => isset($calendario) ? $calendario->url : null,
            'header_logo' => isset($header_logo) ? $header_logo->url : null,
            'header_fundo' => isset($header_fundo) ? $header_fundo->getHeaderFundo() : null,
        ];
    }

    public function itensHomeStorage($file = null)
    {
        if(isset($file) && Storage::disk('itens_home')->exists($file))
            return Storage::disk('itens_home')->delete($file) ? event(new CrudEvent('arquivo armazenado como item da home: '.$file, 'excluiu', '---')) : 'Não foi removido.';
        if(isset($file) && !Storage::disk('itens_home')->exists($file))
            throw new \Exception('Arquivo não existe', 404);
        
        $files = Storage::disk('itens_home')->allFiles();

        return [
            'path' => $files,
            'caminho' => HomeImagem::caminhoStorage(),
        ];
    }
}