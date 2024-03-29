<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $table = 'newsletters';
    protected $primaryKey = 'idnewsletter';

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idnewsletter');
    }
}
