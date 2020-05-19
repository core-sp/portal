<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function noticia_is_shown_on_homepage_after_its_creation()
    {
        $noticia = factory('App\Noticia')->create();

        $this->get('/')->assertSee($noticia->titulo);
    }

    /** @test */
    public function link_to_noticia_is_shown_on_homepage_after_its_creation()
    {
        $noticia = factory('App\Noticia')->create();

        $this->get('/')->assertSee(route('noticias.show', $noticia->slug));
    }
}
