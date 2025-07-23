<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ImagensLazyLoad
{
    private $baseImagemLFM;
    private $baseThumbLFM;
    private $nomeLFM;
    private $pathCompletoLFM;
    private $ext;

    public static function pastaSaveImg()
    {
        return '.blur/';
    }

    public static function inicioBlurSaveImg()
    {
        return 'small-';
    }

    public static function criarPastaBlur($blur)
    {
        try {
            if(!file_exists($blur))
                mkdir($blur, 0755);
        } catch (\Throwable $th) {
            $msg = '[Portal: erro ao criar a pasta "' . self::pastaSaveImg() . '" com o caminho "' . $blur. '"], ';
            \Log::error($msg . '[Erro: '.$th->getMessage().'], [Código: '.$th->getCode().'], [Arquivo: '.$th->getFile().'], [Linha: '.$th->getLine().']');
        }
    }

    public function logAcao($old, $new)
    {
        \Log::channel('interno')->info('[Portal - Ação Blur] - Imagem "'. str_replace(public_path(), '..', $old) .'" renomeada para "'. str_replace(public_path(), '..', $new) .'".');
    }

    public function logRollback($new, $old)
    {
        \Log::channel('interno')->info('[Portal - Rollback Ação Blur] - Imagem "'. str_replace(public_path(), '..', $new) .'" renomeada de volta para "'. str_replace(public_path(), '..', $old) .'" por falta da pasta ".blur".');
    }

    public function inicializaLFM($img)
    {
        if(strlen($img) < 5)
            return false;

        if(!Str::startsWith($img, '/imagens/'))
            return false;

        $this->pathCompletoLFM = public_path() . $img;

        if(!file_exists($this->pathCompletoLFM))
            return false;

        $posLFM = strripos($img, '/');

        $this->baseImagemLFM = substr($img, 0, $posLFM + 1);
        $this->nomeLFM = substr($img, $posLFM + 1);
        $this->baseThumbLFM = public_path() . $this->baseImagemLFM . 'thumbnails/';
        $this->ext = substr($this->nomeLFM, strripos($this->nomeLFM, '.'));

        return true;
    }

    public function renomear($principal_img, $old_nomeLFM)
    {
        rename($this->pathCompletoLFM, $principal_img);

        if(file_exists($principal_img))
            $this->logAcao($this->pathCompletoLFM, $principal_img);

        if(file_exists($this->baseThumbLFM . $old_nomeLFM))
            rename($this->baseThumbLFM . $old_nomeLFM, $this->baseThumbLFM . $this->nomeLFM);

        if(file_exists($this->baseThumbLFM . $this->nomeLFM))
            $this->logAcao($this->baseThumbLFM . $old_nomeLFM, $this->baseThumbLFM . $this->nomeLFM);
    }

    public function rollbackRenomear($principal_img, $old_nomeLFM)
    {
        // Rollback
        rename($principal_img, $this->pathCompletoLFM);

        if(file_exists($this->pathCompletoLFM))
            $this->logRollback($principal_img, $this->pathCompletoLFM);

        if(file_exists($this->baseThumbLFM . $this->nomeLFM))
            rename($this->baseThumbLFM . $this->nomeLFM, $this->baseThumbLFM . $old_nomeLFM);

        if(file_exists($this->baseThumbLFM . $old_nomeLFM))
            $this->logRollback($this->baseThumbLFM . $this->nomeLFM, $this->baseThumbLFM . $old_nomeLFM);

        return false;
    }

    public function gerarPreImagemLFM($path_img, $update = true)
    {
        $init = $this->inicializaLFM($path_img);
        if(!$init)
            return false;

        $old_nomeLFM = $this->nomeLFM;
        $base = public_path() . $this->baseImagemLFM;
        $blur = $base . self::pastaSaveImg();

        if($update)
            $this->nomeLFM = hash('sha256', file_get_contents($this->pathCompletoLFM)) . $this->ext;

        $nome = self::inicioBlurSaveImg() . $this->nomeLFM;
        $blur_img = $blur . $nome;
        $principal_img = $base . $this->nomeLFM;

        if($update && ($this->pathCompletoLFM != $principal_img))
            $this->renomear($principal_img, $old_nomeLFM);

        self::criarPastaBlur($blur);

        if($update && !file_exists($blur))
            return $this->rollbackRenomear($principal_img, $old_nomeLFM);

        if(!file_exists($blur_img))
            exec('ffmpeg -y -i "' . $principal_img . '" -vf scale=20:-1 "' . $blur_img . '"');

        return file_exists($blur_img) ? $this->baseImagemLFM . $this->nomeLFM : false;
    }

    public function localPreImagemLFM($principal_img)
    {
        if(!$this->gerarPreImagemLFM($principal_img, false))
            return '';

        $blur_img = $this->baseImagemLFM . self::pastaSaveImg() . self::inicioBlurSaveImg() . $this->nomeLFM;

        return !file_exists(public_path() . $blur_img) ? '' : asset($blur_img);
    }
}
