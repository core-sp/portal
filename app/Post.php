<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function path()
    {
        return '/blog/' . $this->slug;
    }

    public function latestPosts()
    {
        return $this->orderBy('created_at', 'DESC')->limit(3)->get();
    }
}
