<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;

class PlantaoJuridicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permissao::insert([
            [
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ],[
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ],[
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'index',
                'perfis' => '1,'
            ],[
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'create',
                'perfis' => '1,'
            ],[
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ],[
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ]
        ]);
    }

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $plantao = factory('App\PlantaoJuridico')->create();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
        
        $this->get(route('plantao.juridico.index'))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertRedirect(route('login'));
        $this->put(route('plantao.juridico.editar', $plantao->id))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.index'))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertRedirect(route('login'));
        $this->post(route('plantao.juridico.bloqueios.criar'))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertRedirect(route('login'));
        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id))->assertRedirect(route('login'));
        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.ajax'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $plantao = factory('App\PlantaoJuridico')->create();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

        $this->get(route('plantao.juridico.index'))->assertForbidden();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertForbidden();
        $this->put(route('plantao.juridico.editar', $plantao->id))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.index'))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertForbidden();
        $this->post(route('plantao.juridico.bloqueios.criar'))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertForbidden();
        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id))->assertForbidden();
        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.ajax'))->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $this->assertAuthenticated('web');
        
        $plantao = factory('App\PlantaoJuridico')->create();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantao->id
        ]);
        
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();
        $this->put(route('plantao.juridico.editar', $plantao->id), ['qtd_advogados' => 0])->assertStatus(302);
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['12:00']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)->assertStatus(302);
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();
        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)->assertStatus(302);
        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertStatus(302);
        // $this->get(route('plantao.juridico.bloqueios.ajax'))->assertOk();
    }
}
