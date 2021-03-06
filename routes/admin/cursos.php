<?php

// Rotas para cursos
Route::prefix('cursos')->group(function(){
    Route::get('/', 'CursoController@index')->name('cursos.index');
    Route::get('/busca', 'CursoController@busca')->name('cursos.busca');
    Route::get('/create', 'CursoController@create')->name('cursos.create');
    Route::post('/', 'CursoController@store')->name('cursos.store');
    Route::get('/{id}/edit', 'CursoController@edit')->name('cursos.edit');
    Route::patch('/{id}', 'CursoController@update')->name('cursos.update');
    Route::delete('/{id}', 'CursoController@destroy')->name('cursos.destroy');
    Route::get('/lixeira', 'CursoController@lixeira')->name('cursos.lixeira');
    Route::get('/{id}/restore', 'CursoController@restore')->name('cursos.restore');
    // Lida com a parte de inscritos
    Route::get('/inscritos/{id}', 'CursoController@inscritos')->name('inscritos.index');
    Route::get('/inscritos/{id}/busca', 'CursoInscritoController@busca');
    Route::get('/inscritos/editar/{id}', 'CursoInscritoController@edit');
    Route::put('/inscritos/editar/{id}', 'CursoInscritoController@update');
    Route::put('/inscritos/confirmar-presenca/{id}', 'CursoInscritoController@confirmarPresenca');
    Route::put('/inscritos/confirmar-falta/{id}', 'CursoInscritoController@confirmarFalta');
    Route::get('/adicionar-inscrito/{id}', 'CursoInscritoController@create');
    Route::post('/adicionar-inscrito/{id}', 'CursoInscritoController@store');
    Route::delete('/cancelar-inscricao/{id}', 'CursoInscritoController@destroy');
    Route::get('/inscritos/download/{id}', 'CursoInscritoController@download');
});