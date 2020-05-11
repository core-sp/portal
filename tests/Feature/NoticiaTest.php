<?php

namespace Tests\Feature;

use App\Noticia;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoticiaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permissao::insert([
            [
                'controller' => 'NoticiaController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ]
        ]);
    }
    
    /** @test */
    public function route_to_create_noticia_is_working_properly()
    {
        $this->signInAsAdmin();
        $attributes = factory('App\Noticia')->raw();

        $this->get('/admin/noticias/criar')->assertOk();
        $this->post('/admin/noticias/criar', $attributes);
        $this->assertDatabaseHas('noticias', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function noticias_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();
        $noticiaDois = factory('App\Noticia')->create();
        
        $this->get('/admin/noticias')->assertSee($noticia->titulo);
        $this->get('/admin/noticias')->assertSee($noticiaDois->titulo);
    }

    /** @test */
    public function noticia_is_shown_on_the_website()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();

        $this->get('/noticia/' . $noticia->slug)
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    /** @test */
    public function noticia_with_same_title_cannot_be_created()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();
        $attributes = factory('App\Noticia')->raw([
            'titulo' => $noticia->titulo
        ]);
        $this->post('/admin/noticias/criar', $attributes);
        $this->assertEquals(1, Noticia::count());
    }

    /** @test */
    public function noticia_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->put('/admin/noticias/editar/' . $noticia->idnoticia, [
            'idusuario' => $user->idusuario,
            'titulo' => 'Novo titulo',
            'conteudo' => $noticia->conteudo
        ]);

        $this->assertEquals(Noticia::find($noticia->idnoticia)->titulo, 'Novo titulo');
    }

    /** @test */
    public function noticia_cannot_be_updated_to_existing_title()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $noticiaDois = factory('App\Noticia')->create();

        $this->put('/admin/noticias/editar/' . $noticia->idnoticia, [
            'titulo' => $noticiaDois->titulo
        ]);
        
        $this->assertNotEquals(Noticia::find($noticia->idnoticia)->titulo, $noticiaDois->titulo);
        $this->assertEquals(Noticia::find($noticia->idnoticia)->titulo, $noticia->titulo);
    }
}
