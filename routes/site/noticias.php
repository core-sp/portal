<?php

Route::get('/noticias', 'NoticiaController@siteGrid')->name('noticias.siteGrid');
Route::get('/noticias/{slug}', 'NoticiaController@show')->name('noticias.show');
// Redirects
Route::get('/noticia/{slug}', function($slug){
    return redirect(route('noticias.show', $slug), 301);
});