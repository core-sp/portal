<?php

Route::prefix('paginas')->group(function() {
    Route::get('/', 'PaginaController@index')->name('paginas.index');
    Route::get('/create', 'PaginaController@create')->name('paginas.create');
    Route::post('/', 'PaginaController@store')->name('paginas.store');
    Route::get('/{id}/edit', 'PaginaController@edit')->name('paginas.edit');
    Route::patch('/{id}', 'PaginaController@update')->name('paginas.update');
    Route::delete('/{id}', 'PaginaController@destroy')->name('paginas.destroy');
    Route::get('/lixeira', 'PaginaController@lixeira')->name('paginas.trashed');
    Route::get('/{id}/restore', 'PaginaController@restore')->name('paginas.restore');
    Route::get('/busca', 'PaginaController@busca')->name('paginas.busca');
});