<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;

class SolicitaCedulaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permissao::insert([
            [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'index',
                'perfis' => '1,2,'
            ], [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'show',
                'perfis' => '1,2,'
            ]
        ]);
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
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('admin.solicita-cedula.show', $cedula->id))->assertForbidden();
        $this->post(route('admin.representante-solicita-cedula.post'), [
            'id' => $cedula->id,
            'idusuario' => $user->idusuario
            ])->assertForbidden();
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Aceito']);
    }

    /** @test 
     * 
     * Usuário com autorização pode aprovar Solicitação de Cédula.
    */
    public function authorized_users_can_accept_solicitacao_cedula()
    {
        $user = $this->signIn();
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('admin.solicita-cedula.show', $cedula->id))->assertOk();
        $this->post(route('admin.representante-solicita-cedula.post'), [
            'id' => $cedula->id,
            'idusuario' => $user->idusuario
            ])->assertStatus(302);
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
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('admin.solicita-cedula.show', $cedula->id))->assertForbidden();
        $this->post(route('admin.representante-solicita-cedula-reprovada.post'), [
            'id' => $cedula->id,
            'justificativa' => 'Não foi aprovado...',
            'idusuario' => $user->idusuario
            ])->assertForbidden();
        // $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Usuário com autorização pode reprovar Solicitação de Cédula.
    */
    public function authorized_users_can_refuse_solicitacao_cedula()
    {
        $user = $this->signIn();
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('admin.solicita-cedula.show', $cedula->id))->assertOk();
        $this->post(route('admin.representante-solicita-cedula-reprovada.post'), [
            'id' => $cedula->id,
            'justificativa' => 'Não foi aprovado...',
            'idusuario' => $user->idusuario
            ])->assertStatus(302);
        $this->assertDatabaseHas('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Usuário com autorização não pode reprovar Solicitação de Cédula sem justificativa.
    */
    public function authorized_users_cannot_refuse_solicitacao_cedula_without_justificativa()
    {
        $user = $this->signIn();
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('admin.solicita-cedula.show', $cedula->id))->assertOk();
        $this->post(route('admin.representante-solicita-cedula-reprovada.post'), [
            'id' => $cedula->id,
            'justificativa' => '',
            'idusuario' => $user->idusuario
            ])->assertSessionHasErrors(['justificativa']);
        $this->assertDatabaseMissing('solicitacoes_cedulas', ['status' => 'Recusado']);
    }

    /** @test 
     * 
     * Teste nos critérios de busca de Solicitação de Cédula.
     * (id da solicitaçao, status, nome do representante, cpf_cnpj, registro_core, regional)
    */
    public function search_criteria_for_solicita_cedula()
    {
        $this->signIn();
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
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'mindia' => Carbon::yesterday()->toDateString(), 
            'maxdia' => Carbon::tomorrow()->toDateString()
        ]))
        ->assertSeeText($cedula->regional->regional) 
        ->assertSeeText($cedula->representante->nome) 
        ->assertSeeText($cedula->representante->registro_core) 
        ->assertSeeText($cedula->status);

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'mindia' => Carbon::now()->toDateString(), 
            'maxdia' => Carbon::now()->toDateString()
        ]))
        ->assertSeeText($cedula->regional->regional) 
        ->assertSeeText($cedula->representante->nome) 
        ->assertSeeText($cedula->representante->registro_core) 
        ->assertSeeText($cedula->status);

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'mindia' => Carbon::yesterday()->toDateString(), 
            'maxdia' => Carbon::yesterday()->toDateString()
        ]))
        ->assertDontSeeText($cedula->regional->regional) 
        ->assertDontSeeText($cedula->representante->nome) 
        ->assertDontSeeText($cedula->representante->registro_core) 
        ->assertDontSeeText($cedula->status);

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'mindia' => Carbon::tomorrow()->toDateString(), 
            'maxdia' => Carbon::tomorrow()->toDateString()
        ]))
        ->assertDontSeeText($cedula->regional->regional) 
        ->assertDontSeeText($cedula->representante->nome) 
        ->assertDontSeeText($cedula->representante->registro_core) 
        ->assertDontSeeText($cedula->status);

        $this->get(route('solicita-cedula.filtro', [
            'filtro' => 'sim', 
            'mindia' => Carbon::tomorrow()->toDateString(), 
            'maxdia' => Carbon::yesterday()->toDateString()
        ]))
        ->assertDontSeeText($cedula->status);
    }

    /** @test 
     * 
     * Usuário com autorização pode ver o botão pdf após o aceite.
    */
    public function authorized_users_can_to_view_pdf_button()
    {
        $user = $this->signIn();
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('admin.solicita-cedula.show', $cedula->id))->assertOk();
        $this->post(route('admin.representante-solicita-cedula.post'), [
            'id' => $cedula->id,
            'idusuario' => $user->idusuario
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
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('admin.solicita-cedula.show', $cedula->id))->assertForbidden();
        $this->post(route('admin.representante-solicita-cedula.post'), [
            'id' => $cedula->id,
            'idusuario' => $user->idusuario
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
        $cedula = factory('App\SolicitaCedula')->create([
            'status' => 'Aceito',
            'idusuario' => $user->idusuario
        ]);
        $this->get(route('solicita-cedula.index'))->assertDontSeeText('PDF');
        $this->get(route('admin.solicita-cedula.pdf', $cedula->id))->assertForbidden();
    }

    /** @test 
     * 
     * Usuário com autorização pode acessar a rota do pdf gerado
    */
    public function authorized_users_can_access_route_pdf()
    {
        $user = $this->signIn();
        $cedula = factory('App\SolicitaCedula')->create([
            'status' => 'Aceito',
            'idusuario' => $user->idusuario
        ]);
        $this->get(route('solicita-cedula.index'))->assertSeeText('PDF');
        $this->get(route('admin.solicita-cedula.pdf', $cedula->id))->assertOk();
    }

    /** @test 
     * 
     * Usuário não pode gerar o pdf se a cédula não possui status Aceito
    */
    public function authorized_users_cannot_to_view_pdf_with_status_different_aceito()
    {
        $user = $this->signIn();
        $cedula = factory('App\SolicitaCedula')->create();
        $this->get(route('solicita-cedula.index'))->assertDontSeeText('PDF');
        $this->get(route('admin.solicita-cedula.pdf', $cedula->id))->assertStatus(302);
        $this->get(route('solicita-cedula.index'))->assertSeeText('A cédula não foi aceita.');
    }

    /** @test 
     * 
     * Gerar log ao aceitar a solicitação
    */
    public function log_is_generated_when_cedula_is_accepted()
    {
        $user = $this->signInAsAdmin();
        $cedula = factory('App\SolicitaCedula')->create();
        $this->post(route('admin.representante-solicita-cedula.post'), [
            'idusuario' => $user->idusuario,
            'id' => $cedula->id
        ]);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('atendente aceitou', $log);
        $this->assertStringContainsString('solicitação de cédula', $log);
    }

    /** @test 
     * 
     * Gerar log ao recusar a solicitação
    */
    public function log_is_generated_when_cedula_is_refuse()
    {
        $user = $this->signInAsAdmin();
        $cedula = factory('App\SolicitaCedula')->create();
        $this->post(route('admin.representante-solicita-cedula-reprovada.post'), [
            'idusuario' => $user->idusuario,
            'id' => $cedula->id,
            'justificativa' => 'teste com logs'
        ]);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('atendente recusou e justificou', $log);
        $this->assertStringContainsString('solicitação de cédula', $log);
    }
}
