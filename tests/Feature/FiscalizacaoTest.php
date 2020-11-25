<?php

namespace Tests\Feature;

use App\Permissao;
use Tests\TestCase;
use App\AnoFiscalizacao;
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

    /** @test 
     * 
     * Usuário sem autorização não pode listar anos de fiscalização.
    */
    public function non_authorized_users_cannot_list_ano_fiscalizacao()
    {
        $this->signIn();

        $this->get(route("fiscalizacao.index"))->assertForbidden();  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode criar ano de fiscalização.
    */
    public function non_authorized_users_cannot_create_ano_fiscalizacao()
    {
        $this->signIn();

        $atributos = factory("App\AnoFiscalizacao")->raw();

        $this->get(route("fiscalizacao.createano"))->assertForbidden();
        $this->post(route("fiscalizacao.storeano", $atributos))->assertForbidden();

        $this->assertDatabaseMissing("anos_fiscalizacao", ["ano" => $atributos["ano"]]);  
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar ano de fiscalização.
    */
    public function non_authorized_users_cannot_edit_ano_fiscalizacao()
    {
        $this->signIn();

        $anoFiscalizacao = factory("App\AnoFiscalizacao")->create();
        $dados = factory("App\DadoFiscalizacao")->create();
        $atributos = factory("App\DadoFiscalizacao")->raw(["notificacaopf" => 11111]);

        $this->get(route("fiscalizacao.editano", $anoFiscalizacao["ano"]))->assertForbidden();
        $this->post(route("fiscalizacao.updateano", $anoFiscalizacao["ano"]), $atributos)->assertForbidden();
            
        $this->assertNotEquals(DadoFiscalizacao::find($dados->id)->notificacaopf, $atributos["notificacaopf"]);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode publicar ano de fiscalização.
    */
    public function non_authorized_users_cannot_publish_ano_fiscalizacao()
    {
        $this->signIn();

        $anoFiscalizacao = factory("App\AnoFiscalizacao")->create();

        $this->post(route("fiscalizacao.updatestatus"), ["ano" => 2020, "status" => 1])->assertForbidden();
            
        $this->assertEquals(AnoFiscalizacao::find($anoFiscalizacao->ano)->status, 0);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode buscar ano de fiscalização.
    */
    public function non_authorized_users_cannot_search_ano_fiscalizacao()
    {
        $this->signIn();

        $anoFiscalizacao = factory("App\AnoFiscalizacao")->create(["ano" => 2020]);

        $this->get(route("fiscalizacao.busca", ["q" => "2020"]))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário com autorização pode listar anos de fiscalização.
    */
    public function authorized_users_can_list_ano_fiscalizacao()
    {
        $this->signInAsAdmin();

        $this->get(route("fiscalizacao.index"))->assertOk();  
    }

    /** @test 
     * 
     * Usuário com autorização pode criar ano de fiscalização.
    */
    public function authorized_users_can_create_ano_fiscalizacao()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\AnoFiscalizacao")->raw();

        $this->get(route("fiscalizacao.createano"))->assertOk();
        $this->post(route("fiscalizacao.storeano", $atributos));

        $this->assertDatabaseHas("anos_fiscalizacao", ["ano" => $atributos["ano"]]);  
        $this->assertEquals(DadoFiscalizacao::count(), 1);
    }

    /** @test 
     * 
     * Usuário com autorização pode editar ano de fiscalização.
    */
    public function authorized_users_can_edit_ano_fiscalizacao()
    {
        $this->signInAsAdmin();

        $anoAtributos = factory("App\AnoFiscalizacao")->raw();
        $this->post(route("fiscalizacao.storeano", $anoAtributos));
        $dadoAtributos = factory("App\DadoFiscalizacao")->raw(["notificacaopf" => 11111]);

        $this->get(route("fiscalizacao.editano", $anoAtributos["ano"]))->assertOk();
        $this->post(route("fiscalizacao.updateano", $anoAtributos["ano"]), ["regional" => [1 => $dadoAtributos]]);

        $this->assertEquals(DadoFiscalizacao::find(1)->notificacaopf, $dadoAtributos["notificacaopf"]);
    }

    /** @test 
     * 
     * Usuário com autorização pode publicar ano de fiscalização.
    */
    public function authorized_users_can_publish_ano_fiscalizacao()
    {
        $this->signInAsAdmin();

        $anoAtributos = factory("App\AnoFiscalizacao")->raw();
        $this->post(route("fiscalizacao.storeano", $anoAtributos));

        $this->post(route("fiscalizacao.updatestatus"), ["ano" => 2020, "status" => 1]);
            
        $this->assertEquals(AnoFiscalizacao::find($anoAtributos["ano"])->status, 1);
    }

    /** @test 
     * 
     * Usuário com autorização pode buscar ano de fiscalização.
    */
    public function authorized_users_can_search_ano_fiscalizacao()
    {
        $this->signInAsAdmin();

        $anoFiscalizacao = factory("App\AnoFiscalizacao")->create(["ano" => 2020]);

        $this->get(route("fiscalizacao.busca", ["q" => "2020"]))->assertSeeText($anoFiscalizacao->ano);
    }

    /** 
     * =======================================================================================================
     * TESTES DE REGRA DE NEGÓCIOS
     * =======================================================================================================
     */

    /** @test 
     * 
     * Sistema não deve permitir criação de anos repetidos.
    */
    public function cannot_create_duplicated_ano()
    {
        $this->signInAsAdmin();

        factory("App\AnoFiscalizacao")->create();

        $atributos = factory("App\AnoFiscalizacao")->raw();

        $this->post(route("fiscalizacao.storeano", $atributos))->assertSessionHasErrors("ano");

        $this->assertEquals(AnoFiscalizacao::count(), 1);
    }

    /** @test 
     * 
     * Sistema não deve permitir criação de anos com valores de ano inválido.
    */
    public function cannot_create_ano_with_invalid_ano()
    {
        $this->signInAsAdmin();

        $atributos = factory("App\AnoFiscalizacao")->raw(["ano" => 0]);

        $this->post(route("fiscalizacao.storeano", $atributos))->assertSessionHasErrors("ano");

        $this->assertEquals(AnoFiscalizacao::count(), 0);
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
     * Se nenhum ano estiver publicado, mapa deve ser aberto com o combobox desabilitado e
     * com o valor "Ano indisponível".
    */
    public function access_ano_mapas_from_portal_with_no_ano_published()
    {
        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("Ano indisponível");
    }

    /** @test 
     * 
     * Se algum ano estiver publicado, mapa deve ser aberto com o combobox habilitado e
     * com o valores dos anos publicados.
    */
    public function access_ano_mapas_from_portal_with_ano_published()
    {
        factory("App\AnoFiscalizacao")->create(["ano" => 2020, "status" => 1]);
        factory("App\AnoFiscalizacao")->create(["ano" => 2021, "status" => 1]);

        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("2020")
            ->assertSee("2021");
    }

    /** @test 
     * 
     * Anos publicados devem mostrar seus respectivos dados de fiscalização.
     * Página padrão sempre mostrar o maior ano.
    */
    public function access_ano_mapas_from_portal_with_dados()
    {
        factory("App\Regional")->create();
        factory("App\AnoFiscalizacao")->create(["ano" => 2020, "status" => 1]);
        factory("App\DadoFiscalizacao")->create(["notificacaopf" => 11111]);

        $this->get(route("fiscalizacao.mapa"))
            ->assertOk()
            ->assertSee("11111");
    }

    /** @test 
     * 
     * Múltiplos anos publicados devem mostrar seus respectivos dados de fiscalização.
    */
    public function access_ano_mapas_from_portal_with_multiple_anos_and_dados()
    {
        factory("App\Regional")->create();
        factory("App\AnoFiscalizacao")->create(["ano" => 2020, "status" => 1]);
        factory("App\DadoFiscalizacao")->create(["ano" => 2020, "notificacaopf" => 11111]);

        factory("App\AnoFiscalizacao")->create(["ano" => 2021, "status" => 1]);
        factory("App\DadoFiscalizacao")->create(["ano" => 2021, "notificacaopf" => 22222]);

        $this->get(route("fiscalizacao.mapaano", 2020))
            ->assertOk()
            ->assertSee("11111")
            ->assertDontSee("22222");

        $this->get(route("fiscalizacao.mapaano", 2021))
            ->assertOk()
            ->assertDontSee("11111")
            ->assertSee("22222");
    }
}