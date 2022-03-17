<?php

namespace Tests\Feature;

use App\Regional;
use App\Permissao;
use Tests\TestCase;
use App\Agendamento;
use App\Mail\AgendamentoMailGuest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AgendamentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permissao::insert([
            [
                'controller' => 'AgendamentoController',
                'metodo' => 'index',
                'perfis' => '1,6,12,13,8,21'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'edit',
                'perfis' => '1,21'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ]
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES AGENDAMENTO NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $agendamento = factory('App\Agendamento')->create();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();

        $this->get(route('agendamentos.lista'))->assertRedirect(route('login'));
        $this->get(route('agendamentos.busca'))->assertRedirect(route('login'));
        $this->get(route('agendamentos.filtro'))->assertRedirect(route('login'));
        $this->get(route('agendamentos.pendentes'))->assertRedirect(route('login'));
        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertRedirect(route('login'));
        $this->put(route('agendamentos.edit', $agendamento->idagendamento))->assertRedirect(route('login'));
        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertRedirect(route('login'));

        $this->get(route('agendamentobloqueios.lista'))->assertRedirect(route('login'));
        $this->get('/admin/agendamentos/bloqueios/busca')->assertRedirect(route('login'));
        $this->get('/admin/agendamentos/bloqueios/criar')->assertRedirect(route('login'));
        $this->get('/admin/agendamentos/bloqueios/editar/'.$bloqueio->idagendamentobloqueio)->assertRedirect(route('login'));
        $this->post('/admin/agendamentos/bloqueios/criar')->assertRedirect(route('login'));
        $this->put('/admin/agendamentos/bloqueios/editar/'.$bloqueio->idagendamentobloqueio)->assertRedirect(route('login'));
        $this->delete('/admin/agendamentos/bloqueios/apagar/'.$bloqueio->idagendamentobloqueio)->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $agendamento = factory('App\Agendamento')->create();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();

        $this->get(route('agendamentos.lista'))->assertForbidden();
        $this->get(route('agendamentos.busca'))->assertForbidden();
        $this->get(route('agendamentos.filtro'))->assertForbidden();
        $this->get(route('agendamentos.pendentes'))->assertForbidden();
        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertForbidden();
        $this->put(route('agendamentos.edit', $agendamento->idagendamento), $agendamento->toArray())->assertForbidden();
        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertForbidden();

        $this->get(route('agendamentobloqueios.lista'))->assertForbidden();
        $this->get('/admin/agendamentos/bloqueios/busca')->assertForbidden();
        $this->get('/admin/agendamentos/bloqueios/criar')->assertForbidden();
        $this->get('/admin/agendamentos/bloqueios/editar/'.$bloqueio->idagendamentobloqueio)->assertForbidden();
        $this->post('/admin/agendamentos/bloqueios/criar', $bloqueio->toArray())->assertForbidden();
        $this->put('/admin/agendamentos/bloqueios/editar/'.$bloqueio->idagendamentobloqueio, $bloqueio->toArray())->assertForbidden();
        $this->delete('/admin/agendamentos/bloqueios/apagar/'.$bloqueio->idagendamentobloqueio)->assertForbidden();
    }

    /** @test 
     * 
     * Usuário sem autorização não pode listar Agendamentos. Verificando autorização no uso
     * dos filtros, da busca e da visualização de Agendamentos pendentes.
    */
    public function non_authorized_users_cannot_list_agendamento()
    {
        $this->signIn();

        $this->get(route('agendamentos.lista'))->assertForbidden();
        $this->get(route('agendamentos.busca'))->assertForbidden();
        $this->get(route('agendamentos.filtro'))->assertForbidden();
        $this->get(route('agendamentos.pendentes'))->assertForbidden();   
    }

    /** @test 
     * 
     * Usuário sem autorização não pode editar Agendamentos.
    */
    public function non_authorized_users_cannot_edit_agendamento()
    {
        $user = $this->signIn();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['status'] = Agendamento::STATUS_CANCELADO;
        $dados['antigo'] = 0;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertForbidden();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertForbidden();
        $this->assertNotEquals(Agendamento::find($agendamento->idagendamento)->status, $dados['status']);
    }

    // /** @test */
    // public function authorized_users_can_edit_agendamento()
    // {
    //     $user = $this->signInAsAdmin();

    //     $agendamento = factory('App\Agendamento')->create();

    //     $agendamento->nome = 'Novo nome';
    //     $agendamento->email = 'novoemail@teste.com';
    //     $dados = $agendamento->toArray();
    //     $dados['antigo'] = 0;

    //     $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
    //     $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertStatus(302);

    //     $this->assertDatabaseHas('agendamentos', [
    //         'idagendamento' => $agendamento->idagendamento,
    //         'nome' => $agendamento->nome,
    //         'email' => $agendamento->email,
    //         'cpf' => $agendamento->cpf,
    //         'celular' => $agendamento->celular,
    //     ]);
    // }

    /** @test */
    public function perfil_gerente_seccional_can_edit_tomorrow_agendamento_if_same_regional()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $this->signIn($user);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['status'] = Agendamento::STATUS_CANCELADO;
        $dados['antigo'] = 0;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertStatus(302);
        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, $dados['status']);
    }

    /** @test */
    public function perfil_gerente_seccional_cannot_edit_tomorrow_agendamento_if_different_regional()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $this->signIn($user);

        $agendamento = factory('App\Agendamento')->create();

        $dados = $agendamento->toArray();
        $dados['status'] = Agendamento::STATUS_CANCELADO;
        $dados['antigo'] = 0;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertForbidden();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertForbidden();
        $this->assertNotEquals(Agendamento::find($agendamento->idagendamento)->status, $dados['status']);
    }

    /** @test 
     * 
     * Usuário com autorização pode listar Agendamentos. Verificando autorização no uso
     * dos filtros, da busca e da visualização de Agendamentos pendentes.
    */
    public function authorized_users_can_list_agendamento()
    {
        $this->signInAsAdmin();

        $this->get(route('agendamentos.lista'))->assertOk();
        $this->get(route('agendamentos.busca'))->assertOk();
        $this->get(route('agendamentos.filtro'))->assertOk();
        $this->get(route('agendamentos.pendentes'))->assertOk();   
    }

    /** @test */
    public function cannot_edit_status_to_nao_compareceu_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['status'] = Agendamento::STATUS_NAO_COMPARECEU;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertStatus(302);
        $this->get(route('agendamentos.lista'))
        ->assertSeeText('Status do agendamento não pode ser modificado para Compareceu ou Não Compareceu antes da data agendada');
    }

    /** @test */
    public function cannot_edit_status_to_compareceu_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['status'] = Agendamento::STATUS_COMPARECEU;
        $dados['antigo'] = 0;
        $dados['idusuario'] = $user->idusuario;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertStatus(302);
        $this->get(route('agendamentos.lista'))
        ->assertSeeText('Status do agendamento não pode ser modificado para Compareceu ou Não Compareceu antes da data agendada');
    }

    /** @test */
    public function cannot_edit_atendente_if_status_sem_status_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['idusuario'] = $user->idusuario;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertStatus(302);
        $this->get(route('agendamentos.lista'))
        ->assertSeeText('Agendamento sem status não pode ter atendente');
    }

    /** @test */
    public function cannot_edit_atendente_if_status_sem_status()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['idusuario'] = $user->idusuario;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertStatus(302);
        $this->get(route('agendamentos.lista'))
        ->assertSeeText('Agendamento sem status não pode ter atendente');
    }

    /** @test */
    public function cannot_edit_without_atendente_if_status_compareceu()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['status'] = Agendamento::STATUS_COMPARECEU;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['idusuario']);
    }

    /** @test */
    public function cannot_edit_without_atendente_if_status_compareceu_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['status'] = Agendamento::STATUS_COMPARECEU;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['idusuario']);
    }

    /** @test */
    public function cannot_edit_wrong_tiposervico_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['tiposervico'] = 'Qualquer coisa';

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['tiposervico']);
    }

    /** @test */
    public function cannot_edit_wrong_tiposervico()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['tiposervico'] = 'Qualquer coisa';

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['tiposervico']);
    }

    /** @test */
    public function cannot_edit_wrong_status_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['status'] = 'Qualquer coisa';

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function cannot_edit_wrong_status()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['status'] = 'Qualquer coisa';

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function cannot_edit_wrong_cpf_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['cpf'] = '123.456.789-00';

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['cpf']);
    }

    /** @test */
    public function cannot_edit_without_requireds_inputs_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['nome'] = '';
        $dados['email'] = '';
        $dados['cpf'] = '';
        $dados['celular'] = '';
        $dados['tiposervico'] = '';

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['nome', 'email', 'cpf', 'celular', 'tiposervico']);
    }

    /** @test */
    public function cannot_edit_nome_email_cpf_celular_in_past_or_today_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados = [
            'antigo' => 1,
            'nome' => 'Teste',
            'email' => 'teste@teste.com',
            'cpf' => '267.239.070-35',
            'celular' => '(11) 99999-9999',
        ];

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular
        ]);
    }

    /** @test */
    public function cannot_edit_without_tiposervico_in_past_or_today_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['tiposervico'] = null;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['tiposervico']);

    }

    /** @test */
    public function can_edit_status_to_sem_status()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['status'] = null;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $dados['status']
        ]);
    }

    /** @test */
    public function can_edit_status_to_sem_status_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'status' => Agendamento::STATUS_CANCELADO
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['status'] = null;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $dados['status']
        ]);
    }

    /** @test */
    public function can_edit_status_to_cancelado()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['status'] = Agendamento::STATUS_CANCELADO;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $dados['status']
        ]);
    }

    /** @test */
    public function can_edit_status_to_cancelado_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;
        $dados['status'] = Agendamento::STATUS_CANCELADO;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $dados['status']
        ]);
    }

    /** @test */
    public function can_edit_status_to_nao_compareceu()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
        ]);

        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;
        $dados['status'] = Agendamento::STATUS_NAO_COMPARECEU;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $dados['status']
        ]);
    }

    /** @test */
    public function can_view_inputs_agendamento()
    {
        $user = $this->signInAsAdmin();

        $perfilAtendente = factory('App\Perfil')->create([
            'idperfil' => 8,
            'nome' => 'Atendimento',
        ]);

        $atendente = factory('App\User')->create([
            'idperfil' => $perfilAtendente->idperfil,
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))
        ->assertSee($agendamento->nome)
        ->assertSee($agendamento->email)
        ->assertSee($agendamento->cpf)
        ->assertSee($agendamento->celular)
        ->assertSee($agendamento->tiposervico)
        ->assertSee($agendamento->status)
        ->assertSee($agendamento->regional->regional)
        ->assertSee(onlyDate($agendamento->dia))
        ->assertSee($agendamento->hora)
        ->assertSee('Ninguém')
        ->assertSee($atendente->nome);
    }

    /** @test */
    public function can_view_messages_status_agendamento()
    {
        $user = $this->signInAsAdmin();

        $age1 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $this->get(route('agendamentos.edit', $age1->idagendamento))
        ->assertSee('Reenviar email de confirmação');

        $age2 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $this->get(route('agendamentos.edit', $age2->idagendamento))
        ->assertSee('Validação pendente');

        $age3 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'status' => Agendamento::STATUS_CANCELADO
        ]);

        $this->get(route('agendamentos.edit', $age3->idagendamento))
        ->assertSee('Atendimento cancelado');

        $age4 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $this->get(route('agendamentos.edit', $age4->idagendamento))
        ->assertSee('Não compareceu');

        $age4 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
            'status' => Agendamento::STATUS_COMPARECEU
        ]);

        $this->get(route('agendamentos.edit', $age4->idagendamento))
        ->assertSee('Atendimento realizado com sucesso no dia');
    }

    /** @test */
    public function can_view_agendamentos_list()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('agendamentos.lista'))
        ->assertSee('Nenhum agendamento encontrado');

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $this->get(route('agendamentos.lista'))
        ->assertSee($agendamento->protocolo)
        ->assertSee($agendamento->idagendamento)
        ->assertSee($agendamento->cpf)
        ->assertSee($agendamento->tiposervico)
        ->assertSee($agendamento->status)
        ->assertSee($agendamento->regional->regional)
        ->assertSee(onlyDate($agendamento->dia))
        ->assertSee($agendamento->hora);
    }

    /** @test */
    public function can_view_buttons_status_and_editar_in_past_or_today_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $this->get(route('agendamentos.lista'))
        ->assertSee('<button type="submit" name="status" class="btn btn-sm btn-primary" value="'.Agendamento::STATUS_COMPARECEU.'">Confirmar</button>')
        ->assertSee('<button type="submit" name="status" class="btn btn-sm btn-danger ml-1" value="'.Agendamento::STATUS_NAO_COMPARECEU.'">'.Agendamento::STATUS_NAO_COMPARECEU.'</button>')
        ->assertSee('<a href="'.route('agendamentos.edit', $agendamento->idagendamento).'" class="btn btn-sm btn-default">Editar</a>');
    }

    /** @test */
    public function can_view_buttons_editar_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim',
            'regional' => '',
            'status' => 'Qualquer',
            'datemin' => $agendamento->dia, 
            'datemax' => $agendamento->dia
        ]))
        ->assertSee('<a href="'.route('agendamentos.edit', $agendamento->idagendamento).'" class="btn btn-sm btn-default">Editar</a>');
    }

    /** @test */
    public function can_view_message_status_in_past_or_today_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
            'status' => Agendamento::STATUS_CANCELADO
        ]);

        $this->get(route('agendamentos.lista'))
        ->assertSeeText(Agendamento::STATUS_CANCELADO)
        ->assertSee('<a href="'.route('agendamentos.edit', $agendamento->idagendamento).'" class="btn btn-sm btn-default">Editar</a>');

        $agendamento2 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $this->get(route('agendamentos.lista'))
        ->assertSeeText(Agendamento::STATUS_NAO_COMPARECEU)
        ->assertSee('<a href="'.route('agendamentos.edit', $agendamento2->idagendamento).'" class="btn btn-sm btn-default">Editar</a>');

        $agendamento3 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
            'status' => Agendamento::STATUS_COMPARECEU,
            'idusuario' => $user->idusuario
        ]);

        $this->get(route('agendamentos.lista'))
        ->assertSeeText(Agendamento::STATUS_COMPARECEU)
        ->assertSee('<a href="'.route('agendamentos.edit', $agendamento3->idagendamento).'" class="btn btn-sm btn-default">Editar</a>')
        ->assertSeeText($user->nome);
    }

    /** @test */
    public function can_view_all_filtros()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('agendamentos.lista'))
        ->assertSeeText('Seccional')
        ->assertSeeText('Status')
        ->assertSeeText('Serviço')
        ->assertSeeText('De')
        ->assertSeeText('Até');
    }

    /** @test */
    public function atendente_and_gerente_seccional_cannot_view_all_filtros()
    {
        $atendente = factory('App\Perfil')->create([
            'idperfil' => 8,
            'nome' => 'Atendimento'
        ]);

        $gerente = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $atendente->idperfil
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
        ]);

        $this->signIn($user);

        $this->get(route('agendamentos.lista'))
        ->assertDontSeeText('Seccional')
        ->assertSeeText('Status')
        ->assertSeeText('Serviço')
        ->assertSeeText('De')
        ->assertSeeText('Até');

        $user2 = factory('App\User')->create([
            'idperfil' => $gerente->idperfil
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user2->idregional,
            'dia' => date('Y-m-d'),
        ]);

        $this->signIn($user2);

        $this->get(route('agendamentos.lista'))
        ->assertDontSeeText('Seccional')
        ->assertSeeText('Status')
        ->assertSeeText('Serviço')
        ->assertSeeText('De')
        ->assertSeeText('Até');
    }

    /** @test */
    public function can_update_status_to_compareceu()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
        ]);

        $dados['idagendamento'] = $agendamento->idagendamento;
        $dados['status'] = Agendamento::STATUS_COMPARECEU;

        $this->put(route('agendamentos.updateStatus'), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $dados['status']
        ]);
    }

    /** @test */
    public function can_update_status_to_nao_compareceu()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
        ]);

        $dados['idagendamento'] = $agendamento->idagendamento;
        $dados['status'] = Agendamento::STATUS_NAO_COMPARECEU;

        $this->put(route('agendamentos.updateStatus'), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $dados['status']
        ]);
    }

    /** @test */
    public function cannot_update_status_to_compareceu_if_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $dados['idagendamento'] = $agendamento->idagendamento;
        $dados['status'] = Agendamento::STATUS_COMPARECEU;

        $this->put(route('agendamentos.updateStatus'), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->get(route('agendamentos.lista'))
        ->assertSee('Status do agendamento não pode ser modificado para '.Agendamento::STATUS_COMPARECEU.' ou '.Agendamento::STATUS_NAO_COMPARECEU.' antes da data agendada');

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $agendamento->status
        ]);
    }

    /** @test */
    public function cannot_update_status_to_nao_compareceu_if_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $dados['idagendamento'] = $agendamento->idagendamento;
        $dados['status'] = Agendamento::STATUS_NAO_COMPARECEU;

        $this->put(route('agendamentos.updateStatus'), $dados)
        ->assertRedirect(route('agendamentos.lista'));

        $this->get(route('agendamentos.lista'))
        ->assertSee('Status do agendamento não pode ser modificado para '.Agendamento::STATUS_COMPARECEU.' ou '.Agendamento::STATUS_NAO_COMPARECEU.' antes da data agendada');

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
            'status' => $agendamento->status
        ]);
    }

    /** @test */
    public function gerente_seccional_can_update_status_if_same_regional()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $this->signIn($user);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados['status'] = Agendamento::STATUS_COMPARECEU;
        $dados['idagendamento'] = $agendamento->idagendamento;

        $this->put(route('agendamentos.updateStatus'), $dados)->assertStatus(302);
        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, $dados['status']);

        $agendamento2 = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $dados['status'] = Agendamento::STATUS_NAO_COMPARECEU;
        $dados['idagendamento'] = $agendamento2->idagendamento;

        $this->put(route('agendamentos.updateStatus'), $dados)->assertStatus(302);
        $this->assertEquals(Agendamento::find($agendamento2->idagendamento)->status, $dados['status']);
    }

    /** @test */
    public function gerente_seccional_cannot_update_status_if_different_regional()
    {        
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $this->signIn($user);

        $agendamento = factory('App\Agendamento')->create([
            'dia' => date('Y-m-d')
        ]);

        $dados['status'] = Agendamento::STATUS_COMPARECEU;
        $dados['idagendamento'] = $agendamento->idagendamento;

        $this->put(route('agendamentos.updateStatus'), $dados)->assertForbidden();
        $this->assertNotEquals(Agendamento::find($agendamento->idagendamento)->status, $dados['status']);

        $agendamento2 = factory('App\Agendamento')->create([
            'dia' => date('Y-m-d')
        ]);

        $dados['status'] = Agendamento::STATUS_NAO_COMPARECEU;
        $dados['idagendamento'] = $agendamento2->idagendamento;

        $this->put(route('agendamentos.updateStatus'), $dados)->assertForbidden();
        $this->assertNotEquals(Agendamento::find($agendamento2->idagendamento)->status, $dados['status']);
    }

    /** @test */
    public function resend_agendamento_mail()
    {
        Mail::fake();

        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertStatus(302);

        Mail::assertSent(AgendamentoMailGuest::class);
    }

    /** @test */
    public function gerente_seccional_can_resend_agendamento_mail_if_same_regional()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $this->signIn($user);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertStatus(302);
    }

    /** @test */
    public function gerente_seccional_cannot_resend_agendamento_mail_if_different_regional()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $this->signIn($user);

        $agendamento = factory('App\Agendamento')->create();

        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertForbidden();
    }

    /** @test */
    public function non_authorized_cannot_resend_agendamento_mail()
    {
        $user = $this->signIn();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertForbidden();
    }

    /** @test */
    public function cannot_resend_agendamento_mail_if_past_or_today_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d')
        ]);

        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertStatus(302);
        $this->get(route('agendamentos.lista'))
        ->assertSeeText('Não pode reenviar email para agendamento de hoje para trás'); 
    }
    
    /** @test */
    public function search_criteria_for_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $this->get(route('agendamentos.busca', ['q' => $agendamento->nome]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->idagendamento]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->cpf]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->email]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->protocolo]))
            ->assertSeeText($agendamento->protocolo); 
            
        $this->get(route('agendamentos.busca', ['q' => 'Critério de busca sem resultado']))
            ->assertDontSeeText($agendamento->protocolo);
    }

    /** @test */
    public function peding_agendamentos_by_role_and_region()
    {
        // Criando usuário Admin. A Regional Sede (idregional = 1) é criada junta
        $admin = $this->signInAsAdmin();

        // Criando regional seccional (idregional != 1)
        $regional_seccional = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Seccional', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Criando regional seccional (idregional != 1) adicional
        $regional_seccional_2 = factory('App\Regional')->create([
            'idregional' => 3,
            'regional' => 'Seccional', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Criando Perfil 'Coordenadoria de Atendimento'
        $coordenadoria_atendimento = factory('App\Perfil')->create([
            'idperfil' => 6,
            'nome' => 'Coordenadoria de Atendimento'
        ]);

        // Criando Perfil 'Gestão Atendimento Sede'
        $gestao_atendimento_sede = factory('App\Perfil')->create([
            'idperfil' => 12,
            'nome' => 'Gestão Atendimento Sede'
        ]);

        // Criando Perfil 'Gestão Atendimento Seccionais'
        $gestao_atendimento_seccional = factory('App\Perfil')->create([
            'idperfil' => 13,
            'nome' => 'Gestão Atendimento Seccionais'
        ]);

        // Criando Perfil 'Atendimento'
        $atendimento = factory('App\Perfil')->create([
            'idperfil' => 8,
            'nome' => 'Atendimento'
        ]);

        // Criando usuário com Perfil 'Coordenadoria de Atendimento'
        $user_coordenadoria_atendimento = factory('App\User')->create([
            'nome' => 'Coordenadoria de Atendimento',
            'idregional' => 1,
            'idperfil' => $coordenadoria_atendimento->idperfil
        ]);

        // Criando usuário com Perfil 'Gestão Atendimento Sede'
        $user_gestao_atendimento_sede = factory('App\User')->create([
            'nome' => 'Gestão Atendimento Sede',
            'idregional' => 1,
            'idperfil' => $gestao_atendimento_sede->idperfil
        ]);

        // Criando usuário com Perfil 'Gestão Atendimento Seccionais'
        $user_gestao_atendimento_seccional = factory('App\User')->create([
            'nome' => 'Gestão Atendimento Seccionais',
            'idregional' => 1,
            'idperfil' => $gestao_atendimento_seccional->idperfil
        ]);

        // Criando usuário com Perfil 'Atendimento'
        $user_atendimento = factory('App\User')->create([
            'nome' => 'Atendimento',
            'idregional' => 2,
            'idperfil' => $atendimento->idperfil
        ]);

        // Criando Agendamento pendente no passado na sede
        $agendamento_sede_pendente = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000001'
        ]);

        // Criando Agendamento concluído no passado na sede
        $agendamento_sede_concluido = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000002',
            'status' => Agendamento::STATUS_COMPARECEU
        ]);

        // Criando Agendamento pendente no futuro na sede
        $agendamento_sede_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000003'
        ]);
        
        // Criando Agendamento pendente no passado na seccional
        $agendamento_seccional_pendente = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000004'
        ]);

        // Criando Agendamento concluído no passado na seccional
        $agendamento_seccional_concluido = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000005',
            'status' => Agendamento::STATUS_COMPARECEU
        ]);

        // Criando Agendamento pendente no futuro na seccional
        $agendamento_seccional_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000006'
        ]);

        // Criando Agendamento pendente no passado na seccional
        $agendamento_seccional_pendente_2 = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional_2->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000007'
        ]);

        // Testando listagem com usuário Admin

        // Usuário deve ver todos os Agendamentos pendentes do passado (tanto sede como seccional)
        $this->get(route('agendamentos.pendentes'))
            ->assertSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertSeeText('AGE-000007');

        // Testando listagem com usuário 'Coordenadoria de Atendimento'
        $this->signIn($user_coordenadoria_atendimento);

        // Usuário deve ver todos os Agendamentos pendentes do passado (tanto sede como seccional)
        $this->get(route('agendamentos.pendentes'))
            ->assertSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertSeeText('AGE-000007');

        // Testando listagem com usuário 'Gestão Atendimento Sede' 
        $this->signIn($user_gestao_atendimento_sede);

        // Usuário deve ver apenas os Agendamentos pendentes do passado da sede
        $this->get(route('agendamentos.pendentes'))
            ->assertSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertDontSeeText('AGE-000007');

        // Testando listagem com usuário 'Gestão Atendimento Seccionais'
        $this->signIn($user_gestao_atendimento_seccional);

        // Usuário deve ver apenas os Agendamentos pendentes do passado de todas as seccionais
        $this->get(route('agendamentos.pendentes'))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertSeeText('AGE-000007');

        // Testando listagem com usuário 'Atendimento'
        $this->signIn($user_atendimento);

        // Usuário deve ver apenas os Agendamentos pendentes do passado da sua regional
        $this->get(route('agendamentos.pendentes'))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006')
            ->assertDontSeeText('AGE-000007');
    }

    /** @test */
    public function agendamentos_filter()
    {
        // Criando usuário Admin. A Regional Sede (idregional = 1) é criada junta
        $admin = $this->signInAsAdmin();

        // Criando regional seccional (idregional != 1)
        $regional_seccional = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Seccional', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Criando Agendamento pendente no passado na sede
        $agendamento_sede_pendente = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000001'
        ]);

        // Criando Agendamento concluído no passado na sede
        $agendamento_sede_concluido = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000002',
            'status' => Agendamento::STATUS_COMPARECEU,
            'idusuario' => 1
        ]);

        // Criando Agendamento pendente no futuro na sede
        $agendamento_sede_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => 1,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000003'
        ]);
        
        // Criando Agendamento pendente no passado na seccional
        $agendamento_seccional_pendente = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000004'
        ]);

        // Criando Agendamento concluído no passado na seccional
        $agendamento_seccional_concluido = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000005',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        // Criando Agendamento pendente no futuro na seccional
        $agendamento_seccional_pendente_futuro = factory('App\Agendamento')->create([
            'idregional' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'hora' => '10:00',
            'protocolo' => 'AGE-000006'
        ]);

        // Listando todos os agendamentos (qualquer regional, status e datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => '', 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))->assertSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertSeeText('AGE-000005')
            ->assertSeeText('AGE-000006');

        // Listando todos os agendamentos da Sede (qualquer status e datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando apenas os agendamentos com status "Compareceu" da Sede (datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => Agendamento::STATUS_COMPARECEU, 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando apenas agendamentos da Sede do dia -1
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('-1 day'))
        ]))
            ->assertSeeText('AGE-000001') 
            ->assertSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando nenhum o agendamentos da Sede por causa de data sem agendamento
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => 1, 
            'status' => Agendamento::STATUS_COMPARECEU, 
            'datemin' => date('Y-m-d'), 
            'datemax' => date('Y-m-d')
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');


        // Listando todos os agendamentos da Seccional (qualquer status e datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional, 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertSeeText('AGE-000004') 
            ->assertSeeText('AGE-000005')
            ->assertSeeText('AGE-000006');

        // Listando apenas os agendamentos com status "Não Compareceu" da Seccional (datas cobrindos todos os agendamentos)
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional,
            'status' => Agendamento::STATUS_NAO_COMPARECEU, 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');

        // Listando apenas agendamentos da Seccional do dia +1
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional,
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('+1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertSeeText('AGE-000006');

        // Listando nenhum o agendamentos da Seccional por causa de data sem agendamento
        $this->get(route('agendamentos.filtro', [
            'filtro' => 'sim', 
            'regional' => $regional_seccional->idregional,
            'status' => Agendamento::STATUS_COMPARECEU, 
            'datemin' => date('Y-m-d'), 
            'datemax' => date('Y-m-d')
        ]))
            ->assertDontSeeText('AGE-000001') 
            ->assertDontSeeText('AGE-000002') 
            ->assertDontSeeText('AGE-000003') 
            ->assertDontSeeText('AGE-000004') 
            ->assertDontSeeText('AGE-000005')
            ->assertDontSeeText('AGE-000006');
    }

    /** 
     * =======================================================================================================
     * TESTES AGENDAMENTO BLOQUEIO
     * =======================================================================================================
     */



    // /** 
    //  * =======================================================================================================
    //  * TESTES NO PORTAL
    //  * =======================================================================================================
    //  */

    // /** @test 
    //  * 
    //  * Testando acesso a página de criação de Agendamentos.
    // */
    // public function access_agendamentos_from_portal()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $this->get(route('agendamentosite.formview'))->assertOk();
    // }

    // /** @test 
    //  * 
    //  * Testando acesso a página de consulta de Agendamentos.
    // */
    // public function access_search_agendamentos_from_portal()
    // {
    //     $this->get(route('agendamentosite.consultaView'))->assertOk();
    // }

    // /** @test 
    //  * 
    //  * Testando criação de agendamento pelo Portal.
    //  * Verificando o envio de email.
    // */
    // public function agendamento_can_be_created_on_portal()
    // {
    //     Mail::fake();

    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'termo' => 'on'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento)->assertOk();

    //     $this->assertEquals(Agendamento::count(), 1);

    //     Mail::assertQueued(AgendamentoMailGuest::class);
    // }

    // /** @test 
    //  * 
    //  * Testando consulta de Agendamento pelo protocolo no Portal.
    // */
    // public function search_agendamento_on_portal()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     $this->get(route('agendamentosite.consulta', ['protocolo' => 'XXXXXX']))->assertSee($agendamento->protocolo);
    // }

    // /** @test 
    //  * 
    //  * Testando cancelamento de Agendamento no Portal.
    // */
    // public function cancel_agendamento_on_portal()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     $this->put(route('agendamentosite.cancelamento'), [
    //         'idagendamento' => $agendamento->idagendamento,
    //         'protocolo' => $agendamento->protocolo, 
    //         'cpf' => $agendamento->cpf
    //     ]);

    //     $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, 'Cancelado');
    // }

    // /** @test 
    //  * 
    //  * Testando a API que retorna os horários de acordo com regional e dia.
    // */
    // public function retrieve_agendamentos_by_api()
    // {
    //     // regional_1 permite 2 agendamentos por horário
    //     $regional_1 = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $regional_2 = factory('App\Regional')->create([
    //         'idregional' => 2,
    //         'regional' => 'Campinas', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Registrando um agendamento na regional_1 às 10:00
    //     $agendamento_1 = factory('App\Agendamento')->create([
    //         'idregional' => $regional_1->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     // Verificando que ainda é possível agendar na regional_1 às 10:00 e em todos os outros horários
    //     $this->get(route('agendamentosite.checaHorarios', ['idregional' => 1, 'dia' => date('d/m/Y', strtotime('+1 day'))]))
    //         ->assertSee('10:00')
    //         ->assertSee('11:00')
    //         ->assertSee('12:00')
    //         ->assertSee('13:00')
    //         ->assertSee('14:00');

    //     // Registrando mais um agendamento na regional_1 às 10:00
    //     $agendamento_2 = factory('App\Agendamento')->create([
    //         'idregional' => $regional_1->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-YYYYYY'
    //     ]);

    //     // Verificando que não é mais possível agendar na regional_1 às 10:00, mas ainda é possível em todos os outros horários
    //     $this->get(route('agendamentosite.checaHorarios', ['idregional' => 1, 'dia' => date('d/m/Y', strtotime('+1 day'))]))
    //         ->assertDontSee('10:00')
    //         ->assertSee('11:00')
    //         ->assertSee('12:00')
    //         ->assertSee('13:00')
    //         ->assertSee('14:00');

    //     // Verificando que o horário as 10:00 está disponível na outra regional "regional_2"
    //     $this->get(route('agendamentosite.checaHorarios', ['idregional' => 2, 'dia' => date('d/m/Y', strtotime('+1 day'))]))
    //         ->assertSee('10:00')
    //         ->assertSee('11:00')
    //         ->assertSee('12:00')
    //         ->assertSee('13:00')
    //         ->assertSee('14:00');
    // }

    // /** @test 
    //  * 
    //  * Testando campos obrigatórios para criação de Agendamento.
    //  * 
    //  * TODO adicionar testes para valores pré-definidos (tiposervico, regional)
    // */
    // public function agendamento_missing_mandatory_input_cannot_be_created()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento = factory('App\Agendamento')->raw([
    //         'idregional' => null,
    //         'nome' => null,
    //         'cpf' => null,
    //         'email' => null,
    //         'celular' => null,
    //         'dia' => null,
    //         'hora' => null
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento)->assertSessionHasErrors([
    //         'nome',
    //         'cpf',
    //         'email',
    //         'celular',
    //         'dia',
    //         'hora'
    //     ]);

    //     $this->assertEquals(Agendamento::count(), 0);
    // }

    // /** @test 
    //  * 
    //  * Testando validação de CPF na criação de Agendamento.
    // */
    // public function agendamento_with_invalid_cpf_cannot_be_created()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'cpf' => '00.000.000/0000-00',
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento)->assertSessionHasErrors(['cpf',]);

    //     $this->assertEquals(Agendamento::count(), 0);
    // }

    // /** @test 
    //  * 
    //  * Testando validação que permite que um CPF possa ser usado apenas em dois Agendamentos no mesmo dia.
    // */
    // public function agendamento_with_same_cpf_can_be_created_two_time_on_same_day()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Primeiro Agendamento do dia com o CPF
    //     $agendamento_1 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     // Segundo Agendamento do dia com o mesmo CPF
    //     $agendamento_2 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '11:00',
    //         'protocolo' => 'AGE-YYYYYY'
    //     ]);

    //     // Terceiro Agendamento do dia com o mesmo CPF
    //     $agendamento_3 = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '12:00',
    //         'termo' => 'on'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento_3)->assertStatus(500);

    //     // Apenas os dois primeiros devem estar no banco de dados
    //     $this->assertEquals(Agendamento::count(), 2);
    // }

    // /** @test 
    //  * 
    //  * Testando validação que bloqueia Agendamento com CPF que deixou de comparecer três vezes em Agendamentos anteriores
    //  * nos últimos 90 dias.
    // */
    // public function agendamento_with_cpf_that_didnt_show_up_three_times_in_90_days()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Primeiro Agendamento em que a pessoa com o CPF não compareceu
    //     $agendamento_1 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d'),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX',
    //         'status' => Agendamento::STATUS_NAO_COMPARECEU
    //     ]);

    //     // Segundo Agendamento em que a pessoa com o CPF não compareceu
    //     $agendamento_2 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d'),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-YYYYYY',
    //         'status' => Agendamento::STATUS_NAO_COMPARECEU
    //     ]);

    //     // Terceiro Agendamento em que a pessoa com o CPF não compareceu
    //     $agendamento_3 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d'),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-WWWWWW',
    //         'status' => Agendamento::STATUS_NAO_COMPARECEU
    //     ]);

    //     // Quarto Agendamento com o CPF da pessoa que não compareceu três vezes
    //     $agendamento_4 = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'termo' => 'on'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento_4)->assertStatus(405);

    //     // Apenas os três primeiros Agendamentos devem estar no banco de dados
    //     $this->assertEquals(Agendamento::count(), 3);
    // }

    // /** @test 
    //  * 
    //  * Testando validação que permite Agendamento com CPF que deixou de comparecer três vezes em Agendamentos anteriores
    //  * com mais de 90 dias.
    // */
    // public function agendamento_with_cpf_that_didnt_show_up_three_times_in_more_than_90_days()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Primeiro Agendamento em que a pessoa com o CPF não compareceu (91 dias atrás)
    //     $agendamento_1 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('-91 days')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX',
    //         'status' => Agendamento::STATUS_NAO_COMPARECEU
    //     ]);

    //     // Segundo Agendamento em que a pessoa com o CPF não compareceu (91 dias atrás)
    //     $agendamento_2 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('-91 days')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-YYYYYY',
    //         'status' => Agendamento::STATUS_NAO_COMPARECEU
    //     ]);

    //     // Terceiro Agendamento em que a pessoa com o CPF não compareceu (91 dias atrás)
    //     $agendamento_3 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('-91 days')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-WWWWWW',
    //         'status' => Agendamento::STATUS_NAO_COMPARECEU
    //     ]);

    //     // Quarto Agendamento com o CPF da pessoa que não compareceu três vezes
    //     $agendamento_4 = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'termo' => 'on'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento_4)->assertOk();

    //     // Todos os agendamentos devem estar presentes
    //     $this->assertEquals(Agendamento::count(), 4);
    // }

    // /** @test 
    //  * 
    //  * Testando validação que bloqueia Agendamento com data anterior a atual.
    // */
    // public function agendamento_with_older_date_cannot_be_created()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Usando data -1
    //     $agendamento = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('-1 day')),
    //         'hora' => '10:00',
    //         'termo' => 'on'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento)->assertStatus(500);

    //     $this->assertEquals(Agendamento::count(), 0);
    // }

    // /** @test 
    //  * 
    //  * Testando validação que bloqueia Agendamento quando o horário requerido não está disponível.
    //  * 
    //  * TODO - adicionar uma mensagem ao erro
    // */
    // public function agendamento_with_no_available_time()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 1, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Ocupando o único horário disponível às 10:00
    //     $agendamento_1 = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     // Tentando criar Agendamento novamente às 10:00
    //     $agendamento_2 = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'termo' =>'on'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento_2)->assertStatus(500);

    //     // Apenas o primeiro Agendamento deve estar presente no banco de dados
    //     $this->assertEquals(Agendamento::count(), 1);

    //     // Nova regional onde não há zero atendimentos por horário
    //     $regional_2 = factory('App\Regional')->create([
    //         'idregional' => 2,
    //         'regional' => 'Campinas', 
    //         'ageporhorario' => 0, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento_3 = factory('App\Agendamento')->raw([
    //         'idregional' => $regional_2->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'termo' => 'on'
    //     ]);

    //     $this->post(route('agendamentosite.store'), $agendamento_3)->assertStatus(500);

    //     // Banco de dados deve continuar apenas com um agendamento
    //     $this->assertEquals(Agendamento::count(), 1);
    // }

    // /** @test 
    //  * 
    //  * Testando consulta de Agendamento com protocolo errado no Portal.
    // */
    // public function wrong_protocol_search_agendamento_on_portal()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     // Usando protocolo diferente do usado na criação do Agendamento
    //     $this->get(route('agendamentosite.consulta', ['protocolo' => 'YYYYYY']))->assertDontSee($agendamento->protocolo);
    // }

    // /** @test 
    //  * 
    //  * Testando bloqueio de cancelamento de Agendamento no Portal quando CPF fornecido não bate com protocolo.
    // */
    // public function cancel_agendamento_with_wrong_cpf_on_portal()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     $agendamento = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     // Usando CPF diferente na consulta
    //     $this->put(route('agendamentosite.cancelamento'), [
    //         'idagendamento' => $agendamento->idagendamento,
    //         'protocolo' => $agendamento->protocolo, 
    //         'cpf' => '000.000.000-00'
    //     ]);

    //     // Garantir que o status do Agendamento é nulo e não "Cancelado"
    //     $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, null);
    // }

    // /** @test 
    //  * 
    //  * Testando bloqueio de cancelamento de Agendamento no Portal quando cancelamento é feito no mesmo dia do agendamento.
    // */
    // public function cancel_agendamento_on_agendamento_day_on_portal()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Criando Agendamento no dia atual para tentar cancelar no mesmo dia
    //     $agendamento = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d'),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     $this->put(route('agendamentosite.cancelamento'), [
    //         'idagendamento' => $agendamento->idagendamento,
    //         'protocolo' => $agendamento->protocolo, 
    //         'cpf' => $agendamento->cpf
    //     ])->assertStatus(302);

    //     // Garantir que o status do Agendamento é nulo e não "Cancelado"
    //     $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, null);
    // }

    // /** @test 
    //  * 
    //  * Mudança de status do Agendamento pelos botões "Confirmar"/"Não Compareceu" na tabela que lista os Agendamentos 
    //  * não é permitida antes da data do agendamento.
    // */
    // public function agendamento_cannot_update_from_table_before_agendamento_date()
    // {
    //     $user = $this->signInAsAdmin();

    //     $agendamento = factory('App\Agendamento')->create([
    //         'idregional' => $user->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     $this->put(route('agendamentos.updateStatus'), ['idagendamento' => $agendamento->idagendamento, 'status' => Agendamento::STATUS_COMPARECEU])->assertStatus(302);

    //     // Checa se status continua nulo (siguinifica que o agendamento está pendente)
    //     $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, null);

    //     $this->put(route('agendamentos.updateStatus'), ['idagendamento' => $agendamento->idagendamento, 'status' => Agendamento::STATUS_NAO_COMPARECEU])->assertStatus(302);

    //     // Checa se status continua nulo (siguinifica que o agendamento está pendente)
    //     $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, null);
    // }
    // /** @test 
    //  * 
    //  * Agendamento não pode ser criado no memso horário com mesmo CPF/CNPJ.
    // */
    // public function cannot_create_agendamento_on_same_schedule_with_same_cpf_cnpj()
    // {
    //     $regional = factory('App\Regional')->create([
    //         'idregional' => 1,
    //         'regional' => 'São Paulo', 
    //         'ageporhorario' => 2, 
    //         'horariosage' => '10:00,11:00,12:00,13:00,14:00'
    //     ]);

    //     // Criando Agendamento no dia atual para tentar cancelar no mesmo dia
    //     $agendamento = factory('App\Agendamento')->create([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'protocolo' => 'AGE-XXXXXX'
    //     ]);

    //     $dados = factory('App\Agendamento')->raw([
    //         'idregional' => $regional->idregional,
    //         'dia' => date('Y-m-d', strtotime('+1 day')),
    //         'hora' => '10:00',
    //         'termo' => 'on'
    //     ]);

    //     // Checa se ao tentar salvar o agendamento com mesmo horário e CPF retorna erro 500
    //     $this->post(route('agendamentosite.store'), $dados)->assertStatus(500);
    // }

    /** 
     * =======================================================================================================
     * SERVIÇO DO PLANTÃO JURÍDICO
     * =======================================================================================================
     */
    
    /** @test */
    public function can_view_plantao_juridico_option_when_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.formview'))->assertSee('Plantão Jurídico');
    }

    /** @test */
    public function cannot_view_plantao_juridico_option_when_disabled_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 0
        ]);

        $this->get(route('agendamentosite.formview'))->assertDontSee('Plantão Jurídico');
    }

    /** @test */
    public function can_create_agendamento_with_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->get(route('agendamentosite.formview'))->assertSee('Plantão Jurídico');

        $this->post(route('agendamentosite.store'), $dados)->assertOk();
        $this->assertDatabaseHas('agendamentos', [
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function can_create_equal_qtd_agendamento_and_advogados_in_same_hour_with_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 2
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertOk();
        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => 1,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);

        $dados = factory('App\Agendamento')->raw([
            'cpf' => '515.056.080-40',
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertOk();
        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => 2,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_different_qtd_agendamento_and_advogados_in_same_hour_with_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertOk();
        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => 1,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);

        $dados = factory('App\Agendamento')->raw([
            'cpf' => '515.056.080-40',
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(500)
        ->assertSee('A hora para a regional escolhida não é válida para o serviço Plantão Jurídico');

        $this->assertDatabaseMissing('agendamentos', [
            'idagendamento' => 2,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_agendamento_with_disabled_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create();
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->get(route('agendamentosite.formview'))->assertDontSee('Plantão Jurídico');

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(500)
        ->assertSeeText('A regional escolhida não é válida para o serviço');

        $this->assertDatabaseMissing('agendamentos', [
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_agendamento_with_expired_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'dataInicial' => Carbon::today()->format('Y-m-d'),
            'dataFinal' => Carbon::today()->format('Y-m-d'),
            'qtd_advogados' => 1
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(500)
        ->assertSeeText('Não é permitido criar agendamento no passado.');
        
        $this->assertDatabaseMissing('agendamentos', [
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_agendamento_with_date_different_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => Carbon::parse($plantao->dataFinal)->addDay()->format('Y-m-d'),
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(500)
        ->assertSeeText('A data para a regional escolhida não é válida para o serviço Plantão Jurídico');
        
        $this->assertDatabaseMissing('agendamentos', [
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_agendamento_with_hour_different_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataFinal,
            'hora' => '09:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(500)
        ->assertSeeText('A hora para a regional escolhida não é válida para o serviço Plantão Jurídico');
        
        $this->assertDatabaseMissing('agendamentos', [
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_agendamento_with_full_date_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'horarios' => '10:00',
            'qtd_advogados' => 1
        ]);
        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dados = factory('App\Agendamento')->raw([
            'cpf' => '274.461.700-85',
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(500)
        ->assertSeeText('A data escolhida para a regional está indiponível atualmente para o serviço Plantão Jurídico');
        
        $this->assertDatabaseMissing('agendamentos', [
            'idagendamento' => 2,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_two_or_more_agendamentos_with_same_cpf_and_same_regional_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '11:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(500)
        ->assertSeeText('Durante o período deste plantão jurídico é permitido apenas 1 agendamento por cpf');
        
        $this->assertDatabaseMissing('agendamentos', [
            'idagendamento' => 2,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function can_create_two_or_more_agendamentos_with_same_cpf_and_differents_regional_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $plantao2 = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao2->idregional,
            'servico' => 'Plantão Jurídico',
            'pessoa' => 'Ambas',
            'dia' => $plantao->dataInicial,
            'hora' => '11:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertOk();
        
        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => 1,
            'idregional' => $plantao->idregional,
            'cpf' => $agendamento['cpf'],
            'hora' => $agendamento['hora'],
            'tiposervico' => $agendamento['tiposervico']
        ]);
        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => 2,
            'idregional' => $plantao2->idregional,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function get_disabled_regionais_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $plantao2 = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 0
        ]);

        $this->get(route('agendamentosite.regionaisExcluidasPlantaoJuridico'))
        ->assertJson([$plantao2->idregional]);
    }

    /** @test */
    public function get_horarios_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.checaHorarios', [
            'idregional' => $plantao->idregional,
            'dia' => $plantao->dataInicial,
            'servico' => 'Plantão Jurídico'
        ]))
        ->assertJson(explode(',', $plantao->horarios));
    }

    /** @test */
    public function get_empty_array_horarios_when_disabled_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('agendamentosite.checaHorarios', [
            'idregional' => $plantao->idregional,
            'dia' => $plantao->dataInicial,
            'servico' => 'Plantão Jurídico'
        ]))
        ->assertJson([]);
    }

    /** @test */
    public function remove_hours_when_full_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $horarios = explode(',', $plantao->horarios);
        unset($horarios[array_search('10:00', $horarios)]);

        $this->get(route('agendamentosite.checaHorarios', [
            'idregional' => $plantao->idregional,
            'dia' => $plantao->dataFinal,
            'servico' => 'Plantão Jurídico'
        ]))
        ->assertJson($horarios);
    }

    /** @test */
    public function get_lotado_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'horarios' => '10:00',
            'qtd_advogados' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dia = Carbon::parse($plantao->dataFinal);
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.checaMes', [
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico'
        ]))
        ->assertJson([$lotado]);
    }

    /** @test */
    public function get_empty_array_lotado_when_disabled_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('agendamentosite.checaMes', [
            'idregional' => $plantao->idregional,
            'servico' => 'Plantão Jurídico'
        ]))
        ->assertJson([]);
    }

    /** @test */
    public function get_datas_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.datasPlantaoJuridico', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([$plantao->dataInicial, $plantao->dataFinal]);
    }

    /** @test */
    public function get_datas_when_data_inicial_less_then_tomorrow_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'dataInicial' => date('Y-m-d'),
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.datasPlantaoJuridico', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([null, $plantao->dataFinal]);
    }

    /** @test */
    public function get_datas_when_data_final_less_then_tomorrow_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'dataInicial' => date('Y-m-d'),
            'dataFinal' => date('Y-m-d'),
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.datasPlantaoJuridico', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([null, null]);
    }

    /** @test */
    public function get_empty_array_datas_when_disabled_planta_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('agendamentosite.datasPlantaoJuridico', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([]);
    }
}