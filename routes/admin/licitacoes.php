<?php

Route::prefix('licitacoes')->group(function(){
    Route::get('/', 'LicitacaoController@index')->name('licitacoes.index');
    Route::get('/create', 'LicitacaoController@create')->name('licitacoes.create');
    Route::post('/', 'LicitacaoController@store')->name('licitacoes.store');
    Route::get('/{id}/edit', 'LicitacaoController@edit')->name('licitacoes.edit');
    Route::patch('/{id}', 'LicitacaoController@update')->name('licitacoes.update');
    Route::delete('/{id}', 'LicitacaoController@destroy')->name('licitacoes.destroy');
    Route::get('/{id}/restore', 'LicitacaoController@restore')->name('licitacoes.restore');
    Route::get('/busca', 'LicitacaoController@busca')->name('licitacoes.busca');
    Route::get('/lixeira', 'LicitacaoController@lixeira')->name('licitacoes.trashed');
});