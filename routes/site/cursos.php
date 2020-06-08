<?php

Route::get('cursos', 'CursoController@cursosView')->name('cursos.index.website');
Route::get('cursos/{id}', 'CursoController@show')->name('cursos.show');
Route::get('cursos/{id}/inscricao', 'CursoInscritoController@inscricaoView')->name('curso.inscricao.website');
Route::post('cursos/{id}/inscricao', 'CursoInscritoController@inscricao')->name('cursos.inscricao');
Route::get('cursos-anteriores', 'CursoSiteController@cursosAnterioresView')->name('cursos.previous.website');
// Redirects
Route::get('/curso/{id}', function($id){
    return redirect(route('cursos.show', $id), 301);
});