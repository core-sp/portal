<?php

Route::prefix('regionais')->group(function() {
    Route::get('/', 'RegionalController@index')->name('regionais.index');
    Route::get('/busca', 'RegionalController@busca')->name('regionais.busca');
    Route::get('/{id}/edit', 'RegionalController@edit')->name('regionais.edit');
    Route::patch('/{id}', 'RegionalController@update')->name('regionais.update');
});