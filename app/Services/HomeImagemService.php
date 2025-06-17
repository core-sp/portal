<?php

namespace App\Services;

use App\HomeImagem;
use App\Contracts\HomeImagemServiceInterface;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\ImagensLazyLoad;

class HomeImagemService implements HomeImagemServiceInterface {

    use ImagensLazyLoad;

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

        if(!isset($array) && ($resultado->count() < HomeImagem::TOTAL))
            for($cont = $resultado->count(); $cont < HomeImagem::TOTAL; ++$cont)
                $resultado->push(HomeImagem::create(['ordem' => $cont + 1])->fresh());
                

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

                $this->gerarPreImagemLFM($chunk[$indice][0]);
                $this->gerarPreImagemLFM($chunk[$indice][1]);

                event(new CrudEvent('banner principal', 'editou', $banner->idimagem));
            }
        }
        
        return [
            'resultado' => $resultado,
            'variaveis' => (object) $variaveis,
            'total' => HomeImagem::TOTAL,
        ];
    }

    public function itensHome($dados = null)
    {
        $variaveis = [
            'singular' => 'itens da home',
            'singulariza' => 'itens da home',
            'form' => 'itens-home'
        ];

        $resultado = HomeImagem::itensHome();
        
        if(isset($dados) && is_array($dados))
        {
            foreach($dados as $key => $dado)
            {
                $item = HomeImagem::getItemPorResultado($resultado, $key);
                if(isset($item))
                {
                    $dado = HomeImagem::getValor($key, $dado);
                    if($dado == $item->url)
                        continue;
                    $item->update(['url' => $dado, 'url_mobile' => $dado]);
                    event(new CrudEvent('item da home: '.$item->funcao, 'editou', $item->idimagem));
                }
            }
            return;
        }
            
        $itens = array();
        foreach(HomeImagem::padrao() as $key => $campo)
        {
            $chave = str_replace('_default', '', $key);
            $itens[$chave] = HomeImagem::getItemPorResultado($resultado, $chave);
        }

        return array_merge([
            'variaveis' => (object) $variaveis,
            'padroes' => HomeImagem::padrao(),
        ], $itens);
    }

    public function getItens()
    {
        $resultado = !Schema::hasTable('home_imagens') ? collect() : HomeImagem::itensHome();

        $rodape = HomeImagem::getItemPorResultado($resultado, 'footer');
        $cards_1 = HomeImagem::getItemPorResultado($resultado, 'cards_1');
        $cards_2 = HomeImagem::getItemPorResultado($resultado, 'cards_2');
        $cards_laterais_1 = HomeImagem::getItemPorResultado($resultado, 'cards_laterais_1');
        $cards_laterais_2 = HomeImagem::getItemPorResultado($resultado, 'cards_laterais_2');
        $calendario = HomeImagem::getItemPorResultado($resultado, 'calendario');
        $header_logo = HomeImagem::getItemPorResultado($resultado, 'header_logo');
        $header_fundo = HomeImagem::getItemPorResultado($resultado, 'header_fundo');
        $neve = HomeImagem::getItemPorResultado($resultado, 'neve');
        $popup_video = HomeImagem::getItemPorResultado($resultado, 'popup_video');

        return [
            'rodape' => isset($rodape) ? $rodape->url : HomeImagem::padrao()['footer_default'],
            'cards_1' => isset($cards_1) ? $cards_1->url : HomeImagem::padrao()['cards_1_default'],
            'cards_2' => isset($cards_2) ? $cards_2->url : HomeImagem::padrao()['cards_2_default'],
            'cards_laterais_1' => isset($cards_laterais_1) ? $cards_laterais_1->url : HomeImagem::padrao()['cards_laterais_1_default'],
            'cards_laterais_2' => isset($cards_laterais_2) ? $cards_laterais_2->url : HomeImagem::padrao()['cards_laterais_2_default'],
            'calendario' => isset($calendario) ? $calendario->url : HomeImagem::padrao()['calendario_default'],
            'header_logo' => isset($header_logo) ? $header_logo->url : HomeImagem::padrao()['header_logo_default'],
            'header_fundo' => isset($header_fundo) ? $header_fundo->getHeaderFundo() : 'background-image: url(/'.HomeImagem::padrao()['header_fundo_default'].')',
            'neve' => isset($neve) ? $neve->getNeve() : null,
            'popup_video' => isset($popup_video) ? $popup_video->url : null,
        ];
    }

    public function itensHomeStorage($folder = null, $file = null)
    {
        if(isset($file) && Storage::disk('itens_home')->exists($file))
            return Storage::disk('itens_home')->delete($file) ? event(new CrudEvent('arquivo armazenado como item da home: '.$file, 'excluiu', '---')) : 'Não foi removido.';
        if(isset($file) && !Storage::disk('itens_home')->exists($file))
            throw new \Exception('Arquivo não existe', 404);
        
        $files = isset($folder) ? array() : Storage::disk('itens_home')->allFiles();
        $files_folder = isset($folder) ? scandir(public_path() . '/' . $folder) : array();
        foreach($files_folder as $key => $img){
            if(preg_match('/(\.jpg|\.png|\.jpeg)+$/i', $img) === 0)
                unset($files_folder[$key]);
        }

        return [
            'path' => isset($folder) ? array_values($files_folder) : $files,
            'caminho' => isset($folder) ? $folder . '/' : HomeImagem::caminhoStorage(),
            'folder' => isset($folder) ? $folder : 'itens-home',
        ];
    }

    public function uploadFileStorage($file)
    {
        if($file instanceof UploadedFile)
        {
            $url = $file->getClientOriginalName();
            $url = strtr(utf8_decode($url), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

            // renomear arquivo que já existe para não sobrescrever...
            if(Storage::disk('itens_home')->exists($url))
            {
                $ext = '.' . $file->getClientOriginalExtension();
                $url = Str::replaceLast($ext, '', $url) . '_' . Carbon::now()->timestamp . $ext;
            }
            
            $file->storeAs('/', $url, 'itens_home');
            event(new CrudEvent('arquivo de imagem em itens da home com upload do file: ' . HomeImagem::pathCompleto() . $url, 'está armazenando', '---'));
            return ['novo_arquivo' => $url];
        }

        throw new \Exception('Arquivo para upload não existe', 404);
    }

    public function downloadFileStorage($folder, $arquivo)
    {
        switch ($folder) {
            case 'img':
                $temp = HomeImagem::pathCompleto() . $folder . '/' . $arquivo;
                if(\File::exists($temp))
                    return $temp;
                break;
            
            default:
                if(Storage::disk('itens_home')->exists($arquivo))
                    return Storage::disk('itens_home')->path($arquivo);
                break;
        }

        throw new \Exception('Arquivo "'.$arquivo.'" para download não existe no folder "'.$folder.'" !', 404);
    }
}