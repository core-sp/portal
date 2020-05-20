<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function homepage_is_shown_correctly()
    {
        $this->get('/')->assertOk();
    }

    /** @test */
    public function noticia_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create();

        $this->get('/')->assertSee($noticia->titulo);
    }

    /** @test */
    public function link_to_noticia_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create();

        $this->get('/')->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function noticia_cotidiano_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Cotidiano'
        ]);

        $this->get('/')
            ->assertSee($noticia->titulo)
            ->assertSee(route('noticias.show', $noticia->slug));
    }
}
