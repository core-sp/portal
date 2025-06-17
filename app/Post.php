<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ImagensLazyLoad;

class Post extends Model
{
    use SoftDeletes, ImagensLazyLoad;

    protected $guarded = [];

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function imgBlur()
    {
        return $this->localPreImagemLFM($this->img);
    }
}
