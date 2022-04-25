<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     Permissao::insert([
    //         [
    //             'controller' => 'UserController',
    //             'metodo' => 'index',
    //             'perfis' => '1,'
    //         ]
    //     ]);
    // }

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $user = factory('App\User')->create();
        
        $this->get(route('usuarios.lista'))->assertRedirect(route('login'));
        $this->get('/admin/usuarios/busca')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/criar')->assertRedirect(route('login'));
        $this->post('/admin/usuarios/criar')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/editar/'.$user->idusuario)->assertRedirect(route('login'));
        $this->put('/admin/usuarios/editar/'.$user->idusuario)->assertRedirect(route('login'));
        $this->delete('/admin/usuarios/apagar/'.$user->idusuario)->assertRedirect(route('login'));
        $this->get('/admin/usuarios/lixeira')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/restore/'.$user->idusuario)->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $user = factory('App\User')->create();
        
        $this->get(route('usuarios.lista'))->assertForbidden();
        $this->get('/admin/usuarios/busca')->assertForbidden();
        $this->get('/admin/usuarios/criar')->assertForbidden();
        $this->post('/admin/usuarios/criar')->assertForbidden();
        $this->get('/admin/usuarios/editar/'.$user->idusuario)->assertForbidden();
        $this->put('/admin/usuarios/editar/'.$user->idusuario)->assertForbidden();
        $this->delete('/admin/usuarios/apagar/'.$user->idusuario)->assertForbidden();
        $this->get('/admin/usuarios/lixeira')->assertForbidden();
        $this->get('/admin/usuarios/restore/'.$user->idusuario)->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $this->assertAuthenticated('web');
        
        $user = factory('App\User')->create();
        
        $this->get(route('usuarios.lista'))->assertOk();
        $this->get('/admin/usuarios/busca')->assertOk();
        $this->get('/admin/usuarios/criar')->assertOk();
        $this->post('/admin/usuarios/criar')->assertStatus(302);
        $this->get('/admin/usuarios/editar/'.$user->idusuario)->assertOk();
        $this->put('/admin/usuarios/editar/'.$user->idusuario)->assertStatus(302);
        $this->delete('/admin/usuarios/apagar/'.$user->idusuario)->assertStatus(302);
        $this->get('/admin/usuarios/lixeira')->assertOk();
        $this->get('/admin/usuarios/restore/'.$user->idusuario)->assertStatus(302);
    }
}
