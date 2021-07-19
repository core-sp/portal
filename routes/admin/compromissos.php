<?php

Route::prefix('compromissos')->group(function() {
    Route::get('/', 'CompromissoController@index')->name('compromisso.index');
    Route::get('/create', 'CompromissoController@create')->name('compromisso.create');
    Route::post('/create', 'CompromissoController@store')->name('compromisso.store');
    Route::get('/edit/{id}', 'CompromissoController@edit')->name('compromisso.edit');
    Route::post('/edit/{id}', 'CompromissoController@update')->name('compromisso.update');
    Route::get('/busca', 'CompromissoController@busca')->name('compromisso.busca');
    Route::get('/filtro', 'CompromissoController@index')->name('compromisso.filtro');
    Route::delete('/destroy/{id}', 'CompromissoController@destroy')->name('compromisso.destroy');
});