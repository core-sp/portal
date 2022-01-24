<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertRedirect(route('login'));
        $this->get('/admin/usuarios/perfis/criar')->assertRedirect(route('login'));
        $this->post('/admin/usuarios/perfis/criar')->assertRedirect(route('login'));
        $this->get('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertRedirect(route('login'));
        $this->put('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertRedirect(route('login'));
        $this->delete('/admin/usuarios/perfis/apagar/'.$perfil->idperfil)->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertForbidden();
        $this->get('/admin/usuarios/perfis/criar')->assertForbidden();
        $this->post('/admin/usuarios/perfis/criar')->assertForbidden();
        $this->get('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertForbidden();
        $this->put('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertForbidden();
        $this->delete('/admin/usuarios/perfis/apagar/'.$perfil->idperfil)->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertOk();
        $this->get('/admin/usuarios/perfis/criar')->assertOk();
        $this->post('/admin/usuarios/perfis/criar')->assertStatus(302);
        $this->get('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertOk();
        $this->put('/admin/usuarios/perfis/editar/'.$perfil->idperfil)->assertStatus(302);
        $this->delete('/admin/usuarios/perfis/apagar/'.$perfil->idperfil)->assertStatus(302);
    }
}
