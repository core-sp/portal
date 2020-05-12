<?php

Route::resource('/posts', 'PostsController')->except(['show']);
Route::get('/posts/busca', 'PostsController@busca');