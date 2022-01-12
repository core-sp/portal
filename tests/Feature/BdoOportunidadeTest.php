<?php

namespace Tests\Feature;

use App\Permissao;
use App\BdoEmpresa;
use Tests\TestCase;
use App\BdoOportunidade;
use App\Mail\AnunciarVagaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BdoOportunidadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permissao::insert([
            [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'destroy',
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
        $bdoEmpresa = factory('App\BdoEmpresa')->create();
        $bdoOportunidade = factory('App\BdoOportunidade')->create();

        $this->get(route('bdooportunidades.lista'))->assertRedirect(route('login'));
        $this->get(route('bdooportunidades.busca'))->assertRedirect(route('login'));
        $this->get(route('bdooportunidades.create', $bdoEmpresa->idempresa))->assertRedirect(route('login'));
        $this->get(route('bdooportunidades.edit', $bdoOportunidade->idoportunidade))->assertRedirect(route('login'));
        $this->put(route('bdooportunidades.update', $bdoOportunidade->idoportunidade))->assertRedirect(route('login'));
        $this->post(route('bdooportunidades.store'))->assertRedirect(route('login'));
        $this->delete(route('bdooportunidades.destroy', $bdoOportunidade->idoportunidade))->assertRedirect(route('login'));
    }

    /** @test 
     * 
     * Usuário sem autorização não pode listar BdoOportunidade.
    */
    public function non_authorized_users_cannot_list_bdooportunidade()
    {
        $this->signIn();

        $this->get(route('bdooportunidades.lista'))->assertForbidden();

        $this->get(route('bdooportunidades.busca'))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode criar BdoOportunidade.
    */
    public function non_authorized_users_cannot_create_bdooportunidade()
    {
        $this->signIn();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->raw(['idempresa' => $bdoEmpresa->idempresa]);
        $bdoOportunidade['regiaoatuacao'] = explode(',', trim($bdoOportunidade['regiaoatuacao'], ','));

        $this->get(route('bdooportunidades.create', $bdoEmpresa->idempresa))->assertForbidden();

        $this->post(route('bdooportunidades.store'), $bdoOportunidade)->assertForbidden();

        $this->assertDatabaseMissing('bdo_oportunidades', ['titulo' => $bdoOportunidade['titulo']]);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar BdoOportunidade. Atualizando título para 'Novo Título'.
    */
    public function non_authorized_users_cannot_update_bdooportunidade()
    {
        $user = $this->signIn();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa]);
        $bdoOportunidade->titulo = 'Novo Título';
        $bdoOportunidade->regiaoatuacao = explode(',', trim($bdoOportunidade->regiaoatuacao, ','));

        $this->get(route('bdooportunidades.edit', $bdoEmpresa->idempresa))->assertForbidden();

        $this->put(route('bdooportunidades.update', $bdoEmpresa->idempresa), $bdoOportunidade->toArray())->assertForbidden();

        $this->assertNotEquals(BdoOportunidade::find($bdoOportunidade->idoportunidade)->titulo, $bdoOportunidade->titulo);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode apagar BdoOportunidade.
    */
    public function non_authorized_users_cannot_delete_bdooportunidade()
    {
        $this->signIn();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa]);

        $this->delete(route('bdooportunidades.destroy', $bdoOportunidade->idoportunidade))->assertForbidden();

        $this->assertNull(BdoOportunidade::find($bdoOportunidade->idoportunidade)->deleted_at);
    }

    /** @test 
     * 
     * Usuário com autorização pode listar BdoOportunidade.
    */
    public function authorized_users_can_list_bdooportunidade()
    {
        $this->signInAsAdmin();

        $this->get(route('bdooportunidades.lista'))->assertOk();

        $this->get(route('bdooportunidades.busca'))->assertOk();
    }

    /** @test 
     * 
     * Usuário com autorização pode criar BdoOportunidade.
    */
    public function authorized_users_can_create_bdooportunidade()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->raw(['idempresa' => $bdoEmpresa->idempresa]);
        $bdoOportunidade['regiaoatuacao'] = explode(',', trim($bdoOportunidade['regiaoatuacao'], ','));

        $this->get(route('bdooportunidades.create', $bdoEmpresa->idempresa))->assertOk();

        $this->post(route('bdooportunidades.store'), $bdoOportunidade);

        $this->assertDatabaseHas('bdo_oportunidades', ['titulo' => $bdoOportunidade['titulo']]);
    }

    /** @test 
     * 
     * Usuário com autorização pode editar BdoOportunidade. Atualizando título para 'Novo Título'.
    */
    public function authorized_users_can_update_bdooportunidade()
    {
        $user = $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa]);
        $bdoOportunidade->titulo = 'Novo Título';
        $bdoOportunidade->regiaoatuacao = explode(',', trim($bdoOportunidade->regiaoatuacao, ','));

        $this->get(route('bdooportunidades.edit', $bdoEmpresa->idempresa))->assertOk();

        $this->put(route('bdooportunidades.update', $bdoEmpresa->idempresa), $bdoOportunidade->toArray());

        $this->assertEquals(BdoOportunidade::find($bdoOportunidade->idoportunidade)->titulo, $bdoOportunidade->titulo);
    }

    /** @test 
     * 
     * Usuário com autorização pode apagar BdoOportunidade.
    */
    public function authorized_users_can_delete_bdooportunidade()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa]);

        $this->delete(route('bdooportunidades.destroy', $bdoOportunidade->idoportunidade));

        $this->assertSoftDeleted('bdo_oportunidades', ['idoportunidade' => $bdoOportunidade->idoportunidade]);
    }


    /** 
     * =======================================================================================================
     * TESTES DE INPUT NO ADMIN
     * =======================================================================================================
     */

    /** @test 
     * 
     * BdoOportunidade com campos obrigatórios faltando não pode ser criada. BdoOportunidade sempre 
     * é criada a partir de uma BdoEmpresa.
     * (Título, Vagas disponiveis, Descrição, Região atuação)
    */
    public function bdooportunidade_missing_mandatory_input_cannot_be_created()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->raw([
            'idempresa' => $bdoEmpresa->idempresa,
            'titulo' => null,
            'vagasdisponiveis' => null,
            'descricao' => null,
            'regiaoatuacao' => null
        ]);

        $this->post(route('bdooportunidades.store'), $bdoOportunidade)->assertSessionHasErrorsIn('default', [
            'titulo', 
            'vagasdisponiveis',
            'descricao',
            'regiaoatuacao',
        ]);           

        $this->assertEquals(BdoOportunidade::count(), 0);
    }

    /** @test 
     * 
     * Teste nos critérios de busca de BdoOportunidade.
     * (descricao, status)
    */
    public function search_criteria_for_bdooportunidade()
    {
        $this->signInAsAdmin();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa]);

        $this->get(route('bdooportunidades.busca', ['q' => $bdoOportunidade->descricao]))
            ->assertSeeText($bdoEmpresa->razaosocial);

        $this->get(route('bdooportunidades.busca', ['q' => $bdoOportunidade->status]))
            ->assertSeeText($bdoEmpresa->razaosocial);
    }


    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     * 
     * Obs: Teste de criação de BdoOportunidade junto com BdoEmpresa se encontra nos testes de BdoEmpresa.
     * Teste de verificação de campos obrigatórios para criação de BdoOportunidade também se encontra,
     * nos testes de BdoEmpresa.
     */


    /** @test 
     * 
     * Criação de BdoOportunidade pelo Portal (página para anunciar vagas). 
     * Cenário onde uma nova BdoOportunidade está sendo criada em cima de uma BdoEmpresa existente. Esta
     * BdoEmpresa não possui nenhuma BdoOportunidades, portanto a BdoOportunidade pode ser criada.
    */
    public function create_bdooportunidade_on_portal_with_existing_bdoempresa_with_no_bdooportunidade()
    {
        Mail::fake();

        $this->get(route('bdosite.anunciarVagaView'))->assertOk();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade = factory('App\BdoOportunidade')->raw(['idempresa' => $bdoEmpresa->idempresa]);

        $anunciarVaga['idempresa'] = $bdoEmpresa->idempresa;
        $anunciarVaga['segmento'] = $bdoEmpresa->segmento;
        $anunciarVaga['cnpj'] = $bdoEmpresa->cnpj;
        $anunciarVaga['razaosocial'] = $bdoEmpresa->razaosocial;
        $anunciarVaga['fantasia'] = $bdoEmpresa->fantasia;
        $anunciarVaga['capitalsocial'] = $bdoEmpresa->capitalsocial;
        $anunciarVaga['endereco'] = $bdoEmpresa->endereco;
        $anunciarVaga['site'] = $bdoEmpresa->site;
        $anunciarVaga['email'] = $bdoEmpresa->email;
        $anunciarVaga['telefone'] = $bdoEmpresa->telefone;
        $anunciarVaga['descricao'] = $bdoEmpresa->descricao;
        $anunciarVaga['contatonome'] = $bdoEmpresa->contatonome;
        $anunciarVaga['contatotelefone'] = $bdoEmpresa->contatotelefone;
        $anunciarVaga['contatoemail'] = $bdoEmpresa->contatoemail;
        $anunciarVaga['idusuario'] = $bdoEmpresa->idusuario;

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
     * Criação de BdoOportunidade pelo Portal (página para anunciar vagas). 
     * Cenário onde uma nova BdoOportunidade está sendo criada em cima de uma BdoEmpresa existente. Esta
     * BdoEmpresa não possui BdoOportunidades abertas (Em andamento ou Sob Análise), portanto a BdoOportunidade
     * pode ser criada.
    */
    public function create_bdooportunidade_on_portal_with_existing_bdoempresa_with_no_open_bdooportunidade()
    {
        Mail::fake();

        $this->get(route('bdosite.anunciarVagaView'))->assertOk();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade_1 = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa, 'status' => BdoOportunidade::STATUS_CONCLUIDO]);

        $bdoOportunidade_2 = factory('App\BdoOportunidade')->raw(['idempresa' => $bdoEmpresa->idempresa]);

        $anunciarVaga['idempresa'] = $bdoEmpresa->idempresa;
        $anunciarVaga['segmento'] = $bdoEmpresa->segmento;
        $anunciarVaga['cnpj'] = $bdoEmpresa->cnpj;
        $anunciarVaga['razaosocial'] = $bdoEmpresa->razaosocial;
        $anunciarVaga['fantasia'] = $bdoEmpresa->fantasia;
        $anunciarVaga['capitalsocial'] = $bdoEmpresa->capitalsocial;
        $anunciarVaga['endereco'] = $bdoEmpresa->endereco;
        $anunciarVaga['site'] = $bdoEmpresa->site;
        $anunciarVaga['email'] = $bdoEmpresa->email;
        $anunciarVaga['telefone'] = $bdoEmpresa->telefone;
        $anunciarVaga['descricao'] = $bdoEmpresa->descricao;
        $anunciarVaga['contatonome'] = $bdoEmpresa->contatonome;
        $anunciarVaga['contatotelefone'] = $bdoEmpresa->contatotelefone;
        $anunciarVaga['contatoemail'] = $bdoEmpresa->contatoemail;
        $anunciarVaga['idusuario'] = $bdoEmpresa->idusuario;

        $anunciarVaga['titulo'] = $bdoOportunidade_2['titulo'];
        $anunciarVaga['segmentoOportunidade'] = $bdoOportunidade_2['segmento'];
        $anunciarVaga['regiaoAtuacao'] = explode(',', trim($bdoOportunidade_2['regiaoatuacao'], ','));
        $anunciarVaga['descricaoOportunidade'] = $bdoOportunidade_2['descricao'];
        $anunciarVaga['nrVagas'] = $bdoOportunidade_2['vagasdisponiveis'];
        $anunciarVaga['termo'] = 'on';

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga);

        $this->assertDatabaseHas('bdo_oportunidades', ['titulo' => $bdoOportunidade_2['titulo']]);

        Mail::assertQueued(AnunciarVagaMail::class);
    }

    /** @test 
     * 
     * Criação de BdoOportunidade pelo Portal (página para anunciar vagas). 
     * Cenário onde uma nova BdoOportunidade está sendo criada em cima de uma BdoEmpresa existente. Esta
     * BdoEmpresa possui BdoOportunidades abertas (Em andamento ou Sob Análise), portanto a BdoOportunidade
     * não pode ser criada.
    */
    public function create_bdooportunidade_on_portal_with_existing_bdoempresa_with_open_bdooportunidade()
    {
        Mail::fake();

        $this->get(route('bdosite.anunciarVagaView'))->assertOk();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade_1 = factory('App\BdoOportunidade')->create(['idempresa' => $bdoEmpresa->idempresa, 'status' => BdoOportunidade::STATUS_SOB_ANALISE]);

        $bdoOportunidade_2 = factory('App\BdoOportunidade')->raw(['idempresa' => $bdoEmpresa->idempresa]);

        $anunciarVaga['idempresa'] = $bdoEmpresa->idempresa;
        $anunciarVaga['segmento'] = $bdoEmpresa->segmento;
        $anunciarVaga['cnpj'] = $bdoEmpresa->cnpj;
        $anunciarVaga['razaosocial'] = $bdoEmpresa->razaosocial;
        $anunciarVaga['fantasia'] = $bdoEmpresa->fantasia;
        $anunciarVaga['capitalsocial'] = $bdoEmpresa->capitalsocial;
        $anunciarVaga['endereco'] = $bdoEmpresa->endereco;
        $anunciarVaga['site'] = $bdoEmpresa->site;
        $anunciarVaga['email'] = $bdoEmpresa->email;
        $anunciarVaga['telefone'] = $bdoEmpresa->telefone;
        $anunciarVaga['descricao'] = $bdoEmpresa->descricao;
        $anunciarVaga['contatonome'] = $bdoEmpresa->contatonome;
        $anunciarVaga['contatotelefone'] = $bdoEmpresa->contatotelefone;
        $anunciarVaga['contatoemail'] = $bdoEmpresa->contatoemail;
        $anunciarVaga['idusuario'] = $bdoEmpresa->idusuario;

        $anunciarVaga['titulo'] = $bdoOportunidade_2['titulo'];
        $anunciarVaga['segmentoOportunidade'] = $bdoOportunidade_2['segmento'];
        $anunciarVaga['regiaoAtuacao'] = explode(',', trim($bdoOportunidade_2['regiaoatuacao'], ','));
        $anunciarVaga['descricaoOportunidade'] = $bdoOportunidade_2['descricao'];
        $anunciarVaga['nrVagas'] = $bdoOportunidade_2['vagasdisponiveis'];

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga);

        $this->assertEquals(BdoOportunidade::where('titulo', $bdoOportunidade_2['titulo'])->count(), 0);

        $bdoOportunidade_1->update(['status' => BdoOportunidade::STATUS_EM_ANDAMENTO]);

        $this->post(route('bdosite.anunciarVaga'), $anunciarVaga);

        $this->assertEquals(BdoOportunidade::where('titulo', $bdoOportunidade_2['titulo'])->count(), 0);

        Mail::assertNotQueued(AnunciarVagaMail::class);
    }

    /** @test 
     * 
     * Teste de busca por BdoOportunidades no Portal.
     * Apenas BdoOportunidades com status "Em Andamento" e "Expirado" são listadas.
    */
    public function bdooportunidade_search_on_portal()
    {
        $this->get(route('bdosite.index'))->assertOk();

        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $bdoOportunidade_1 = factory('App\BdoOportunidade')->create([
            'idempresa' => $bdoEmpresa->idempresa, 
            'status' => BdoOportunidade::STATUS_EM_ANDAMENTO, 
            'segmento' => BdoEmpresa::segmentos()[0],
            'regiaoatuacao' => ',1,',
        ]);

        $bdoOportunidade_2 = factory('App\BdoOportunidade')->create([
            'idempresa' => $bdoEmpresa->idempresa, 
            'status' => BdoOportunidade::STATUS_EXPIRADO, 
            'segmento' => BdoEmpresa::segmentos()[1],
            'regiaoatuacao' => ',2,'
        ]);
        $bdoOportunidade_3 = factory('App\BdoOportunidade')->create([
            'idempresa' => $bdoEmpresa->idempresa, 
            'status' => BdoOportunidade::STATUS_SOB_ANALISE, 
            'segmento' => BdoEmpresa::segmentos()[2]
        ]);
        
        $bdoOportunidade_4 = factory('App\BdoOportunidade')->create([
            'idempresa' => $bdoEmpresa->idempresa, 
            'status' => BdoOportunidade::STATUS_CONCLUIDO, 
            'segmento' => BdoEmpresa::segmentos()[3]
        ]);
        
        $bdoOportunidade_5 = factory('App\BdoOportunidade')->create([
            'idempresa' => $bdoEmpresa->idempresa, 
            'status' => BdoOportunidade::STATUS_RECUSADO, 
            'segmento' => BdoEmpresa::segmentos()[4]
        ]);
        $bdoOportunidade_6 = factory('App\BdoOportunidade')->create([
            'idempresa' => $bdoEmpresa->idempresa, 
            'status' => BdoOportunidade::STATUS_EM_ANDAMENTO, 
            'segmento' => BdoEmpresa::segmentos()[0],
            'regiaoatuacao' => ',1,'
        ]);

        // Sem filtro nenhum
        $this->get(route('bdosite.buscaOportunidades', [
            'palavra-chave' => null,
            'segmento' => null,
            'regional' => 'todas'
        ]))->assertSeeText($bdoOportunidade_1->titulo)
                ->assertSeeText($bdoOportunidade_2->titulo)
                ->assertDontSeeText($bdoOportunidade_3->titulo)
                ->assertDontSeeText($bdoOportunidade_4->titulo)
                ->assertDontSeeText($bdoOportunidade_5->titulo)
                ->assertSeeText($bdoOportunidade_6->titulo);

        // Todos filtros para achar apenas bdoOportunidade_1
        $this->get(route('bdosite.buscaOportunidades', [
            'palavra-chave' => $bdoOportunidade_1->titulo,
            'segmento' => $bdoOportunidade_1->segmento,
            'regional' => trim($bdoOportunidade_1->regiaoatuacao, ',')
            ]))->assertSeeText($bdoOportunidade_1->titulo)
                ->assertDontSeeText($bdoOportunidade_2->titulo)
                ->assertDontSeeText($bdoOportunidade_3->titulo)
                ->assertDontSeeText($bdoOportunidade_4->titulo)
                ->assertDontSeeText($bdoOportunidade_5->titulo)
                ->assertDontSeeText($bdoOportunidade_6->titulo);

        // Todos filtros para achar apenas bdoOportunidade_2
        $this->get(route('bdosite.buscaOportunidades', [
            'palavra-chave' => $bdoOportunidade_2->titulo,
            'segmento' => $bdoOportunidade_2->segmento,
            'regional' => trim($bdoOportunidade_2->regiaoatuacao, ',')
            ]))->assertDontSeeText($bdoOportunidade_1->titulo)
                ->assertSeeText($bdoOportunidade_2->titulo)
                ->assertDontSeeText($bdoOportunidade_3->titulo)
                ->assertDontSeeText($bdoOportunidade_4->titulo)
                ->assertDontSeeText($bdoOportunidade_5->titulo)
                ->assertDontSeeText($bdoOportunidade_6->titulo);

        // Apenas filtro por segmento
        $this->get(route('bdosite.buscaOportunidades', [
            'palavra-chave' => '',
            'segmento' => $bdoOportunidade_1->segmento,
            'regional' => 'todas'
            ]))->assertSeeText($bdoOportunidade_1->titulo)
                ->assertDontSeeText($bdoOportunidade_2->titulo)
                ->assertDontSeeText($bdoOportunidade_3->titulo)
                ->assertDontSeeText($bdoOportunidade_4->titulo)
                ->assertDontSeeText($bdoOportunidade_5->titulo)
                ->assertSeeText($bdoOportunidade_6->titulo);

        // Apenas filtro por região de atuação
        $this->get(route('bdosite.buscaOportunidades', [
            'palavra-chave' => '',
            'segmento' => '',
            'regional' => trim($bdoOportunidade_1->regiaoatuacao, ',')
            ]))->assertSeeText($bdoOportunidade_1->titulo)
                ->assertDontSeeText($bdoOportunidade_2->titulo)
                ->assertDontSeeText($bdoOportunidade_3->titulo)
                ->assertDontSeeText($bdoOportunidade_4->titulo)
                ->assertDontSeeText($bdoOportunidade_5->titulo)
                ->assertSeeText($bdoOportunidade_6->titulo);

        // Filtro para não trazer nenhum resultado
        $this->get(route('bdosite.buscaOportunidades', [
            'palavra-chave' => 'Teste sem resultado',
            'segmento' => null,
            'regional' => 'todas'
            ]))->assertDontSeeText($bdoOportunidade_1->titulo)
                ->assertDontSeeText($bdoOportunidade_2->titulo)
                ->assertDontSeeText($bdoOportunidade_3->titulo)
                ->assertDontSeeText($bdoOportunidade_4->titulo)
                ->assertDontSeeText($bdoOportunidade_5->titulo)
                ->assertDontSeeText($bdoOportunidade_6->titulo);
    }

}
