<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ImagensLazyLoad
{
    public static function pastaSaveImg()
    {
        return '.blur/';
    }

    public function gerarPreImagemLFM($path_img)
    {
        if(strlen($path_img) < 5)
            return false;

        if(!Str::startsWith($path_img, '/imagens/'))
            return false;

        if(!file_exists(public_path($path_img)))
            return false;

        $pos = strripos($path_img, '/');
        $caminho = public_path() . substr($path_img, 0, $pos + 1) . self::pastaSaveImg();
        $nome = 'small-' . substr($path_img, $pos + 1);
        $nova_img = $caminho . $nome;
        $principal_img = public_path() . $path_img;

        try {
            if(!file_exists($caminho))
                mkdir($caminho, 0755);
        } catch (\Throwable $th) {
            $msg = '[Portal: erro ao criar a pasta "' . self::pastaSaveImg() . '" com o caminho "' . $caminho. '"], ';
            \Log::error($msg . '[Erro: '.$th->getMessage().'], [Código: '.$th->getCode().'], [Arquivo: '.$th->getFile().'], [Linha: '.$th->getLine().']');
        }

        if(file_exists($caminho))
            return exec('ffmpeg -y -i ' . $principal_img . ' -vf scale=20:-1 ' . $nova_img);

        return false;
    }

    public function localPreImagemLFM($principal_img)
    {
        if(strlen($principal_img) < 3)
            return '';

        $pos = strripos($principal_img, '/');
        $img = 'small-' . substr($principal_img, $pos + 1);
        $nova_img = substr($principal_img, 0, $pos + 1) . self::pastaSaveImg() . $img;

        if(!file_exists(public_path() . $nova_img) && ($this->gerarPreImagemLFM($principal_img) === false))
            return '';

        return asset($nova_img);
    }
}
