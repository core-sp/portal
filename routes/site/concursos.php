<?php

Route::get('/concursos', 'ConcursoController@siteGrid')->name('concursos.siteGrid');
Route::get('/concursos/busca', 'ConcursoController@siteBusca')->name('concursos.siteBusca');
Route::get('/concursos/{id}', 'ConcursoController@show')->name('concursos.show');
// Redirects
Route::get('/concurso/{id}', function($id){
    return redirect(route('concursos.show', $id), 301);
});