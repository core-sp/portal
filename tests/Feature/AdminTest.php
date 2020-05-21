<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_logged_in_user_can_see_the_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $this->get('/admin')->assertOk()->assertSee($user->nome);
    }

    /** @test */
    public function a_logged_in_user_can_see_his_info_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $this->get('/admin/perfil')
            ->assertOk()
            ->assertSee($user->nome)
            ->assertSee($user->username)
            ->assertSee($user->email);
    }

    /** @test */
    public function the_admin_menu_has_correct_links()
    {
        $this->signInAsAdmin();

        $this->get('/admin')
            ->assertSee(route('regionais.index'))
            ->assertSee(route('paginas.index'))
            ->assertSee(route('paginas.create'))
            ->assertSee(route('noticias.index'))
            ->assertSee(route('noticias.create'))
            ->assertSee(route('posts.index'))
            ->assertSee(route('posts.create'))
            ->assertSee(route('concursos.index'))
            ->assertSee(route('concursos.create'));
    }

    /** @test */
    public function non_authorized_users_cannot_see_links()
    {
        $this->signIn();

        $this->get('/admin')
            ->assertDontSee(route('paginas.index'))
            ->assertDontSee(route('paginas.create'))
            ->assertDontSee(route('noticias.index'))
            ->assertDontSee(route('noticias.create'))
            ->assertDontSee(route('posts.index'))
            ->assertDontSee(route('posts.create'))
            ->assertDontSee(route('concursos.index'))
            ->assertDontSee(route('concursos.create'));
    }
}
