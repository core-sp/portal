<?php

namespace Tests\Feature;

use App\Permissao;
use App\BdoEmpresa;
use Tests\TestCase;
use App\BdoOportunidade;
use App\Mail\AnunciarVagaMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\AnunciarVagaRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BdoEmpresaTest extends TestCase
{
    use RefreshDatabase;

    protected $cnpjTeste;
    protected $cnpjInvalido;

    protected function setUp(): void
    {
        $this->cnpjTeste_alternativo = '15.456.496/0001-80';
        $this->cnpjInvalido = '00.000.000/0000-01';

        parent::setUp();
        Permissao::insert([
            [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ],
            [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'create',
                'perfis' => '1,'
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
        
        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $this->get(route('bdoempresas.lista'))->assertRedirect(route('login'));
        $this->get(route('bdoempresas.busca'))->assertRedirect(route('login'));
        $this->get(route('bdoempresas.create'))->assertRedirect(route('login'));
        $this->get(route('bdoempresas.edit', $bdoEmpresa->idempresa))->assertRedirect(route('login'));
        $this->put(route('bdoempresas.update', $bdoEmpresa->idempresa))->assertRedirect(route('login'));
        $this->post(route('bdoempresas.store'))->assertRedirect(route('login'));
        $this->delete(route('bdoempresas.destroy', $bdoEmpresa->idempresa))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdo2 = factory('App\BdoEmpresa')->raw([
            'cnpj' => '32173979000196'
        ]);

        $this->get(route('bdoempresas.lista'))->assertForbidden();
        $this->get(route('bdoempresas.busca'))->assertForbidden();
        $this->get(route('bdoempresas.create'))->assertForbidden();
        $this->get(route('bdoempresas.edit', $bdoEmpresa->idempresa))->assertForbidden();
        $this->put(route('bdoempresas.update', $bdoEmpresa->idempresa), $bdoEmpresa->toArray())->assertForbidden();
        $this->post(route('bdoempresas.store'), $bdo2)->assertForbidden();
        $this->delete(route('bdoempresas.destroy', $bdoEmpresa->idempresa))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode listar BdoEmpresa.
    */
    public function non_authorized_users_cannot_list_bdoempresa()
    {
        $this->signIn();

        $this->get(route('bdoempresas.lista'))->assertForbidden();

        $this->get(route('bdoempresas.busca'))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode criar BdoEmpresa.
    */
    public function non_authorized_users_cannot_create_bdoempresa()
    {
        $this->signIn();

        $this->get(route('bdoempresas.create'))->assertForbidden();

        $bdoEmpresa = factory('App\BdoEmpresa')->raw();

        $this->post(route('bdoempresas.store'), $bdoEmpresa)->assertForbidden();

        $this->assertDatabaseMissing('bdo_empresas', ['cnpj' => $bdoEmpresa['cnpj']]);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar BdoEmpresa. Atualizando endereço para 'Novo Endereço'.
    */
    public function non_authorized_users_cannot_update_bdoempresa()
    {
        $this->signIn();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoEmpresa->endereco = 'Novo Endereço';

        $this->get(route('bdoempresas.edit', $bdoEmpresa->idempresa))->assertForbidden();

        $this->put(route('bdoempresas.update', $bdoEmpresa->idempresa), $bdoEmpresa->toArray())->assertForbidden();

        $this->assertNotEquals(BdoEmpresa::find($bdoEmpresa->idempresa)->endereco, $bdoEmpresa->endereco);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode apagar BdoEmpresa.
    */
    public function non_authorized_users_cannot_delete_bdoempresa()
    {
        $this->signIn();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $this->delete(route('bdoempresas.destroy', $bdoEmpresa->idempresa))->assertForbidden();

        $this->assertNull(BdoEmpresa::find($bdoEmpresa->idempresa)->deleted_at);
    }

    /** @test 
     * 
     * Usuário com autorização pode listar BdoEmpresa.
    */
    public function authorized_users_can_list_bdoempresa()
    {
        $this->signInAsAdmin();

        $this->get(route('bdoempresas.lista'))->assertOk();

        $this->get(route('bdoempresas.busca'))->assertOk();
    }

    /** @test 
     * 
     * Usuário com autorização pode criar BdoEmpresa.
    */
    public function authorized_users_can_create_bdoempresa()
    {
        $this->signInAsAdmin();

        $this->get(route('bdoempresas.create'))->assertOk();

        $bdoEmpresa = factory('App\BdoEmpresa')->raw();

        $this->post(route('bdoempresas.store'), $bdoEmpresa);

        $this->assertDatabaseHas('bdo_empresas', ['cnpj' => $bdoEmpresa['cnpj']]);
    }

    /** @test 
     * 
     * Usuário com autorização pode editar BdoEmpresa. Atualizando endereço para 'Novo Endereço'.
    */
    public function authorized_users_can_update_bdoempresa()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoEmpresa->endereco = 'Novo Endereço';

        $this->get(route('bdoempresas.edit', $bdoEmpresa->idempresa))->assertOk();

        $this->put(route('bdoempresas.update', $bdoEmpresa->idempresa), $bdoEmpresa->toArray());

        $this->assertEquals(BdoEmpresa::find($bdoEmpresa->idempresa)->endereco, $bdoEmpresa->endereco);
    }

    /** @test 
     * 
     * Usuário com autorização pode apagar BdoEmpresa.
    */
    public function authorized_users_can_delete_bdoempresa()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $this->delete(route('bdoempresas.destroy', $bdoEmpresa->idempresa));

        $this->assertSoftDeleted('bdo_empresas', ['idempresa' => $bdoEmpresa->idempresa]);
    }


    /** 
     * =======================================================================================================
     * TESTES DE INPUT NO ADMIN
     * =======================================================================================================
     */

    /** @test 
     * 
     * BdoEmpresa com CNPJ repetido não pode ser criada.
    */
    public function bdoempresa_with_same_cnpj_cannot_be_created()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoEmpresaRepedida = factory('App\BdoEmpresa')->raw();

        $this->post(route('bdoempresas.store'), $bdoEmpresaRepedida)->assertSessionHasErrorsIn('default', ['cnpj']);

        $this->assertEquals(BdoEmpresa::where('cnpj', $bdoEmpresa->cnpj)->count(), 1);
    }

    /** @test 
     * 
     * BdoEmpresa com CNPJ inválido não pode ser criada.
    */
    public function bdoempresa_with_invalid_cnpj_cannot_be_created()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->raw(['cnpj' => $this->cnpjInvalido]);

        $this->post(route('bdoempresas.store'), $bdoEmpresa)->assertSessionHasErrorsIn('default', ['cnpj']);

        $this->assertEquals(BdoEmpresa::count(), 0);
    }

    /** @test 
     * 
     * BdoEmpresa com campos obrigatórios faltando não pode ser criada.
     * (CNPJ, Razão Social, Endereço, Descrição, Email, Telefone)
    */
    public function bdoempresa_missing_mandatory_input_cannot_be_created()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->raw([
            'cnpj' => null,
            'razaosocial' => null,
            'endereco' => null,
            'descricao' => null,
            'email' => null,
            'telefone' => null
        ]);

        $this->post(route('bdoempresas.store'), $bdoEmpresa)->assertSessionHasErrorsIn('default', [
            'cnpj', 
            'razaosocial',
            'endereco',
            'descricao',
            'email',
            'telefone'
        ]);           

        $this->assertEquals(BdoEmpresa::count(), 0);
    }

    /** @test 
     * 
     * BdoEmpresa com CNPJ repetido não pode ser atualizada.
    */
    public function bdoempresa_with_same_cnpj_cannot_be_updated()
    {
        $this->signInAsAdmin();

        $bdoEmpresa_1 = factory('App\BdoEmpresa')->create();
        $bdoEmpresa_2 = factory('App\BdoEmpresa')->create(['cnpj' => $this->cnpjTeste_alternativo]);
        $bdoEmpresa_2->cnpj = $bdoEmpresa_1->cnpj;
        
        $this->put(route('bdoempresas.update', $bdoEmpresa_2->idempresa), $bdoEmpresa_2->toArray())->assertSessionHasErrorsIn('default', ['cnpj']);

        $this->assertEquals(BdoEmpresa::where('cnpj', $bdoEmpresa_1->cnpj)->count(), 1);
    }

    /** @test 
     * 
     * BdoEmpresa com CNPJ inválido não pode ser atualizada.
    */
    public function bdoempresa_with_invalid_cnpj_cannot_be_updated()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoEmpresa->cnpj = $this->cnpjInvalido;

        $this->put(route('bdoempresas.update', $bdoEmpresa->idempresa), $bdoEmpresa->toArray())->assertSessionHasErrorsIn('default', ['cnpj']);

        $this->assertEquals(BdoEmpresa::where('cnpj', $this->cnpjInvalido)->count(), 0);
    }

    /** @test 
     * 
     * BdoEmpresa com campos obrigatórios faltando não pode ser atualizada.
     * (CNPJ, Razão Social, Endereço, Descrição, Email, Telefone)
    */
    public function bdoempresa_missing_mandatory_input_cannot_be_updated()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoEmpresa->cnpj = null;
        $bdoEmpresa->razaosocial = null;
        $bdoEmpresa->endereco = null;
        $bdoEmpresa->descricao = null;
        $bdoEmpresa->email = null;
        $bdoEmpresa->telefone = null;

        $this->put(route('bdoempresas.update', $bdoEmpresa->idempresa), $bdoEmpresa->toArray())->assertSessionHasErrorsIn('default', [
            'cnpj', 
            'razaosocial',
            'endereco',
            'descricao',
            'email',
            'telefone'
        ]);

        $this->assertEquals(BdoEmpresa::where('cnpj', null)->count(), 0);
    }

    /** @test 
     * 
     * Teste nos critérios de busca de BdoEmpresa.
     * (segmento, razaosocial, cnpj)
    */
    public function search_criteria_for_bdoempresa()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $this->get(route('bdoempresas.busca', ['q' => $bdoEmpresa->razaosocial]))
            ->assertSeeText($bdoEmpresa->razaosocial);

        $this->get(route('bdoempresas.busca', ['q' => $bdoEmpresa->segmento]))
            ->assertSeeText($bdoEmpresa->razaosocial);

        $this->get(route('bdoempresas.busca', ['q' => $bdoEmpresa->cnpj]))
            ->assertSeeText($bdoEmpresa->razaosocial);
    }


    /** 
     * =======================================================================================================
     * TESTES DE REGRA DE NEGÓCIOS
     * =======================================================================================================
     */

    /** @test 
     * 
     * BdoEmpresa com oportunidades não pode ser apagada.
    */
    public function bdoempresa_with_bdooportunidade_cannot_be_deleted()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoOportunidade = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa]);

        $this->delete(route('bdoempresas.destroy', $bdoEmpresa->idempresa));

        $this->assertNull(BdoEmpresa::find($bdoEmpresa->idempresa)->deleted_at);
    }


    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test 
     * 
     * Teste da API "apiGetEmpresa" usada pelo Ajax na página para anunciar de vagas.
     * Dois comportamentos: se BdoEmpresa não possuir BdoOportunidade aberta, resposta possui 'alert-success',
     * se BdoEmpresa possuir BdoOportunidade aberta, resposta possui 'alert-warning'
    */
    public function retrieve_bdoempresa_by_api()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $this->get(route('bdosite.apiGetEmpresa', apenasNumeros($bdoEmpresa->cnpj)))
            ->assertOk()
            ->assertSeeText('alert-success');

        $bdoOportunidade = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa]);

        $this->get(route('bdosite.apiGetEmpresa', apenasNumeros($bdoEmpresa->cnpj)))
            ->assertOk()
            ->assertSeeText('alert-warning');
    }

    /** @test 
     * 
     * Criação de BdoEmpresa pelo Portal (página para anunciar vagas). 
     * Criação de BdoEmpresa pelo Portal sempre cria também uma BdoOportunidade.
     * Esse teste cobre de criação de ambas as entidades juntas (BdoEmpresa e BdoOportunidade).
    */
    public function create_bdoempresa_on_portal()
    {
        Mail::fake();

        $this->get(route('bdosite.anunciarVagaView'))->assertOk();

        $bdoEmpresa = factory('App\BdoEmpresa')->raw();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw();

        $anunciarVaga['idempresa'] = "0";
        $anunciarVaga['segmento'] = $bdoEmpresa['segmento'];
        $anunciarVaga['cnpj'] = $bdoEmpresa['cnpj'];
        $anunciarVaga['razaosocial'] = $bdoEmpresa['razaosocial'];
        $anunciarVaga['fantasia'] = $bdoEmpresa['fantasia'];
        $anunciarVaga['capitalsocial'] = $bdoEmpresa['capitalsocial'];
        $anunciarVaga['endereco'] = $bdoEmpresa['endereco'];
        $anunciarVaga['site'] = $bdoEmpresa['site'];
        $anunciarVaga['email'] = $bdoEmpresa['email'];
        $anunciarVaga['telefone'] = $bdoEmpresa['telefone'];
        $anunciarVaga['descricao'] = $bdoEmpresa['descricao'];
        $anunciarVaga['contatonome'] = $bdoEmpresa['contatonome'];
        $anunciarVaga['contatotelefone'] = $bdoEmpresa['contatotelefone'];
        $anunciarVaga['contatoemail'] = $bdoEmpresa['contatoemail'];
        $anunciarVaga['idusuario'] = $bdoEmpresa['idusuario'];

        $anunciarVaga['titulo'] = $bdoOportunidade['titulo'];
        $anunciarVaga['segmentoOportunidade'] = $bdoOportunidade['segmento'];
        $anunciarVaga['regiaoAtuacao'] = explode(',', trim($bdoOportunidade['regiaoatuacao'], ','));
        $anunciarVaga['descricaoOportunidade'] = $bdoOportunidade['descricao'];
        $anunciarVaga['nrVagas'] = $bdoOportunidade['vagasdisponiveis'];
        $anunciarVaga['termo'] = 'on';

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga);

        $this->assertDatabaseHas('bdo_empresas', ['cnpj' => $bdoEmpresa['cnpj']]);

        $this->assertDatabaseHas('bdo_oportunidades', ['titulo' => $bdoOportunidade['titulo']]);

        Mail::assertQueued(AnunciarVagaMail::class);
    }

    /** @test 
     * 
     * Criação de BdoEmpresa pelo Portal (página para anunciar vagas). 
     * Criação de BdoEmpresa pelo Portal sempre cria também uma BdoOportunidade.
     * Dois possíveis comportamentos: caso nenhum CNPJ seja fornecido, uma mensagem de erro é retornada,
     * caso um CNPJ que não esteja cadastrado no Portal seja fornecido, o valor "0" é atribuído ao
     * "idempresa" e a validação das outras entradas é verificada.
     * Esse teste cobre verificação de campos obrigatórios para criação de BdoOportunidade.
    */
    public function missing_mandatory_input_on_portal_cannot_create_bdoempresa()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->raw();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw();

        $anunciarVaga = [];

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga)->assertSessionHasErrorsIn('default', ['idempresa']);

        $anunciarVaga['idempresa'] = "0";

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga)->assertSessionHasErrorsIn('default', [
            'razaosocial',
            'fantasia',
            'cnpj',
            'segmento',
            'endereco',
            'site', 
            'email',
            'titulo',
            'segmentoOportunidade',
            'nrVagas',
            'regiaoAtuacao',
            'descricaoOportunidade',
            'contatonome',
            'contatoemail'
        ]);

        $this->assertEquals(BdoEmpresa::count(), 0);

        $this->assertEquals(BdoOportunidade::count(), 0);

    }
}