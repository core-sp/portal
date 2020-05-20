<?php

Route::get('seccionais', 'RegionalController@siteGrid')->name('regionais.siteGrid');
Route::get('seccionais/{id}', 'RegionalController@show')->name('regionais.show');
// Redirects
Route::get('/seccional/{id}', function($id){
    return redirect(route('regionais.show', $id), 301);
});