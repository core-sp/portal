<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Noticia;

class NoticiaTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    function a_noticia_can_be_created()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->assertDatabaseHas('noticias', ['titulo' => $noticia->titulo]);
        $this->assertEquals(1, Noticia::count());
    }

    /** @test */
    function multiple_noticias_can_be_created()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $noticiaDois = factory('App\Noticia')->create();

        $this->assertDatabaseHas('noticias', ['titulo' => $noticia->titulo]);
        $this->assertDatabaseHas('noticias', ['titulo' => $noticiaDois->titulo]);
        $this->assertEquals(2, Noticia::count());
    }

    /** @test */
    function a_noticia_without_titulo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Post')->create([
            'titulo' => ''
        ]);

        $this->assertDatabaseMissing('noticias', ['idnoticia' => $noticia->idnoticia]);
        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function noticia_can_be_updated()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        Noticia::findOrFail($noticia->idnoticia)->update([
            'titulo' => 'Novo titulo'
        ]);
        
        $this->assertDatabaseHas('noticias', ['titulo' => 'Novo titulo']);
    }
}
