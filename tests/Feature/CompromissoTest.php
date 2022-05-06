<?php

namespace Tests\Feature;

use App\Permissao;
use App\Compromisso;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompromissoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES DE AUTORIZAÇÃO NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $compromisso = factory('App\Compromisso')->create();

        $this->get(route('compromisso.index'))->assertRedirect(route('login'));
        $this->get(route('compromisso.create'))->assertRedirect(route('login'));
        $this->get(route('compromisso.edit', $compromisso->id))->assertRedirect(route('login'));
        $this->get(route('compromisso.busca'))->assertRedirect(route('login'));
        $this->get(route('compromisso.filtro'))->assertRedirect(route('login'));
        $this->post(route('compromisso.store'))->assertRedirect(route('login'));
        $this->post(route('compromisso.update', $compromisso->id))->assertRedirect(route('login'));
        $this->delete(route('compromisso.destroy', $compromisso->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $compromisso = factory('App\Compromisso')->create();

        $this->get(route('compromisso.index'))->assertForbidden();
        $this->get(route('compromisso.create'))->assertForbidden();
        $this->get(route('compromisso.edit', $compromisso->id))->assertForbidden();
        $this->get(route('compromisso.busca'))->assertForbidden();
        $this->get(route('compromisso.filtro'))->assertForbidden();

        $dados = factory('App\Compromisso')->raw([
            'data' => '20/01/2022'
        ]);
        $this->post(route('compromisso.store'), $dados)->assertForbidden();
        $this->post(route('compromisso.update', $compromisso->id), $dados)->assertForbidden();
        $this->delete(route('compromisso.destroy', $compromisso->id))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode listar crompromisso.
    */
    public function non_authorized_users_cannot_list_compromisso()
    {
        $this->signIn();

        $this->get(route("compromisso.index"))->assertForbidden();  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode criar compromisso.
    */
    public function non_authorized_users_cannot_create_compromisso()
    {
        $this->signIn();

        $atributos = factory("App\Compromisso")->raw(['data' => date('d\/m\/Y')]);

        $this->get(route("compromisso.create"))->assertForbidden();
        $this->post(route("compromisso.store", $atributos))->assertForbidden();

        $this->assertDatabaseMissing("compromissos", ["titulo" => $atributos["titulo"]]);  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode excluir compromisso.
    */
    public function non_authorized_users_cannot_delete_compromisso()
    {
        $this->signIn();

        $compromisso = factory("App\Compromisso")->create();

        $this->delete(route("compromisso.destroy", $compromisso["id"]))->assertForbidden();

        $this->assertDatabaseHas("compromissos", ["titulo" => $compromisso["titulo"]]);  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar compromisso.
    */
    public function non_authorized_users_cannot_edit_compromisso()
    {
        $this->signIn();

        $compromisso = factory("App\Compromisso")->create();
        $compromisso->titulo = "novo titulo";
        $compromisso->data = date('d\/m\/Y');
        $this->get(route("compromisso.edit", $compromisso["id"]))->assertForbidden();
        $this->post(route("compromisso.update", $compromisso["id"]), $compromisso->ToArray())->assertForbidden();
        
        $this->assertNotEquals(Compromisso::find($compromisso->id)->titulo, $compromisso["titulo"]);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode buscar compromisso.
    */
    public function non_authorized_users_cannot_search_compromisso()
    {
        $this->signIn();

        $compromisso = factory("App\Compromisso")->create();

        $this->get(route("compromisso.busca", ["q" => "compromisso"]))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode filtrar compromisso.
    */
    public function non_authorized_users_cannot_filter_compromisso()
    {
        $this->signIn();

        $compromisso = factory("App\Compromisso")->create();

        $this->get(route("compromisso.filtro", ["q" => date('d\/m\/Y')]))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário com autorização pode listar compromisso.
    */
    public function authorized_users_can_list_compromisso()
    {
        $this->signInAsAdmin();

        $this->get(route("compromisso.index"))->assertOk();  
    }

    /** @test 
     * 
     * Usuário com autorização pode criar compromisso.
    */
    public function authorized_users_can_create_compromisso()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\Compromisso")->raw(['data' => date('d\/m\/Y')]);

        $this->get(route("compromisso.create"))->assertOk();
        $this->post(route("compromisso.store", $atributos))->assertStatus(302);

        $this->assertDatabaseHas("compromissos", ["titulo" => $atributos["titulo"]]);
    }

    /** @test 
     * 
     * Usuário com autorização pode criar compromisso.
    */
    public function authorized_users_cannot_create_compromisso_field_empty()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\Compromisso")->raw([
            'titulo' => null,
            'descricao' => null,
            'local' => null,
            'data' => null,
            'horarioinicio' => null,
            'horariotermino' => null
        ]);

        $this->post(route('compromisso.store'), $atributos)->assertSessionHasErrors([
            'titulo',
            'descricao',
            'local',
            'data',
            'horarioinicio',
            'horariotermino'
        ]);

        $this->assertEquals(Compromisso::count(), 0);
    }

    /** @test 
     * 
     * Usuário com autorização pode deletar compromisso.
    */
    public function authorized_users_can_delete_compromisso()
    {
        $this->signInAsAdmin();

        $compromisso = factory("App\Compromisso")->create();

        $this->delete(route("compromisso.destroy", $compromisso["id"]))->assertStatus(302);

        $this->assertDatabaseMissing("compromissos", ["titulo" => $compromisso["titulo"]]);  
    }

    /** @test 
     * 
     * Usuário com autorização pode editar compromisso.
    */
    public function authorized_users_can_edit_compromisso()
    {
        $this->signInAsAdmin();

        $compromisso = factory("App\Compromisso")->create();

        $compromisso->titulo = "novo titulo";
        $compromisso->data = date('d\/m\/Y');

        $this->get(route("compromisso.edit", $compromisso["id"]))->assertOk();
        $this->post(route("compromisso.update", $compromisso["id"]), $compromisso->attributesToArray())->assertStatus(302);
    
        $this->assertEquals(Compromisso::find($compromisso->id)->titulo, $compromisso["titulo"]);
    }

    /** @test 
     * 
     * Usuário com autorização pode buscar compromisso.
    */
    public function authorized_users_can_search_compromisso()
    {
        $this->signInAsAdmin();

        $compromisso = factory("App\Compromisso")->create();

        $this->get(route("compromisso.busca", ["q" => "compromisso"]))->assertOk();
    }

    /** 
     * =======================================================================================================
     * TESTES DE REGRA DE NEGÓCIOS
     * =======================================================================================================
     */

    /** @test 
     * 
     * Sistema não deve permitir criação de compromisso com data inválida.
    */
    public function cannot_create_compromisso_with_invalid_date()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\Compromisso")->raw(["data" => '20-20-20']);

        $this->post(route("compromisso.store", $atributos))->assertSessionHasErrors("data");

        $this->assertEquals(Compromisso::count(), 0);
    }

    /** @test 
     * 
     * Sistema não deve permitir criação de compromissos com horario inválido.
    */
    public function cannot_create_compromisso_with_invalid_hour()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\Compromisso")->raw(["horarioinicio" => '26:98', 'horariotermino' => '28:89']);

        $this->post(route("compromisso.store", $atributos))->assertSessionHasErrors("horarioinicio", "horariotermino");

        $this->assertEquals(Compromisso::count(), 0);
    }

    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

   
    /** @test 
     * 
     * O sistema não pode permitir acessar o compromisso com data inválida.
    */
    public function access_compromisso_with_invalid_date()
    {
        $atributos = factory("App\Compromisso")->create(["data" => date('Y-m-d')]);

        $this->get(route("agenda-institucional-data", '20-07-2'))->assertStatus(404);
    }

    /** @test 
     * 
     * Usuário com autorização pode filtrar o compromisso.
    */
    public function authorized_users_can_filter_compromisso()
    {
        $this->signInAsAdmin();

        $compromisso = factory("App\Compromisso")->create();

        $this->get(route("compromisso.filtro", ["q" => "21-07-2021"]))->assertOk();
    }
}