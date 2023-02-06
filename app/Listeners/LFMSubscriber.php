<?php

namespace App\Listeners;

use UniSharp\LaravelFilemanager\Events\ImageIsDeleting;
use UniSharp\LaravelFilemanager\Events\ImageWasDeleted;
use UniSharp\LaravelFilemanager\Events\ImageIsUploading;
use UniSharp\LaravelFilemanager\Events\ImageWasUploaded;
use UniSharp\LaravelFilemanager\Events\ImageIsRenaming;
use UniSharp\LaravelFilemanager\Events\ImageWasRenamed;
use UniSharp\LaravelFilemanager\Events\FolderIsRenaming;
use UniSharp\LaravelFilemanager\Events\FolderWasRenamed;
use UniSharp\LaravelFilemanager\Events\ImageIsCropping;
use UniSharp\LaravelFilemanager\Events\ImageWasCropped;
use UniSharp\LaravelFilemanager\Events\ImageIsResizing;
use UniSharp\LaravelFilemanager\Events\ImageWasResized;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LFMSubscriber
{
    private function getPath($path)
    {
        return str_replace(dirname($path, 4), '..', $path);
    }

    private function dispararLog($texto)
    {
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') ' . $texto);
    }

    public function subscribe($events)
    {
        $events->listen(
            ImageIsDeleting::class,
            'App\Listeners\LFMSubscriber@onImageIsDeleting'
        );

        $events->listen(
            ImageWasDeleted::class,
            'App\Listeners\LFMSubscriber@onImageWasDeleted'
        );

        // $events->listen(
        //     ImageIsUploading::class,
        //     'App\Listeners\LFMSubscriber@onImageIsUploading'
        // );

        $events->listen(
            ImageWasUploaded::class,
            'App\Listeners\LFMSubscriber@onImageWasUploaded'
        );

        $events->listen(
            ImageIsRenaming::class,
            'App\Listeners\LFMSubscriber@onImageIsRenaming'
        );

        $events->listen(
            ImageWasRenamed::class,
            'App\Listeners\LFMSubscriber@onImageWasRenamed'
        );

        $events->listen(
            FolderIsRenaming::class,
            'App\Listeners\LFMSubscriber@onFolderIsRenaming'
        );

        $events->listen(
            FolderWasRenamed::class,
            'App\Listeners\LFMSubscriber@onFolderWasRenamed'
        );

        $events->listen(
            ImageIsCropping::class,
            'App\Listeners\LFMSubscriber@onImageIsCropping'
        );

        $events->listen(
            ImageWasCropped::class,
            'App\Listeners\LFMSubscriber@onImageWasCropped'
        );

        $events->listen(
            ImageIsResizing::class,
            'App\Listeners\LFMSubscriber@onImageIsResizing'
        );

        $events->listen(
            ImageWasResized::class,
            'App\Listeners\LFMSubscriber@onImageWasResized'
        );
    }

    public function onImageIsDeleting(ImageIsDeleting $event)
    {
        $path = $this->getPath($event->path());
        $texto = 'está excluindo o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }

    public function onImageWasDeleted(ImageWasDeleted $event)
    {
        $path = $this->getPath($event->path());
        $texto = 'excluiu o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }

    // Bug do package LFM quando anexando vários arquivos ao mesmo tempo
    // public function onImageIsUploading(ImageIsUploading $event)
    // {
    //     $path = $this->getPath($event->path());
    //     $texto = 'está anexando o seguinte arquivo: ' . $path;
    //     $this->dispararLog($texto);
    // }

    public function onImageWasUploaded(ImageWasUploaded $event)
    {
        $path = $this->getPath($event->path());
        $texto = 'anexou o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }

    public function onImageIsRenaming(ImageIsRenaming $event)
    {
        $path = $this->getPath($event->oldPath());
        $texto = 'está renomeando o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }

    public function onImageWasRenamed(ImageWasRenamed $event)
    {
        $old_path = basename($event->oldPath());
        $path = $this->getPath($event->newPath());
        $texto = 'renomeou o arquivo "' . $old_path . '" para: ' . $path;
        $this->dispararLog($texto);
    }

    public function onFolderIsRenaming(FolderIsRenaming $event)
    {
        $path = $this->getPath($event->oldPath());
        $texto = 'está renomeando a seguinte pasta: ' . $path;
        $this->dispararLog($texto);
    }

    public function onFolderWasRenamed(FolderWasRenamed $event)
    {
        $old_path = basename($event->oldPath());
        $path = $this->getPath($event->newPath());
        $texto = 'renomeou o arquivo "' . $old_path . '" para: ' . $path;
        $this->dispararLog($texto);
    }

    public function onImageIsCropping(ImageIsCropping $event)
    {
        $path = $this->getPath($event->path());
        $texto = 'está cortando o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }

    public function onImageWasCropped(ImageWasCropped $event)
    {
        $path = $this->getPath($event->path());
        $texto = 'cortou o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }

    public function onImageIsResizing(ImageIsResizing $event)
    {
        $path = $this->getPath($event->path());
        $texto = 'está redimensionando o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }

    public function onImageWasResized(ImageWasResized $event)
    {
        $path = $this->getPath($event->path());
        $texto = 'redimensionou o seguinte arquivo: ' . $path;
        $this->dispararLog($texto);
    }
}
