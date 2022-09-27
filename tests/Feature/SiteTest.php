<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Noticia;

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
        $noticia = factory('App\Noticia')->create([
            'idregional' => null
        ]);

        $this->get('/')->assertSee($noticia->titulo);
    }

    /** @test */
    public function noticias_is_shown_on_homepage()
    {
        $noticias = factory('App\Noticia', 7)->create([
            'idregional' => null
        ]);

        $this->get('/')
        ->assertSee($noticias->get(0)->titulo)
        ->assertSee($noticias->get(1)->titulo)
        ->assertSee($noticias->get(2)->titulo)
        ->assertSee($noticias->get(3)->titulo)
        ->assertSee($noticias->get(4)->titulo)
        ->assertSee($noticias->get(5)->titulo)
        ->assertDontSee($noticias->get(6)->titulo);
    }

    /** @test */
    public function link_to_noticia_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'idregional' => null
        ]);

        $this->get('/')->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function cotidiano_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Cotidiano'
        ]);

        $this->get('/')->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function cotidianos_is_shown_on_homepage()
    {
        $noticias = factory('App\Noticia', 5)->create([
            'categoria' => 'Cotidiano'
        ]);

        $this->get('/')
        ->assertSee($noticias->get(0)->titulo)
        ->assertSee($noticias->get(1)->titulo)
        ->assertSee($noticias->get(2)->titulo)
        ->assertSee($noticias->get(3)->titulo)
        ->assertDontSee($noticias->get(4)->titulo);
    }

    /** @test */
    public function post_is_shown_on_homepage()
    {
        $post = factory('App\Post')->create();

        $this->get('/')->assertSee($post->titulo);
    }

    /** @test */
    public function posts_is_shown_on_homepage()
    {
        $posts = factory('App\Post', 4)->create();

        $this->get('/')
        ->assertSee($posts->get(0)->titulo)
        ->assertSee($posts->get(1)->titulo)
        ->assertSee($posts->get(2)->titulo)
        ->assertDontSee($posts->get(3)->titulo);
    }

    /** @test */
    public function feiras_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Feiras'
        ]);

        $this->get(route('site.feiras'))->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function acoes_fiscalizacao_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Fiscalização'
        ]);

        $this->get(route('fiscalizacao.acoesfiscalizacao'))->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function espaco_contador_is_shown_on_homepage()
    {
        $noticia = factory('App\Noticia')->create([
            'categoria' => 'Espaço do Contador'
        ]);

        $this->get(route('fiscalizacao.espacoContador'))->assertSee($noticia->titulo)
        ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function representante_logged_is_shown_on_homepage()
    {
        $representante = factory('App\Representante')->create();

        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030'])
        ->assertRedirect(route('representante.dashboard'));

        $this->get('/')->assertSee($representante->nome);
    }

    /** @test */
    public function user_externo_logged_is_shown_on_homepage()
    {
        $user_externo = factory('App\UserExterno')->create();
        $dados = [
            'cpf_cnpj' => $user_externo['cpf_cnpj'],
            'password' => 'Teste102030'
        ];
        $this->post(route('externo.login.submit'), $dados)
        ->assertRedirect(route('externo.dashboard'));

        $this->get('/')->assertSee($user_externo->nome);
    }
}
