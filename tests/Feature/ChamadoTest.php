<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChamadoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $chamado = factory('App\Chamado')->create();

        $this->get(route('chamados.lista'))->assertRedirect(route('login'));
        $this->get('/admin/chamados/busca')->assertRedirect(route('login'));
        $this->get('/admin/chamados/criar')->assertRedirect(route('login'));
        $this->post('/admin/chamados/criar')->assertRedirect(route('login'));
        $this->get('/admin/chamados/editar/'.$chamado->idchamado)->assertRedirect(route('login'));
        $this->put('/admin/chamados/editar/'.$chamado->idchamado)->assertRedirect(route('login'));
        $this->put('/admin/chamados/resposta/'.$chamado->idchamado)->assertRedirect(route('login'));
        $this->get('/admin/chamados/ver/'.$chamado->idchamado)->assertRedirect(route('login'));
        $this->delete('/admin/chamados/apagar/'.$chamado->idchamado)->assertRedirect(route('login'));
        $this->get('/admin/chamados/concluidos')->assertRedirect(route('login'));
        $this->get('/admin/chamados/restore/'.$chamado->idchamado)->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $chamado = factory('App\Chamado')->create();

        $this->get(route('chamados.lista'))->assertForbidden();
        $this->get('/admin/chamados/busca')->assertForbidden();
        $this->put('/admin/chamados/resposta/'.$chamado->idchamado)->assertForbidden();
        $this->delete('/admin/chamados/apagar/'.$chamado->idchamado)->assertForbidden();
        $this->get('/admin/chamados/concluidos')->assertForbidden();
        $this->get('/admin/chamados/restore/'.$chamado->idchamado)->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $user = $this->signInAsAdmin();
        $chamado = factory('App\Chamado')->create([
            'idusuario' => $user->idusuario
        ]);

        $this->get(route('chamados.lista'))->assertOk();
        $this->get('/admin/chamados/busca')->assertOk();
        $this->get('/admin/chamados/criar')->assertOk();
        $this->post('/admin/chamados/criar')->assertStatus(302);
        $this->get('/admin/chamados/editar/'.$chamado->idchamado)->assertOk();
        $this->put('/admin/chamados/editar/'.$chamado->idchamado)->assertStatus(302);
        $this->put('/admin/chamados/resposta/'.$chamado->idchamado)->assertStatus(302);
        $this->get('/admin/chamados/ver/'.$chamado->idchamado)->assertOk();
        $this->delete('/admin/chamados/apagar/'.$chamado->idchamado)->assertStatus(302);
        $this->get('/admin/chamados/concluidos')->assertOk();
        $this->get('/admin/chamados/restore/'.$chamado->idchamado)->assertStatus(302);
    }
}
