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
