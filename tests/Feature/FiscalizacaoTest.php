<?php

namespace Tests\Feature;

use App\Permissao;
use Tests\TestCase;
use App\PeriodoFiscalizacao;
use App\DadoFiscalizacao;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FiscalizacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permissao::insert([
            [
                "controller" => "FiscalizacaoController",
                "metodo" => "index",
                "perfis" => "1,"
            ], [
                "controller" => "FiscalizacaoController",
                "metodo" => "create",
                "perfis" => "1,"
            ], [
                "controller" => "FiscalizacaoController",
                "metodo" => "edit",
                "perfis" => "1,"
            ]
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES DE AUTORIZAÇÃO NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $fiscal = factory('App\PeriodoFiscalizacao')->create();

        $this->get(route('fiscalizacao.index'))->assertRedirect(route('login'));
        $this->get(route('fiscalizacao.createperiodo'))->assertRedirect(route('login'));
        $this->get(route('fiscalizacao.editperiodo', $fiscal->id))->assertRedirect(route('login'));
        $this->get(route('fiscalizacao.busca'))->assertRedirect(route('login'));
        $this->post(route('fiscalizacao.storeperiodo'))->assertRedirect(route('login'));
        $this->post(route('fiscalizacao.updatestatus'))->assertRedirect(route('login'));
        $this->post(route('fiscalizacao.updateperiodo', $fiscal->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $fiscal = factory('App\PeriodoFiscalizacao')->create();

        $this->get(route('fiscalizacao.index'))->assertForbidden();
        $this->get(route('fiscalizacao.createperiodo'))->assertForbidden();
        $this->get(route('fiscalizacao.editperiodo', $fiscal->id))->assertForbidden();
        $this->get(route('fiscalizacao.busca'))->assertForbidden();

        $fiscal->periodo = '2021';
        $this->post(route('fiscalizacao.storeperiodo'), $fiscal->toArray())->assertForbidden();
        
        $this->post(route('fiscalizacao.updatestatus'))->assertForbidden();
        $this->post(route('fiscalizacao.updateperiodo', $fiscal->id))->assertForbidden();
    }
    
    /** @test 
     * 
     * Usuário sem autorização não pode listar periodos de fiscalização.
    */
    public function non_authorized_users_cannot_list_periodo_fiscalizacao()
    {
        $this->signIn();

        $this->get(route("fiscalizacao.index"))->assertForbidden();  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode criar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_create_periodo_fiscalizacao()
    {
        $this->signIn();

        $atributos = factory("App\PeriodoFiscalizacao")->raw();

        $this->get(route("fiscalizacao.createperiodo"))->assertForbidden();
        $this->post(route("fiscalizacao.storeperiodo", $atributos))->assertForbidden();

        $this->assertDatabaseMissing("periodos_fiscalizacao", ["periodo" => $atributos["periodo"]]);  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_edit_periodo_fiscalizacao()
    {
        $this->signIn();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();
        $dados = factory("App\DadoFiscalizacao")->create();
        $atributos = factory("App\DadoFiscalizacao")->raw(["notificacaopf" => 11111]);

        $this->get(route("fiscalizacao.editperiodo", $periodoFiscalizacao["id"]))->assertForbidden();
        $this->post(route("fiscalizacao.updateperiodo", $periodoFiscalizacao["id"]), $atributos)->assertForbidden();
            
        $this->assertNotEquals(DadoFiscalizacao::find($dados->id)->notificacaopf, $atributos["notificacaopf"]);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode publicar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_publish_periodo_fiscalizacao()
    {
        $this->signIn();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create();

        $this->post(route("fiscalizacao.updatestatus"), ["id" => $periodoFiscalizacao->id, "status" => 1])->assertForbidden();
            
        $this->assertEquals(PeriodoFiscalizacao::find($periodoFiscalizacao->id)->status, 0);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode buscar periodo de fiscalização.
    */
    public function non_authorized_users_cannot_search_periodo_fiscalizacao()
    {
        $this->signIn();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2020]);

        $this->get(route("fiscalizacao.busca", ["q" => "2020"]))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário com autorização pode listar periodos de fiscalização.
    */
    public function authorized_users_can_list_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $this->get(route("fiscalizacao.index"))->assertOk();  
    }

    /** @test 
     * 
     * Usuário com autorização pode criar periodo de fiscalização.
    */
    public function authorized_users_can_create_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\PeriodoFiscalizacao")->raw();

        $this->get(route("fiscalizacao.createperiodo"))->assertOk();
        $this->post(route("fiscalizacao.storeperiodo", $atributos));

        $this->assertDatabaseHas("periodos_fiscalizacao", ["periodo" => $atributos["periodo"]]);  
        $this->assertEquals(DadoFiscalizacao::count(), 1);
    }

    /** @test 
     * 
     * Usuário com autorização pode editar periodo de fiscalização.
    */
    public function authorized_users_can_edit_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2020]);
        $dadoFiscalizacao = factory("App\DadoFiscalizacao")->create(["idperiodo" => $periodoFiscalizacao->id, "idregional" => 1]);

        $dadoAtributos = factory("App\DadoFiscalizacao")->raw(["processofiscalizacaopf" => 11111]);

        $this->get(route("fiscalizacao.editperiodo", $periodoFiscalizacao->id))->assertOk();
        $this->post(route("fiscalizacao.updateperiodo", $periodoFiscalizacao->id), ["regional" => [1 => $dadoAtributos]]);

        $this->assertEquals(DadoFiscalizacao::find($dadoFiscalizacao->id)->processofiscalizacaopf, $dadoAtributos["processofiscalizacaopf"]);
    }

    /** @test 
     * 
     * Usuário com autorização pode publicar periodo de fiscalização.
    */
    public function authorized_users_can_publish_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2020]);

        $this->post(route("fiscalizacao.updatestatus"), ["idperiodo" => $periodoFiscalizacao->id , "status" => 1]);
            
        $this->assertEquals(PeriodoFiscalizacao::find($periodoFiscalizacao->id)->status, 1);
    }

    /** @test 
     * 
     * Usuário com autorização pode buscar periodo de fiscalização.
    */
    public function authorized_users_can_search_periodo_fiscalizacao()
    {
        $this->signInAsAdmin();

        $periodoFiscalizacao = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2020]);

        $this->get(route("fiscalizacao.busca", ["q" => "2020"]))->assertSeeText($periodoFiscalizacao->periodo);
    }

    /** 
     * =======================================================================================================
     * TESTES DE REGRA DE NEGÓCIOS
     * =======================================================================================================
     */

    /** @test 
     * 
     * Sistema não deve permitir criação de periodos repetidos.
    */
    public function cannot_create_duplicated_periodo()
    {
        $this->signInAsAdmin();

        factory("App\PeriodoFiscalizacao")->create();

        $atributos = factory("App\PeriodoFiscalizacao")->raw();

        $this->post(route("fiscalizacao.storeperiodo", $atributos))->assertSessionHasErrors("periodo");

        $this->assertEquals(PeriodoFiscalizacao::count(), 1);
    }

    /** @test 
     * 
     * Sistema não deve permitir criação de periodos com valores de periodo inválido.
    */
    public function cannot_create_periodo_with_invalid_periodo()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\PeriodoFiscalizacao")->raw(["periodo" => 0]);

        $this->post(route("fiscalizacao.storeperiodo", $atributos))->assertSessionHasErrors("periodo");

        $this->assertEquals(PeriodoFiscalizacao::count(), 0);
    }

    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test 
     * 
     * Testando acesso a página do mapa.
    */
    public function access_mapas_from_portal()
    {
        $this->get(route("fiscalizacao.mapa"))->assertOk();
    }

    /** @test 
     * 
     * Se nenhum periodo estiver publicado, mapa deve ser aberto com o combobox desabilitado e
     * com o valor "Indisponível".
    */
    public function access_periodo_mapas_from_portal_with_no_periodo_published()
    {
        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("Indisponível");
    }

    /** @test 
     * 
     * Se algum periodo estiver publicado, mapa deve ser aberto com o combobox habilitado e
     * com o valores dos periodos publicados.
    */
    public function access_periodo_mapas_from_portal_with_periodo_published()
    {
        $periodo2020 = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2020, "status" => 1]);
        $periodo2021 = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2021, "status" => 1]);

        factory("App\Regional")->create();
        factory("App\DadoFiscalizacao")->create(["idperiodo" => $periodo2020->id]);
        factory("App\DadoFiscalizacao")->create(["idperiodo" => $periodo2021->id]);

        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("2020")
            ->assertSee("2021");
    }

    /** @test 
     * 
     * periodos publicados devem mostrar seus respectivos dados de fiscalização.
     * Página padrão sempre mostrar o maior periodo.
    */
    public function access_periodo_mapas_from_portal_with_dados()
    {
        factory("App\Regional")->create();
        factory("App\PeriodoFiscalizacao")->create(["periodo" => 2020, "status" => 1]);
        factory("App\DadoFiscalizacao")->create(["processofiscalizacaopf" => 11111]);

        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("11111");
    }

    /** @test 
     * 
     * Múltiplos periodos publicados devem mostrar seus respectivos dados de fiscalização.
    */
    public function access_periodo_mapas_from_portal_with_multiple_periodos_and_dados()
    {
        $periodo2020 = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2020, "status" => 1]);
        $periodo2021 = factory("App\PeriodoFiscalizacao")->create(["periodo" => 2021, "status" => 1]);

        factory("App\Regional")->create();
        factory("App\DadoFiscalizacao")->create(["idperiodo" => $periodo2020->id, "processofiscalizacaopf" => 11111]);
        factory("App\DadoFiscalizacao")->create(["idperiodo" => $periodo2021->id, "processofiscalizacaopf" => 22222]);

        $this->get(route("fiscalizacao.mapaperiodo", $periodo2020->id))
            ->assertOk()
            ->assertSee("11111")
            ->assertDontSee("22222");

        $this->get(route("fiscalizacao.mapaperiodo", $periodo2021->id))
            ->assertOk()
            ->assertDontSee("11111")
            ->assertSee("22222");
    }
}