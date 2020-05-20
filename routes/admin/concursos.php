<?php

Route::prefix('concursos')->group(function(){
    Route::get('/', 'ConcursoController@index')->name('concursos.index');
    Route::get('/busca', 'ConcursoController@busca')->name('concursos.busca');
    Route::get('/create', 'ConcursoController@create')->name('concursos.create');
    Route::post('/', 'ConcursoController@store')->name('concursos.store');
    Route::get('/{id}/edit', 'ConcursoController@edit')->name('concursos.edit');
    Route::patch('/{id}', 'ConcursoController@update')->name('concursos.update');
    Route::delete('/{id}', 'ConcursoController@destroy')->name('concursos.destroy');
    Route::get('/lixeira', 'ConcursoController@lixeira')->name('concursos.lixeira');
    Route::get('/{id}/restore', 'ConcursoController@restore')->name('concursos.restore');
});