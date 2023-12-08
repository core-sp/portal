<?php

use App\HomeImagem;
use Faker\Generator as Faker;

$factory->define(HomeImagem::class, function (Faker $faker) {
    return [
        'funcao' => 'bannerprincipal',
        'ordem' => 1,
        'url' => '/imagens/'.date('Y-m').'/'.$faker->word.'.png',
        'url_mobile' => '/imagens/'.date('Y-m').'/'.$faker->word.'.png',
        'link' => $faker->url,
        'target' => '_blank'
    ];
});

$factory->afterCreating(HomeImagem::class, function ($homeImagens, $faker) {
    $homeImagens->each(function ($banner) {
        $banner->update(['ordem' => $banner->idimagem]);
    });
});

$factory->state(HomeImagem::class, 'itens_home', function ($faker) {
    return [
        'funcao' => 'itens_home',
        'url' => null,
        'url_mobile' => null,
        'link' => '#',
        'target' => '_self'
    ];
});

$factory->afterCreatingState(HomeImagem::class, 'itens_home', function ($homeImagens, $faker) {
    $funcoes = [
        'header_logo', 'header_fundo', 'cards_1', 'cards_2', 'cards_laterais_1', 'cards_laterais_2', 'calendario', 'footer', 'neve', 'popup_video'
    ];

    $homeImagens->each(function ($banner) use($funcoes){
        $id = $banner->idimagem - 1;
        $chave = $funcoes[$id];
        switch ($chave) {
            case 'neve':
            case 'popup_video':
                $banner->update(['funcao' => $chave, 'ordem' => 1]);
                break;
            case 'cards_1':
            case 'cards_2':
                $ordem = $chave == 'cards_1' ? 1 : 2;
                $banner->update(['funcao' => 'cards', 'ordem' => $ordem, 'url' => HomeImagem::padrao()[$chave.'_default'], 'url_mobile' => HomeImagem::padrao()[$chave.'_default']]);
                break;
            case 'cards_laterais_1':
            case 'cards_laterais_2':
                $ordem = $chave == 'cards_laterais_1' ? 1 : 2;
                $banner->update(['funcao' => 'cards_laterais', 'ordem' => $ordem, 'url' => HomeImagem::padrao()[$chave.'_default'], 'url_mobile' => HomeImagem::padrao()[$chave.'_default']]);
                break;
            default:
                $banner->update(['funcao' => $chave, 'ordem' => 1, 'url' => HomeImagem::padrao()[$chave.'_default'], 'url_mobile' => HomeImagem::padrao()[$chave.'_default']]);
                break;
        }
    });

    $homeImagens->refresh();
});