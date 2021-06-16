<?php

namespace App\Repositories;

use App\Newsletter;

class NewsletterRepository 
{
    public function getCountAllNewsletter()
    {
        return Newsletter::all()->count();
    }
}