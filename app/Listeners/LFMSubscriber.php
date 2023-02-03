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
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LFMSubscriber
{
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

        $events->listen(
            ImageIsUploading::class,
            'App\Listeners\LFMSubscriber@onImageIsUploading'
        );

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
    }

    public function handle($event)
    {
        $method = 'on'.class_basename($event);
        if (method_exists($this, $method)) {
            call_user_func([$this, $method], $event);
        }
    }

    public function onImageIsDeleting(ImageIsDeleting $event)
    {
        $path = $event->path();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') está excluindo o seguinte arquivo: ' . $path);
    }

    public function onImageWasDeleted(ImageWasDeleted $event)
    {
        $path = $event->path();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') excluiu o seguinte arquivo: ' . $path);
    }

    public function onImageIsUploading(ImageIsUploading $event)
    {
        $path = $event->path();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') está anexando o seguinte arquivo: ' . $path);
    }

    public function onImageWasUploaded(ImageWasUploaded $event)
    {
        $path = $event->path();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') anexou o seguinte arquivo: ' . $path);
    }

    public function onImageIsRenaming(ImageIsRenaming $event)
    {
        $path = $event->oldPath();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') está renomeando o seguinte arquivo: ' . $path);
    }

    public function onImageWasRenamed(ImageWasRenamed $event)
    {
        $path = $event->newPath();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') renomeou o arquivo antigo para: ' . $path);
    }

    public function onFolderIsRenaming(FolderIsRenaming $event)
    {
        $path = $event->oldPath();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') está renomeando a seguinte pasta: ' . $path);
    }

    public function onFolderWasRenamed(FolderWasRenamed $event)
    {
        $path = $event->newPath();
        $ip = "[IP: " . request()->ip() . "] - ";
        Log::channel('interno')->info($ip . auth()->user()->nome.' (usuário '.auth()->user()->idusuario.') renomeou a pasta antiga para: ' . $path);
    }
}
