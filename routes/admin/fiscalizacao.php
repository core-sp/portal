<?php

Route::prefix('fiscalizacao')->group(function() {
    Route::get('/', 'FiscalizacaoController@index')->name('fiscalizacao.index');
    Route::get('/createAno', 'FiscalizacaoController@createAno')->name('fiscalizacao.createano');
    Route::post('/createAno', 'FiscalizacaoController@storeAno')->name('fiscalizacao.storeano');
    Route::post('/updateStatus', 'FiscalizacaoController@updateStatus')->name('fiscalizacao.updatestatus');
    Route::get('/editAno/{ano}', 'FiscalizacaoController@editAno')->name('fiscalizacao.editano');
    Route::post('/editAno/{ano}', 'FiscalizacaoController@updateAno')->name('fiscalizacao.updateano');

    Route::get('/mapa-fiscalizacao', 'FiscalizacaoController@mostrarMapa')->name('fiscalizacao.mapa');
});