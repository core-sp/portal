<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;
use App\Mail\SolicitaCedulaMail;
use Illuminate\Support\Facades\Mail;

class SolicitaCedulaTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES SOLICITAÇÃO DE CÉDULA NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
    
        $repCedula = factory('App\SolicitaCedula')->create();

        $this->get(route('solicita-cedula.index'))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.filtro'))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.show', $repCedula->id))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.pdf', $repCedula->id))->assertRedirect(route('login'));
        $this->get(route('solicita-cedula.busca'))->assertRedirect(route('login'));
        $this->put(route('solicita-cedula.update', $repCedula->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        factory('App\User')->create();
        $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $this->assertAuthenticated('web');
        
        $repCedula = factory('App\SolicitaCedula')->create();

        $this->get(route('solicita-cedula.index'))->assertForbidden();
        $this->get(route('solicita-cedula.filtro'))->assertForbidden();
        $this->get(route('solicita-cedula.show', $repCedula->id))->assertForbidden();
        $this->get(route('solicita-cedula.pdf', $repCedula->id))->assertForbidden();
        $this->get(route('solicita-cedula.busca'))->assertForbidden();
        $this->put(route('solicita-cedula.update', $repCedula->id))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode listar Solicitações de Cédulas. Verificando autorização no uso
     * dos filtros e da busca.
    */
    public function non_authorized_users_cannot_list_solicitacoes_cedulas()
    {
        // para criar mais perfis e ser diferente do "Permissao::insert()"
        factory('App\Perfil', 5)->create();
        $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $this->get(route('solicita-cedula.index'))->assertForbidden();
        $this->get(route('solicita-cedula.busca'))->assertForbidden();
        $this->get(route('solicita-cedula.filtro'))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário com autorização pode listar Solicitações de Cédulas. Verificando autorização no uso
     * dos filtros e da busca.
    */
    public function authorized_users_can_list_solicitacoes_cedulas()
    {
        $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $this->get(route('solicita-cedula.index'))->assertOk();
        $this->get(route('solicita-cedula.busca'))->assertOk();
        $this->get(route('solicita-cedula.filtro'))->assertOk();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode aprovar Solicitação de Cédula.
    */
    public function non_authorized_users_cannot_accept_solicitacao_cedula()
    {
        // para criar mais perfis e ser diferente do "Permissao::insert()"
        factory('App\Perfil', 5)->create();
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertForbidden();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Aceito',
        ])->assertForbidden();
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Aceito']);
    }

    /** @test 
     * 
     * Usuário com autorização pode aprovar Solicitação de Cédula.
    */
    public function authorized_users_can_accept_solicitacao_cedula()
    {
        Mail::fake();

        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertOk();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Aceito',
        ])->assertStatus(302);

        Mail::assertQueued(SolicitaCedulaMail::class);

        $this->assertDatabaseHas('solicitacoes_cedulas', ['status' => 'Aceito']);
    }

    /** @test 
     * 
     * Usuário sem autorização não pode reprovar Solicitação de Cédula.
    */
    public function non_authorized_users_cannot_refuse_solicitacao_cedula()
    {
        // para criar mais perfis e ser diferente do "Permissao::insert()"
        factory('App\Perfil', 5)->create();
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertForbidden();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Recusado',
            'justificativa' => 'Não foi aprovado, porque ...'
        ])->assertForbidden();
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Usuário com autorização pode reprovar Solicitação de Cédula.
    */
    public function authorized_users_can_refuse_solicitacao_cedula()
    {
        Mail::fake();

        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertOk();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Recusado',
            'justificativa' => 'Não foi aprovado, porque ...'
        ])->assertStatus(302);

        Mail::assertQueued(SolicitaCedulaMail::class);

        $this->assertDatabaseHas('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Usuário com autorização não pode reprovar Solicitação de Cédula sem justificativa.
    */
    public function authorized_users_cannot_refuse_solicitacao_cedula_without_justificativa()
    {
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertOk();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Recusado',
            'justificativa' => ''
        ])->assertSessionHasErrors(['justificativa']);
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Usuário com autorização não pode reprovar Solicitação de Cédula sem justificativa.
    */
    public function users_cannot_refuse_solicitacao_cedula_with_justificativa_less_than_5_chars()
    {
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertOk();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Recusado',
            'justificativa' => 'abcd'
        ])->assertSessionHasErrors(['justificativa']);
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Usuário com autorização não pode reprovar Solicitação de Cédula sem justificativa.
    */
    public function users_cannot_refuse_solicitacao_cedula_with_justificativa_more_than_600_chars()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertOk();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Recusado',
            'justificativa' => $faker->sentence(900)
        ])->assertSessionHasErrors(['justificativa']);
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Usuário com autorização não pode reprovar Solicitação de Cédula sem justificativa.
    */
    public function users_cannot_refuse_solicitacao_cedula_with_status_wrong()
    {
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertOk();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Aceita'
        ])->assertSessionHasErrors(['status']);
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Aceita']);
    }

    /** @test 
     * 
     * Teste nos critérios de busca de Solicitação de Cédula.
     * (id da solicitaçao, status, nome do representante, cpf_cnpj, registro_core, regional)
    */
    public function search_criteria_for_solicita_cedula()
    {
        $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();

        $this->get(route('solicita-cedula.busca', ['q' => $cedula->id]))
            ->assertSeeText($cedula->id);

        $this->get(route('solicita-cedula.busca', ['q' => $cedula->status]))
            ->assertSeeText($cedula->status);

        $this->get(route('solicita-cedula.busca', ['q' => $cedula->representante->nome]))
            ->assertSeeText($cedula->representante->nome);

        $this->get(route('solicita-cedula.busca', ['q' => $cedula->representante->cpf_cnpj]))
            ->assertSeeText($cedula->representante->cpf_cnpj);

        $this->get(route('solicita-cedula.busca', ['q' => $cedula->representante->registro_core]))
            ->assertSeeText($cedula->representante->registro_core);

        $this->get(route('solicita-cedula.busca', ['q' => $cedula->regional->regional]))
            ->assertSeeText($cedula->regional->regional);
    }

    /** @test 
     * 
     * Testando o filtro da solicitação de cédula.
     * (data de solicitação mínima e máxima)
    */
    public function solicitacao_cedula_filter()
    {
        $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'datemin' => Carbon::yesterday()->toDateString(), 
            'datemax' => Carbon::tomorrow()->toDateString(),
            'status' => 'Em andamento'
        ]))
        ->assertDontSeeText('Nenhum solicitação de cédula encontrado');

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'datemin' => Carbon::now()->toDateString(), 
            'datemax' => Carbon::now()->toDateString(),
            'status' => 'Em andamento'
        ]))
        ->assertDontSeeText('Nenhum solicitação de cédula encontrado');

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'datemin' => Carbon::yesterday()->toDateString(), 
            'datemax' => Carbon::yesterday()->toDateString(),
            'status' => 'Aceito'
        ]))
        ->assertSeeText('Nenhum solicitação de cédula encontrado');

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'datemin' => Carbon::tomorrow()->toDateString(), 
            'datemax' => Carbon::tomorrow()->toDateString(),
            'status' => 'Recusado'
        ]))
        ->assertSeeText('Nenhum solicitação de cédula encontrado');

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'datemin' => Carbon::tomorrow()->toDateString(), 
            'datemax' => Carbon::yesterday()->toDateString(),
            'status' => 'Qualquer'
        ]))
        ->assertSeeText('Nenhum solicitação de cédula encontrado');
    }

    /** @test 
     * 
     * Usuário com autorização pode ver o botão pdf após o aceite.
    */
    public function authorized_users_can_to_view_pdf_button()
    {
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertOk();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Aceito'
        ])->assertStatus(302);
        $this->get(route('solicita-cedula.index'))->assertSeeText('PDF');
    }

    /** @test 
     * 
     * Usuário sem autorização não pode ver o botão pdf após o aceite.
    */
    public function non_authorized_users_cannot_to_view_pdf_button()
    {
        // para criar mais perfis e ser diferente do "Permissao::insert()"
        factory('App\Perfil', 5)->create();
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.show', $cedula->id))->assertForbidden();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Aceito'
        ])->assertForbidden();
        $this->get(route('solicita-cedula.index'))->assertDontSeeText('PDF');
    }

    /** @test 
     * 
     * Usuário sem autorização não pode acessar a rota do pdf gerado
    */
    public function non_authorized_users_cannot_access_route_pdf()
    {
        // para criar mais perfis e ser diferente do "Permissao::insert()"
        factory('App\Perfil', 5)->create();
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create([
            'status' => 'Aceito',
            'idusuario' => $user->idusuario
        ]);
        $this->get(route('solicita-cedula.index'))->assertDontSeeText('PDF');
        $this->get(route('solicita-cedula.pdf', $cedula->id))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário com autorização pode acessar a rota do pdf gerado
    */
    public function authorized_users_can_access_route_pdf()
    {
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create([
            'status' => 'Aceito',
            'idusuario' => $user->idusuario
        ]);
        $this->get(route('solicita-cedula.index'))->assertSeeText('PDF');
        $this->get(route('solicita-cedula.pdf', $cedula->id))->assertOk();
    }

    /** @test 
     * 
     * Usuário não pode gerar o pdf se a cédula não possui status Aceito
    */
    public function authorized_users_cannot_to_view_pdf_with_status_different_aceito()
    {
        $user = $this->signIn();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.index'))->assertDontSeeText('PDF');
        $this->get(route('solicita-cedula.pdf', $cedula->id))->assertStatus(302);
        $this->get(route('solicita-cedula.index'))->assertSeeText('A solicitação de cédula não foi aceita.');
    }

    /** @test 
     * 
     * Gerar log ao aceitar a solicitação
    */
    public function log_is_generated_when_cedula_is_accepted()
    {
        $user = $this->signInAsAdmin();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Aceito'
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome . ' (usuário ' . $user->idusuario . ') atendente aceitou *solicitação de cédula* (id: ' . $cedula->id . ')';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Gerar log ao recusar a solicitação
    */
    public function log_is_generated_when_cedula_is_refuse()
    {
        $user = $this->signInAsAdmin();
        Permissao::find(59)->update(['perfis' => '1,2']);
        Permissao::find(60)->update(['perfis' => '1,2']);

        $cedula = factory('App\SolicitaCedula')->create();
        $this->put(route('solicita-cedula.update', $cedula->id), [
            'status' => 'Recusado'
        ]);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome . ' (usuário ' . $user->idusuario . ') atendente recusou e justificou *solicitação de cédula* (id: ' . $cedula->id . ')';
        $this->assertStringContainsString($texto, $log);
    }

    /** 
     * =======================================================================================================
     * TESTES SOLICITAÇÃO DE CÉDULA NA ÁREA DO REPRESENTANTE
     * =======================================================================================================
     */

    /** @test 
     * 
     * Representante que já tem cédula em andamento não pode solicitar
    */
    public function cannot_insert_new_solicitacao_cedula_has_solicitacao_em_andamento()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $representante->id
        ]);
        $cedulaNova = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertStatus(302);
        $this->post(route('representante.inserirSolicitarCedula'), $cedulaNova)->assertStatus(302);
        $this->assertDatabaseMissing('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'bairro' => $cedulaNova['bairro'], 
            'logradouro' => $cedulaNova['logradouro'],
            'numero' => $cedulaNova['numero'],
            'tipo' => $cedulaNova['tipo']
        ]);
    }

    /** @test 
     * 
     * Representante que já tem cédulas solicitadas, mas nenhuma em andamento, pode solicitar cédula
    */
    public function insert_new_solicitacao_cedula_has_cedulas_but_hasnt_em_andamento()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        factory('App\SolicitaCedula', 1)->create([
            'idrepresentante' => $representante->id,
            'status' => 'Aceito'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);

        unset($cedula['status']);
        unset($cedula['justificativa']);

        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertStatus(302);
        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'bairro' => $cedula['bairro'], 
            'logradouro' => $cedula['logradouro'],
            'numero' => $cedula['numero'],
            'tipo' => $cedula['tipo']
        ]);
    }

    /** @test 
     * 
     * Não pode criar solicitação de cédula com o formulário vazio
    */
    public function cannot_insert_new_solicitacao_cedula_with_missing_mandatory_input_pf()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $dados = [
            'nome' => '',
            'cpf' => '',
            'rg' => '',
            'cep' => null, 
            'bairro' => null, 
            'logradouro' => null, 
            'numero' => null, 
            'estado' => null, 
            'municipio' => null,
            'tipo' => null,
        ];
        $this->post(route('representante.inserirSolicitarCedula'), $dados)
        ->assertSessionHasErrors([
            'rg',
            'cep', 
            'bairro', 
            'logradouro', 
            'numero', 
            'estado', 
            'municipio',
            'tipo',
        ]);
        $this->assertDatabaseMissing('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id
        ]);
    }

    /** @test 
     * 
     * Não pode criar solicitação de cédula com o formulário vazio
    */
    public function cannot_insert_new_solicitacao_cedula_with_missing_mandatory_input_pj()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $dados = [
            'nome' => '',
            'cpf' => '',
            'rg' => '',
            'cep' => null, 
            'bairro' => null, 
            'logradouro' => null, 
            'numero' => null, 
            'estado' => null, 
            'municipio' => null,
        ];
        $this->post(route('representante.inserirSolicitarCedula'), $dados)
        ->assertSessionHasErrors([
            'nome',
            'cpf',
            'rg',
            'cep', 
            'bairro', 
            'logradouro', 
            'numero', 
            'estado', 
            'municipio',
        ]);
        $this->assertDatabaseMissing('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id
        ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_nome_less_than_6_chars_if_pj()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => 'Teste',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'nome', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_nome_more_than_191_chars_if_pj()
    {
        $faker = \Faker\Factory::create();
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => $faker->sentence(400),
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'nome', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_nome_with_number_if_pj()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => 'Teste t2este',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'nome', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_cpf_wrong_if_pj()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'cpf' => '000000000',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'cpf', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_cep_less_than_9_chars()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'cep' => '0000-000',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'cep', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_bairro_less_than_4_chars()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'bairro' => 'tes',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'bairro', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_bairro_more_than_100_chars()
    {
        $faker = \Faker\Factory::create();
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'bairro' => $faker->sentence(400),
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'bairro', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_logradouro_less_than_4_chars()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'logradouro' => 'tes',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'logradouro', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_logradouro_more_than_100_chars()
    {
        $faker = \Faker\Factory::create();
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'logradouro' => $faker->sentence(400),
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'logradouro', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_numero_more_than_15_chars()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'numero' => '1234567891234567',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'numero', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_complemento_more_than_100_chars()
    {
        $faker = \Faker\Factory::create();
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'complemento' => $faker->sentence(400),
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'complemento', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_estado_wrong()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'estado' => 'PP',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'estado', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_municipio_less_than_4_chars()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'municipio' => 'tes',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'municipio', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_municipio_more_than_100_chars()
    {
        $faker = \Faker\Factory::create();
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'municipio' => $faker->sentence(400),
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'municipio', 
            ]);
    }

    /** @test 
     * 
     * Erro ao não informar alguns dados obrigatórios ao inserir nova solicitação de cédula.
    */
    public function cannot_insert_new_solicitacao_cedula_with_tipo_wrong()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'tipo' => 'Fisico',
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
            ->assertSessionHasErrors([
                'tipo', 
            ]);
    }

    /** @test 
     * 
     * Deve trazer nome, rg e cpf do gerenti e do bd se for PF ao solicitar a cedula
    */
    public function fill_nome_rg_cpf_if_pf_in_solicitacao_cedula()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $response = $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => $response->getOriginalContent()->getData()['nome'],
            'rg' => apenasNumerosLetras($response->getOriginalContent()->getData()['rg']),
            'cpf' => apenasNumeros($response->getOriginalContent()->getData()['cpf'])
        ]);

        unset($cedula['status']);
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'cpf' => null,
            'rg' => $cedula['rg'],
            'nome' => null,
            'cep' => $cedula['cep']
        ]);
    }

    /** @test 
     * 
     * Não deve preencher os campos nome, rg e cpf se for PJ ao solicitar a cedula
    */
    public function if_pj_inputs_nome_rg_cpf_empty_in_solicitacao_cedula()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '30735253000174'
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))->assertOk();
        $response = $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'nome' => $response->getOriginalContent()->getData()['nome'],
            'rg' => apenasNumerosLetras($response->getOriginalContent()->getData()['rg']),
            'cpf' => apenasNumeros($response->getOriginalContent()->getData()['cpf'])
        ]);

        unset($cedula['status']);
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)->assertSessionHasErrors([
            'nome',
            'rg',
            'cpf'
        ]);
    }

    /** @test 
     * 
     * Deve listar solicitações de cédula do representante logado
    */
    public function list_solicitacoes_cedula_only_representante_authenticated()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $representante->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))
        ->assertSeeText($cedula->cep);
    }

    /** @test 
     * 
     * Não deve listar solicitações de cédula de representante diferente do que está logado
    */
    public function cannot_list_solicitacoes_cedula_representante_different_authenticated()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        $representante = factory('App\Representante')->create();
        $fake = factory('App\Representante')->create([
            'cpf_cnpj' => '65736926083'
        ]);
        $cedula = factory('App\SolicitaCedula')->create([
            'idrepresentante' => $fake->id
        ]);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.solicitarCedulaView'))
        ->assertDontSeeText($cedula->cep);
    }

    /** @test 
     * 
     * Representante pode solicitar cédula pela primeira vez
    */
    public function insert_new_solicitacao_cedula_pf()
    {
        Mail::fake();

        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);

        unset($cedula['status']);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
        ->assertRedirect(route('representante.solicitarCedulaView'));

        Mail::assertQueued(SolicitaCedulaMail::class);

        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'rg' => $cedula['rg'],
            'bairro' => $cedula['bairro'], 
            'logradouro' => $cedula['logradouro'],
            'numero' => $cedula['numero'],
            'tipo' => $cedula['tipo']
        ]);
    }

    /** @test 
     * 
    */
    public function log_is_generated_when_access_insert_new_solicitacao_cedula()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create();
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id
        ]);

        unset($cedula['status']);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);

        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= 'Usuário '. $representante->id . ' ("'. $representante->cpf_cnpj .'") acessou a aba "Solicitação de Cédula" para incluir.';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test 
     * 
     * Representante pode solicitar cédula pela primeira vez
    */
    public function insert_new_solicitacao_cedula_pj()
    {
        Mail::fake();

        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '30735253000174',
            'ass_id' => '000002'
        ]);
        $cedula = factory('App\SolicitaCedula')->raw([
            'idrepresentante' => $representante->id,
            'tipo' => null
        ]);

        unset($cedula['status']);
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertOk();
        $this->post(route('representante.inserirSolicitarCedula'), $cedula)
        ->assertRedirect(route('representante.solicitarCedulaView'));

        Mail::assertQueued(SolicitaCedulaMail::class);

        $this->assertDatabaseHas('solicitacoes_cedulas', [
            'idrepresentante' => $representante->id,
            'rg' => $cedula['rg'],
            'bairro' => $cedula['bairro'], 
            'logradouro' => $cedula['logradouro'],
            'numero' => $cedula['numero'],
            'tipo' => \App\SolicitaCedula::TIPO_FISICA
        ]);
    }

    /** @test 
    */
    public function cannot_view_option_digital_in_solicitacao_cedula_pj()
    {
        $regional = factory('App\Regional')->create([
            'regional' => 'SÃO PAULO'
        ]);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO - Alameda Santos'
        ]);
        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '30735253000174'
        ]);

        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.inserirSolicitarCedulaView'))
        ->assertSee('<option value="Impressa" selected>Impressa</option>')
        ->assertDontSee('<option value="Impressa e Digital">Impressa e Digital</option>')
        ->assertDontSee('<option value="Digital">Digital</option>');
    }
}
