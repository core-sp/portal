<?php

namespace Tests\Feature;

use App\Permissao;
use Tests\TestCase;
use App\Agendamento;
use App\Mail\AgendamentoMailGuest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AgendamentoTest extends TestCase
{
    use RefreshDatabase;

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
        $this->get(route('agendamentobloqueios.busca'))->assertRedirect(route('login'));
        $this->get(route('agendamentobloqueios.criar'))->assertRedirect(route('login'));
        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertRedirect(route('login'));
        $this->post(route('agendamentobloqueios.store'))->assertRedirect(route('login'));
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio))->assertRedirect(route('login'));
        $this->delete(route('agendamentobloqueios.delete', $bloqueio->idagendamentobloqueio))->assertRedirect(route('login'));
        $this->get(route('agendamentobloqueios.dadosAjax'), ['idregional' => $bloqueio->idregional])->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $agendamento = factory('App\Agendamento')->create();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['10:00']
        ]);

        $bloqueioSalvo = factory('App\AgendamentoBloqueio')->create();

        $this->get(route('agendamentos.lista'))->assertForbidden();
        $this->get(route('agendamentos.busca'))->assertForbidden();
        $this->get(route('agendamentos.filtro'))->assertForbidden();
        $this->get(route('agendamentos.pendentes'))->assertForbidden();
        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertForbidden();
        $this->put(route('agendamentos.edit', $agendamento->idagendamento), $agendamento->toArray())->assertForbidden();
        $this->post(route('agendamentos.reenviarEmail', $agendamento->idagendamento))->assertForbidden();

        $this->get(route('agendamentobloqueios.lista'))->assertForbidden();
        $this->get(route('agendamentobloqueios.busca'))->assertForbidden();
        $this->get(route('agendamentobloqueios.criar'))->assertForbidden();
        $this->get(route('agendamentobloqueios.edit', $bloqueioSalvo->idagendamentobloqueio))->assertForbidden();
        $this->post(route('agendamentobloqueios.store'), $bloqueio)->assertForbidden();

        $bloqueioSalvo->horarios = ['10:00'];
        $this->put(route('agendamentobloqueios.update', $bloqueioSalvo->idagendamentobloqueio), $bloqueioSalvo->toArray())->assertForbidden();
        $this->delete(route('agendamentobloqueios.delete', $bloqueioSalvo->idagendamentobloqueio))->assertForbidden();
        $this->get(route('agendamentobloqueios.dadosAjax'), ['idregional' => $bloqueioSalvo->idregional])->assertForbidden();
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

    /** @test */
    public function authorized_users_can_edit_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create();

        $agendamento->nome = 'Novo Nome';
        $agendamento->email = 'novoemail@teste.com';
        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)->assertStatus(302);

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento->idagendamento,
            'nome' => $agendamento->nome,
            'email' => $agendamento->email,
            'cpf' => $agendamento->cpf,
            'celular' => $agendamento->celular,
        ]);
    }

    /** @test */
    public function log_is_generated_when_agendamento_is_edited()
    {
        $user = $this->signInAsAdmin();
        $agendamento = factory('App\Agendamento')->create();

        $agendamento->nome = 'Novo Nome';
        $agendamento->email = 'novoemail@teste.com';
        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;

        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('agendamento', $log);
        $this->assertStringContainsString('editou', $log);
    }

    /** @test */
    public function cannot_edit_agendamento_without_input_hidden_antigo()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create();

        $agendamento->email = 'novoemail@teste.com';

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $agendamento->toArray())
        ->assertStatus(400)
        ->assertSee('Erro por falta de campo no request');

        $this->assertDatabaseMissing('agendamentos', [
            'email' => $agendamento->email,
        ]);
    }

    /** @test */
    public function cannot_edit_agendamento_with_input_hidden_antigo_wrong_value()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create();

        $agendamento->email = 'novoemail@teste.com';
        $dados = $agendamento->toArray();
        $dados['antigo'] = 1;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertStatus(400)
        ->assertSee('Erro na validação de campo no request');

        $this->assertDatabaseMissing('agendamentos', [
            'email' => $agendamento->email,
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'dia' => date('Y-m-d')
        ]);

        $agendamento->email = 'novoemail@teste.com';
        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertStatus(400)
        ->assertSee('Erro na validação de campo no request');

        $this->assertDatabaseMissing('agendamentos', [
            'email' => $agendamento->email,
        ]);
    }

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
        Permissao::find(28)->update(['perfis' => '1,21']);

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
        Permissao::find(28)->update(['perfis' => '1,21']);

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
    public function cannot_edit_wrong_email_in_tomorrow_agendamento()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional
        ]);

        $agendamento->email = 'teste.com';
        $dados = $agendamento->toArray();
        $dados['antigo'] = 0;

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))->assertOk();
        $this->put(route('agendamentos.update', $agendamento->idagendamento), $dados)
        ->assertSessionHasErrors(['email']);
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
    public function can_view_only_atendentes_by_regional()
    {
        $user = $this->signInAsAdmin();

        $perfilAtendente = factory('App\Perfil')->create([
            'idperfil' => 8,
            'nome' => 'Atendimento',
        ]);

        $atendente = factory('App\User')->create([
            'idperfil' => $perfilAtendente->idperfil,
        ]);

        $atendente2 = factory('App\User')->create([
            'idperfil' => $perfilAtendente->idperfil,
            'nome' => 'Usuário 2'
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $this->get(route('agendamentos.edit', $agendamento->idagendamento))
        ->assertSee('Ninguém')
        ->assertSee($atendente->nome)
        ->assertDontSee($atendente2->nome);
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
        Permissao::find(27)->update(['perfis' => '1,8']);

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
        Permissao::find(27)->update(['perfis' => '1,21']);

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
    public function log_is_generated_when_agendamento_is_status_edited_to_compareceu()
    {
        $user = $this->signInAsAdmin();
        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
        ]);

        $dados['idagendamento'] = $agendamento->idagendamento;
        $dados['status'] = Agendamento::STATUS_COMPARECEU;

        $this->put(route('agendamentos.updateStatus'), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('agendamento', $log);
        $this->assertStringContainsString('confirmou presença', $log);
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
    public function log_is_generated_when_agendamento_is_status_edited_to_nao_compareceu()
    {
        $user = $this->signInAsAdmin();
        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
            'dia' => date('Y-m-d'),
        ]);

        $dados['idagendamento'] = $agendamento->idagendamento;
        $dados['status'] = Agendamento::STATUS_NAO_COMPARECEU;

        $this->put(route('agendamentos.updateStatus'), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('agendamento', $log);
        $this->assertStringContainsString('confirmou falta', $log);
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
        Permissao::find(27)->update(['perfis' => '1,21']);

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
        Permissao::find(28)->update(['perfis' => '1,21']);

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

        Mail::assertQueued(AgendamentoMailGuest::class);
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
        Permissao::find(28)->update(['perfis' => '1,21']);

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
        Permissao::find(28)->update(['perfis' => '1,21']);

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
    public function search_criteria_for_agendamento_for_profiles_other_than_atendente_and_gerSeccional()
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
    public function search_criteria_for_agendamento_atendente()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 8
        ]);
        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $user = $this->signIn($user);
        Permissao::find(27)->update(['perfis' => '1,8']);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $agendamento2 = factory('App\Agendamento')->create([
            'hora' => '11:00',
            'protocolo' => 'AGE-YYYYYY'
        ]);

        $this->get(route('agendamentos.busca', ['q' => $agendamento->nome]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->idagendamento]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);

        $this->get(route('agendamentos.busca', ['q' => $agendamento->cpf]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->email]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->protocolo]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);
            
        $this->get(route('agendamentos.busca', ['q' => 'Critério de busca sem resultado']))
            ->assertDontSeeText($agendamento->protocolo);
    }

    /** @test */
    public function search_criteria_for_agendamento_gerente_seccional()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21
        ]);
        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $user = $this->signIn($user);
        Permissao::find(27)->update(['perfis' => '1,21']);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $user->idregional,
        ]);

        $agendamento2 = factory('App\Agendamento')->create([
            'hora' => '11:00',
            'protocolo' => 'AGE-YYYYYY'
        ]);

        $this->get(route('agendamentos.busca', ['q' => $agendamento->nome]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->idagendamento]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);

        $this->get(route('agendamentos.busca', ['q' => $agendamento->cpf]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->email]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('agendamentos.busca', ['q' => $agendamento->protocolo]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);
            
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
        Permissao::find(27)->update(['perfis' => '1,6']);

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
        Permissao::find(27)->update(['perfis' => '1,12']);

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
        Permissao::find(27)->update(['perfis' => '1,13']);

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
        Permissao::find(27)->update(['perfis' => '1,8']);

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
            'regional' => 'Todas', 
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

        $this->get(route('agendamentos.filtro', [
            'regional' => $regional_seccional->idregional,
            'status' => Agendamento::STATUS_COMPARECEU, 
            'datemin' => now()->addDay()->format('Y-m-d'), 
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

    /** @test */
    public function can_create_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['10:00', '11:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)->assertRedirect(route('agendamentobloqueios.lista'));
        $this->assertDatabaseHas('agendamento_bloqueios', [
            'idagendamentobloqueio' => 1,
            'diainicio' => $bloqueio['diainicio'],
            'diatermino' => $bloqueio['diatermino'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'qtd_atendentes' => $bloqueio['qtd_atendentes'],
            'idregional' => $bloqueio['idregional']
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_when_idregional_14()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => factory('App\Regional')->create([
                'idregional' => 14
            ]),
            'horarios' => ['10:00', '11:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'idregional',
        ]);
    }

    /** @test */
    public function can_create_bloqueio_all_day_for_all_regionais_with_0_atendentes()
    {
        $user = $this->signInAsAdmin();
        $regionais = factory('App\Regional', 12)->create();
        
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => 'Todas',
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)->assertRedirect(route('agendamentobloqueios.lista'));
        $all = \App\Regional::count() - 1;
        $this->assertEquals(\App\AgendamentoBloqueio::count(), $all);
        $this->assertEquals(\App\AgendamentoBloqueio::find(1)->diatermino, $bloqueio['diatermino']);
    }

    /** @test */
    public function can_create_bloqueio_all_day_for_all_regionais_with_0_atendentes_and_diatermino_null()
    {
        $user = $this->signInAsAdmin();
        $regionais = factory('App\Regional', 12)->create();
        
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => 'Todas',
            'diatermino' => null
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)->assertRedirect(route('agendamentobloqueios.lista'));
        $all = \App\Regional::count() - 1;
        $this->assertEquals(\App\AgendamentoBloqueio::count(), $all);
        $this->assertEquals(\App\AgendamentoBloqueio::find(1)->diatermino, $bloqueio['diatermino']);
    }

    /** @test */
    public function cannot_create_bloqueio_all_day_for_all_regionais_with_qtd_atendentes_greater_than_0()
    {
        $user = $this->signInAsAdmin();
        $regionais = factory('App\Regional', 12)->create();
        
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => 'Todas',
            'qtd_atendentes' => 1
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'qtd_atendentes',
        ]);

        $this->assertNotEquals(\App\AgendamentoBloqueio::count(), \App\Regional::count());
    }

    /** @test */
    public function can_create_bloqueio_with_qtd_atendentes_greater_than_0()
    {
        $user = $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => $regional->idregional,
            'qtd_atendentes' => $regional->ageporhorario - 1,
            'horarios' => ['10:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)->assertRedirect(route('agendamentobloqueios.lista'));
        $this->assertDatabaseHas('agendamento_bloqueios', [
            'idagendamentobloqueio' => 1,
            'diainicio' => $bloqueio['diainicio'],
            'diatermino' => $bloqueio['diatermino'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'qtd_atendentes' => $bloqueio['qtd_atendentes'],
            'idregional' => $bloqueio['idregional']
        ]);
    }

    /** @test */
    public function can_create_bloqueio_without_dia_termino()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['10:00', '11:00'],
            'diatermino' => null
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)->assertRedirect(route('agendamentobloqueios.lista'));
        $this->assertDatabaseHas('agendamento_bloqueios', [
            'idagendamentobloqueio' => 1,
            'diainicio' => $bloqueio['diainicio'],
            'diatermino' => $bloqueio['diatermino'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'qtd_atendentes' => $bloqueio['qtd_atendentes'],
            'idregional' => $bloqueio['idregional']
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_created()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['10:00', '11:00'],
        ]);

        $this->post(route('agendamentobloqueios.store'), $bloqueio);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('bloqueio de agendamento', $log);
        $this->assertStringContainsString('criou', $log);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_created_with_option_todas()
    {
        $user = $this->signInAsAdmin();
        $regionais = factory('App\Regional', 5)->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => 'Todas',
        ]);

        $this->post(route('agendamentobloqueios.store'), $bloqueio);

        $all = \App\Regional::all();
        $log = tailCustom(storage_path($this->pathLogInterno()), $all->count());

        foreach($all as $regional)
            $this->assertStringContainsString('criou *bloqueio de agendamento* (id: '.$regional->idregional.')', $log);
    }

    /** @test */
    public function cannot_create_bloqueio_without_requireds_inputs()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = [
            'diainicio' => null,
            'diatermino' => null,
            'horarios' => '',
            'qtd_atendentes' => '',
            'idregional' => ''
        ];

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'diainicio',
            'horarios',
            'qtd_atendentes',
            'idregional'
        ]);

        $this->assertDatabaseMissing('agendamento_bloqueios', [
            'idagendamentobloqueio' => 1
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_diainicio_and_diatermino_before_today()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['10:00'],
            'diainicio' => Carbon::today()->subDay()->format('Y-m-d'),
            'diatermino' => Carbon::today()->subDay()->format('Y-m-d')
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'diainicio',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_dia_termino_before_dia_inicio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['10:00'],
            'diainicio' => Carbon::today()->addDay()->format('Y-m-d'),
            'diatermino' => Carbon::today()->format('Y-m-d')
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'diatermino'
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_horarios_without_array()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => '10:00',
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_same_hours_in_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['10:00', '10:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios.*'
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_invalid_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'horarios' => ['18:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_without_qtd_atendentes()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'qtd_atendentes' => '',
            'horarios' => ['10:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'qtd_atendentes'
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_qtd_atendentes_greater_than_ageporhorario()
    {
        $user = $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => $regional->idregional,
            'qtd_atendentes' => $regional->ageporhorario + 1,
            'horarios' => ['10:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'qtd_atendentes'
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_qtd_atendentes_equal_ageporhorario()
    {
        $user = $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => $regional->idregional,
            'qtd_atendentes' => $regional->ageporhorario,
            'horarios' => ['10:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'qtd_atendentes'
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_nonexistent_regional()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->raw([
            'idregional' => 5,
            'horarios' => ['10:00'],
        ]);

        $this->get(route('agendamentobloqueios.criar'))->assertOk(); 
        $this->post(route('agendamentobloqueios.store'), $bloqueio)
        ->assertSessionHasErrors([
            'idregional'
        ]);
    }

    /** @test */
    public function can_edit_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['11:00', '12:00'];

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())->assertRedirect(route('agendamentobloqueios.lista'));
        $this->assertDatabaseHas('agendamento_bloqueios', [
            'idagendamentobloqueio' => $bloqueio->idagendamentobloqueio,
            'diainicio' => $bloqueio->diainicio,
            'diatermino' => $bloqueio->diatermino,
            'horarios' => implode(',', $bloqueio->horarios),
            'qtd_atendentes' => $bloqueio->qtd_atendentes,
            'idregional' => $bloqueio->idregional
        ]);
    }

    /** @test */
    public function can_edit_bloqueio_with_qtd_atendentes_greater_than_0()
    {
        $user = $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'qtd_atendentes' => $regional->ageporhorario - 1,
        ]);
        $bloqueio->horarios = ['11:00', '12:00'];

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())->assertRedirect(route('agendamentobloqueios.lista'));
        $this->assertDatabaseHas('agendamento_bloqueios', [
            'idagendamentobloqueio' => $bloqueio->idagendamentobloqueio,
            'diainicio' => $bloqueio->diainicio,
            'diatermino' => $bloqueio->diatermino,
            'horarios' => implode(',', $bloqueio->horarios),
            'qtd_atendentes' => $bloqueio->qtd_atendentes,
            'idregional' => $bloqueio->idregional
        ]);
    }

    /** @test */
    public function can_edit_bloqueio_without_dia_termino()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['11:00', '12:00'];
        $bloqueio->diatermino = null;

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())->assertRedirect(route('agendamentobloqueios.lista'));
        $this->assertDatabaseHas('agendamento_bloqueios', [
            'idagendamentobloqueio' => $bloqueio->idagendamentobloqueio,
            'diainicio' => $bloqueio->diainicio,
            'diatermino' => null,
            'horarios' => implode(',', $bloqueio->horarios),
            'qtd_atendentes' => $bloqueio->qtd_atendentes,
            'idregional' => $bloqueio->idregional
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_edited()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['11:00', '12:00'];

        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray());

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('bloqueio de agendamento', $log);
        $this->assertStringContainsString('editou', $log);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_requireds_inputs()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = null;
        $bloqueio->diainicio = null;
        $bloqueio->qtd_atendentes = null;
        $bloqueio->idregional = null;

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'diainicio',
            'horarios',
            'qtd_atendentes',
            'idregional'
        ]);

        $this->assertDatabaseMissing('agendamento_bloqueios', [
            'idagendamentobloqueio' => 1,
            'diainicio' => null
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_diainicio_and_diatermino_before_today()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['11:00', '12:00'];
        $bloqueio->diainicio = Carbon::today()->subDay()->format('Y-m-d');
        $bloqueio->diatermino = Carbon::today()->subDay()->format('Y-m-d');

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'diainicio',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_dia_termino_before_dia_inicio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['11:00', '12:00'];
        $bloqueio->diainicio = Carbon::today()->addDay()->format('Y-m-d');
        $bloqueio->diatermino = Carbon::today()->format('Y-m-d');

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'diatermino'
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_invalid_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['18:00', '19:00'];

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_qtd_atendentes()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['10:00', '11:00'];
        $bloqueio->qtd_atendentes = '';

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'qtd_atendentes'
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_qtd_atendentes_greater_than_ageporhorario()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['10:00', '11:00'];
        $bloqueio->qtd_atendentes = $bloqueio->regional->ageporhorario + 1;

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'qtd_atendentes'
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_qtd_atendentes_equal_ageporhorario()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['10:00', '11:00'];
        $bloqueio->qtd_atendentes = $bloqueio->regional->ageporhorario;

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'qtd_atendentes'
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_nonexistent_regional()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();
        $bloqueio->horarios = ['10:00', '11:00'];
        $bloqueio->idregional = 5;

        $this->get(route('agendamentobloqueios.edit', $bloqueio->idagendamentobloqueio))->assertOk(); 
        $this->put(route('agendamentobloqueios.update', $bloqueio->idagendamentobloqueio), $bloqueio->toArray())
        ->assertSessionHasErrors([
            'idregional'
        ]);
    }

    /** @test */
    public function can_delete_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();

        $this->delete(route('agendamentobloqueios.delete', $bloqueio->idagendamentobloqueio))->assertRedirect(route('agendamentobloqueios.lista'));
        $this->assertDatabaseHas('agendamento_bloqueios', [
            'idagendamentobloqueio' => $bloqueio->idagendamentobloqueio,
            'diainicio' => $bloqueio->diainicio,
            'deleted_at' => Carbon::now()
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_deleted()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\AgendamentoBloqueio')->create();

        $this->delete(route('agendamentobloqueios.delete', $bloqueio->idagendamentobloqueio));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('bloqueio de agendamento', $log);
        $this->assertStringContainsString('cancelou', $log);
    }

    /** @test */
    public function can_view_list_bloqueios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();

        $this->get(route('agendamentobloqueios.lista'))
        ->assertSee($bloqueio->regional->regional)
        ->assertSee('Início: '.onlyDate($bloqueio->diainicio))
        ->assertSee('Término: '.$bloqueio->getMsgDiaTermino())
        ->assertSee($bloqueio->horarios)
        ->assertSee($bloqueio->qtd_atendentes);
    }

    /** @test */
    public function can_view_list_bloqueios_with_diatermino_null()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'diatermino' => null
        ]);

        $this->get(route('agendamentobloqueios.lista'))
        ->assertSee($bloqueio->regional->regional)
        ->assertSee('Início: '.onlyDate($bloqueio->diainicio))
        ->assertSee('Término: '.$bloqueio->getMsgDiaTermino())
        ->assertSee($bloqueio->horarios)
        ->assertSee($bloqueio->qtd_atendentes);
    }

    /** @test */
    public function can_view_button_editar_and_cancelar()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\AgendamentoBloqueio')->create();

        $this->get(route('agendamentobloqueios.lista'))
        ->assertSee($bloqueio->regional->regional)
        ->assertSee('Início: '.onlyDate($bloqueio->diainicio))
        ->assertSee('Término: '.$bloqueio->getMsgDiaTermino())
        ->assertSee($bloqueio->horarios)
        ->assertSee($bloqueio->qtd_atendentes)
        ->assertSee('Editar')
        ->assertSee('Cancelar');
    }

    /** @test */
    public function can_view_list_regionais_when_create()
    {
        $user = $this->signInAsAdmin();

        $regionais = factory('App\Regional', 5)->create();

        $this->get(route('agendamentobloqueios.criar'))
        ->assertSee($regionais->get(0)->regional)
        ->assertSee($regionais->get(1)->regional)
        ->assertSee($regionais->get(2)->regional)
        ->assertSee($regionais->get(3)->regional)
        ->assertSee($regionais->get(4)->regional);
    }

    /** @test */
    public function cannot_view_regional_14_when_create()
    {
        $user = $this->signInAsAdmin();

        $regional = factory('App\Regional')->create([
            'idregional' => 14,
            'regional' => 'Alameda'
        ]);

        $this->get(route('agendamentobloqueios.criar'))
        ->assertDontSeeText($regional->regional);
    }

    /** @test */
    public function get_horarios_and_atendentes_by_ajax()
    {
        $user = $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();

        $this->get(route('agendamentobloqueios.dadosAjax', ['idregional' => $regional->idregional]))
        ->assertJson([
            'horarios' => $regional->horariosAge(),
            'atendentes' => $regional->ageporhorario
        ]);
    }

    /** @test */
    public function get_error_when_nonexistent_regional_by_ajax()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('agendamentobloqueios.dadosAjax', ['idregional' => 5]))->assertStatus(500);
    }

    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test 
     * 
     * Testando acesso a página de criação de Agendamentos.
    */
    public function access_agendamentos_from_portal()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $this->get(route('agendamentosite.formview'))
        ->assertOk()
        ->assertSee('<option value="'.$regional->idregional.'" >'.$regional->regional.' - Avenida Brigadeiro Luís Antônio</option>');
    }

    /** @test 
     * 
     * Testando criação de agendamento pelo Portal.
     * Verificando o envio de email.
    */
    public function agendamento_can_be_created_on_portal()
    {
        Mail::fake();
        $pegarDia = factory('App\Agendamento')->raw();

        $agendamento = factory('App\Agendamento')->raw([
            'nome' => 'Teste Ão Açaí',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)->assertOk();

        $this->assertEquals(Agendamento::count(), 1);

        Mail::assertQueued(AgendamentoMailGuest::class);
    }

    /** @test */
    public function log_is_generated_when_create_on_portal()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'nome' => 'Teste Ão Açaí',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento);
        $agendamento = Agendamento::find(1);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($agendamento->nome.' (CPF: '.$agendamento->cpf.') *agendou* atendimento em *'.$agendamento->regional->regional, $log);
        $this->assertStringContainsString('* no dia '.onlyDate($agendamento->dia).' para o serviço '.$agendamento->tiposervico.' e ', $log);
    }

    /** @test */
    public function two_agendamentos_can_be_created_with_same_cpf_same_dia()
    {
        $agendamento1 = factory('App\Agendamento')->create();
        $agendamento2 = factory('App\Agendamento')->raw([
            'hora' => '11:00',
            'dia' => onlyDate($agendamento1->dia),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento2)
        ->assertSee('<strong>Seu atendimento foi agendado com sucesso!</strong>');

        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => $agendamento1->idagendamento,
            'dia' => $agendamento1->dia,
            'cpf' => $agendamento1->cpf
        ]);
        $this->assertDatabaseHas('agendamentos', [
            'idagendamento' => 2,
            'dia' => $agendamento1->dia,
            'cpf' => $agendamento2['cpf']
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_without_requireds_inputs()
    {
        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => '',
            'nome' => '',
            'cpf' => '',
            'email' => '',
            'celular' => '',
            'hora' => '',
            'dia' => '',
            'servico' => '',
            'pessoa' => '',
            'termo' => ''
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'idregional',
            'nome',
            'cpf',
            'email',
            'celular',
            'hora',
            'dia',
            'servico',
            'pessoa',
            'termo'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_nome_length_less_than_5()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'nome' => 'Ana',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'nome'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_nome_length_greater_than_191()
    {
        $faker = \Faker\Factory::create();
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'nome' => $faker->sentence(400),
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'nome'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_number_in_nome()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'nome' => 'An4 Teste',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'nome'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_invalid_cpf()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'cpf' => '123.456.789-00',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'cpf'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_invalid_email()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'email' => 'teste.com',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'email'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_invalid_celular()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'celular' => '(11) A9999-9999',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'celular'
        ]);

        $agendamento = factory('App\Agendamento')->raw([
            'celular' => '(1) 99999-9999',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'celular'
        ]);

        $agendamento = factory('App\Agendamento')->raw([
            'celular' => '(11) 999999999',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'celular'
        ]);

        $agendamento = factory('App\Agendamento')->raw([
            'celular' => '11 99999-9999',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'celular'
        ]);

        $agendamento = factory('App\Agendamento')->raw([
            'celular' => '(11) 9999-9999',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'celular'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_invalid_servico()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => 'Teste',
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'servico'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_invalid_pessoa()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PFJ',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'pessoa'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_invalid_regional()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => 55,
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'idregional'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_regional_14()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'idregional' => factory('App\Regional')->create([
                'idregional' => 14
            ]),
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'idregional'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_dia_today()
    {
        $agendamento = factory('App\Agendamento')->raw([
            'dia' => Carbon::today()->format('d/m/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_dia_before_today()
    {
        $dia = Carbon::today()->subDay();
        while($dia->isWeekend())
            $dia->subDay();

        $agendamento = factory('App\Agendamento')->raw([
            'dia' => $dia->format('d/m/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_dia_after_1_month()
    {
        $dia = Carbon::today()->addMonth()->addDay();
        while($dia->isWeekend())
            $dia->addDay();

        $agendamento = factory('App\Agendamento')->raw([
            'dia' => $dia->format('d/m/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_format_invalid_dia()
    {
        $agendamento = factory('App\Agendamento')->raw([
            'dia' => Carbon::tomorrow()->format('Y-m-d'),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_dia_without_format_date()
    {
        $agendamento = factory('App\Agendamento')->raw([
            'dia' => '55//',
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertStatus(500);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_inexistent_hora()
    {
        $pegarDia = factory('App\Agendamento')->raw();
        $agendamento = factory('App\Agendamento')->raw([
            'hora' => '18:00',
            'dia' => onlyDate($pegarDia['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)
        ->assertSessionHasErrors([
            'hora'
        ]);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_same_cpf_same_dia_same_hora()
    {
        $agendamento = factory('App\Agendamento')->create();
        $agendamento2 = factory('App\Agendamento')->raw([
            'dia' => onlyDate($agendamento->dia),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento2)->assertStatus(302);

        $this->get(route('agendamentosite.formview'))
        ->assertSee('<i class="icon fa fa-ban"></i>Este CPF já possui um agendamento neste dia e horário');
    }

    /** @test */
    public function three_or_more_agendamentos_cannot_be_created_with_same_cpf_same_dia()
    {
        $agendado = factory('App\Agendamento')->create();
        factory('App\Agendamento')->create([
            'dia' => $agendado->dia,
            'hora' => '11:00',
        ]);
        $agendamento = factory('App\Agendamento')->raw([
            'hora' => '12:00',
            'dia' => onlyDate($agendado->dia),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento)->assertStatus(302);

        $this->get(route('agendamentosite.formview'))
        ->assertSee('<i class="icon fa fa-ban"></i>É permitido apenas 2 agendamentos por cpf por dia');
    }

    /** @test */
    public function agendamento_can_be_created_with_cpf_that_didnt_show_up_three_times_in_more_90_days()
    {
        $subday = Carbon::today()->subDays(55);
        while($subday->isWeekend())
            $subday->subDay();

        $agendamento_1 = factory('App\Agendamento')->create([
            'dia' => $subday->format('Y-m-d'),
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $subday = Carbon::today()->subDays(65);
        while($subday->isWeekend())
            $subday->subDay();

        $agendamento_2 = factory('App\Agendamento')->create([
            'dia' => $subday->format('Y-m-d'),
            'protocolo' => 'AGE-YYYYYY',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $subday = Carbon::today()->subDays(91);
        while($subday->isWeekend())
            $subday->subDay();

        $agendamento_3 = factory('App\Agendamento')->create([
            'dia' => $subday->format('Y-m-d'),
            'hora' => '12:00',
            'protocolo' => 'AGE-WWWWWW',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $day = Carbon::tomorrow();
        while($day->isWeekend())
            $day->addDay();

        $agendamento_4 = factory('App\Agendamento')->raw([
            'dia' => $day->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'hora' => '11:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento_4)->assertOk();

        $this->assertEquals(Agendamento::count(), 4);
    }

    /** @test */
    public function agendamento_cannot_be_created_with_cpf_that_didnt_show_up_three_times_in_90_days()
    {
        $subday = Carbon::tomorrow()->subDays(35);
        while($subday->isWeekend())
            $subday->subDay();

        $agendamento_1 = factory('App\Agendamento')->create([
            'dia' => $subday->format('Y-m-d'),
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $subday = Carbon::tomorrow()->subDays(45);
        while($subday->isWeekend())
            $subday->subDay();

        $agendamento_2 = factory('App\Agendamento')->create([
            'dia' => $subday->format('Y-m-d'),
            'protocolo' => 'AGE-YYYYYY',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $subday = Carbon::tomorrow()->subDays(55);
        while($subday->isWeekend())
            $subday->subDay();

        $agendamento_3 = factory('App\Agendamento')->create([
            'dia' => $subday->format('Y-m-d'),
            'hora' => '12:00',
            'protocolo' => 'AGE-WWWWWW',
            'status' => Agendamento::STATUS_NAO_COMPARECEU
        ]);

        $day = Carbon::tomorrow();
        while($day->isWeekend())
            $day->addDay();

        $agendamento_4 = factory('App\Agendamento')->raw([
            'dia' => $day->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
            'pessoa' => 'PF',
            'hora' => '11:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $agendamento_4)->assertStatus(302);

        $this->get(route('agendamentosite.formview'))
        ->assertSee('<i class="icon fa fa-ban"></i>Agendamento bloqueado por excesso de falta nos últimos 90 dias.')
        ->assertSee(' Favor entrar em contato com o Core-SP para regularizar o agendamento.');

        $this->assertEquals(Agendamento::count(), 3);
    }

    /** @test */
    public function cannot_get_days_when_regional_14()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 14
        ]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertStatus(500);
    }

    /** @test */
    public function get_full_days()
    {
        $regional = factory('App\Regional')->create([
            'horariosage' => '10:00',
            'ageporhorario' => 1
        ]);
        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '10:00'
        ]);

        $dia = Carbon::parse($agendamento->dia);
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    public function get_full_days_if_weekends()
    {
        $regional = factory('App\Regional')->create();

        $sabado = Carbon::tomorrow();
        while(!$sabado->isSaturday())
            $sabado->addDay();
        $lotado = [$sabado->month, $sabado->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([$lotado]);

        $domingo = Carbon::tomorrow();
        while(!$domingo->isSunday())
            $domingo->addDay();
        $lotado = [$domingo->month, $domingo->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    public function get_full_days_with_0_atendentes_bloqueio()
    {
        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => $regional->horariosage,
        ]);

        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
        $lotado = [$dia->month, $dia->day, 'lotado'];
        $dia->addDays(7);
        $nao_lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([$lotado])
        ->assertJsonMissingExact([$nao_lotado]);
    }

    /** @test */
    // Situação em que o bloqueio é criado para algumas horas e não cancela o agendamento já existente no horário bloqueado.
    // Então mesmo com a hora disponível, a quantidade de agendados já foi preenchida, a não ser que ocorra o cancelamento.
    public function get_full_days_with_0_atendentes_bloqueio_and_created_agendado()
    {
        $regional = factory('App\Regional')->create([
            'horariosage' => '10:00,11:00',
            'ageporhorario' => 1
        ]);
        
        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
            
        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '10:00',
            'dia' => $dia->format('Y-m-d')
        ]);

        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => '10:00',
        ]);

        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    // Situação em que o bloqueio é criado para algumas horas e cancela o agendamento já existente no horário bloqueado.
    public function get_empty_days_with_0_atendentes_bloqueio_and_after_cancel_created_agendado()
    {
        $regional = factory('App\Regional')->create([
            'horariosage' => '10:00,11:00',
            'ageporhorario' => 1
        ]);
        
        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
            
        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '10:00',
            'dia' => $dia->format('Y-m-d')
        ]);

        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => '10:00',
        ]);

        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([$lotado]);

        $agendamento->update(['status' => AGENDAMENTO::STATUS_CANCELADO]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([]);
    }

    /** @test */
    public function get_full_days_with_0_atendentes_and_diatermino_null_bloqueio()
    {
        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => $regional->horariosage,
            'diatermino' => null
        ]);

        $lotados = array();
        $dia = Carbon::tomorrow();
        while($dia->lte(Carbon::today()->addMonth()))
        {
            array_push($lotados, [$dia->month, $dia->day, 'lotado']);
            $dia->addDay();
        }

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJson($lotados);
    }

    /** @test */
    public function get_full_days_with_1_or_more_atendentes_and_diatermino_null_bloqueio()
    {
        $regional = factory('App\Regional')->create([
            'horariosage' => '10:00'
        ]);

        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => $regional->horariosage,
            'qtd_atendentes' => 1,
            'diatermino' => null
        ]);

        $dia = Carbon::tomorrow()->addDays(10);
        while($dia->isWeekend())
            $dia->addDay();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '10:00',
            'dia' => $dia->format('Y-m-d')
        ]);

        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    public function get_empty_days_with_1_or_more_atendentes_bloqueio()
    {
        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => $regional->horariosage,
            'qtd_atendentes' => 1
        ]);

        $lotados = array();
        $dia = Carbon::tomorrow();
        while($dia->lte(Carbon::today()->addMonth()))
        {
            if($dia->isWeekend())
                array_push($lotados, [$dia->month, $dia->day, 'lotado']);
            $dia->addDay();
        }

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJson($lotados);
    }

    /** @test */
    public function get_full_days_when_weekends_and_empty_days_for_agendamentos()
    {
        $regional = factory('App\Regional')->create();
        $agendamentos = factory('App\Agendamento', 2)->create([
            'idregional' => $regional->idregional,
        ]);

        $diaAge = Carbon::parse($agendamentos->get(0)->dia);
        $lotados = array();
        $dia = Carbon::tomorrow();
        while($dia->lte(Carbon::today()->addMonth()))
        {
            if($dia->isWeekend())
                array_push($lotados, [$dia->month, $dia->day, 'lotado']);
            $dia->addDay();
        }

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'servico' => Agendamento::SERVICOS_OUTROS
        ]))
        ->assertJson($lotados)
        ->assertJsonMissingExact(
            [$diaAge->month, $diaAge->day, 'lotado']
        );
    }

    /** @test */
    public function cannot_get_hours_when_regional_14()
    {
        $regional = factory('App\Regional')->create([
            'idregional' => 14
        ]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => date('d/m/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertStatus(500);
    }

    /** @test */
    public function get_hours()
    {
        $regional = factory('App\Regional')->create();
        $agendamento = factory('App\Agendamento')->raw();

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => onlyDate($agendamento['dia']),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJson($regional->horariosAge());
    }

    /** @test */
    public function remove_full_hour()
    {
        $regional = factory('App\Regional')->create();
        $agendamentos = factory('App\Agendamento', $regional->ageporhorario)->create([
            'idregional' => $regional->idregional,
        ]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => onlyDate($agendamentos->get(0)->dia),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJsonMissing(['10:00']);
    }

    /** @test */
    public function remove_full_hour_if_weekend()
    {
        $regional = factory('App\Regional')->create();

        $sabado = Carbon::tomorrow();
        while(!$sabado->isSaturday())
            $sabado->addDay();
        $lotado = [$sabado->month, $sabado->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => $sabado->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJsonMissing($regional->horariosAge());

        $domingo = Carbon::tomorrow();
        while(!$domingo->isSaturday())
            $domingo->addDay();
        $lotado = [$domingo->month, $domingo->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => $domingo->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJsonMissing($regional->horariosAge());
    }

    /** @test */
    public function remove_full_hour_with_0_atendentes_bloqueio()
    {
        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => '10:00,12:00',
        ]);

        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
            
        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => $dia->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJsonMissing(explode(',', $bloqueio->horarios));
    }

    /** @test */
    public function remove_full_hour_with_0_atendentes_and_diatermino_null_bloqueio()
    {
        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => '10:00,12:00',
            'diatermino' => null
        ]);

        $dia = Carbon::tomorrow()->addDays(7);
        while($dia->isWeekend())
            $dia->addDay();

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => $dia->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJsonMissing(explode(',', $bloqueio->horarios));
    }

    /** @test */
    public function remove_full_hour_with_1_or_more_atendentes_bloqueio()
    {
        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => '10:00,12:00',
            'qtd_atendentes' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '10:00'
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '11:00'
        ]);

        $horarios = $regional->horariosAge();
        unset($horarios[array_search('10:00', $horarios)]);

        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => $dia->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJsonMissing(['10:00'])
        ->assertJson($horarios);
    }

    /** @test */
    public function remove_full_hour_with_1_or_more_atendentes_and_diatermino_null_bloqueio()
    {
        $regional = factory('App\Regional')->create();
        $bloqueio = factory('App\AgendamentoBloqueio')->create([
            'idregional' => $regional->idregional,
            'horarios' => '10:00,12:00',
            'qtd_atendentes' => 1,
            'diatermino' => null
        ]);

        $dia = Carbon::tomorrow()->addDays(7);
        while($dia->isWeekend())
            $dia->addDay();

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '10:00',
            'dia' => $dia->format('Y-m-d')
        ]);

        $agendamento2 = factory('App\Agendamento')->create([
            'idregional' => $regional->idregional,
            'hora' => '11:00',
            'dia' => $dia->format('Y-m-d')
        ]);

        $horarios = $regional->horariosAge();
        unset($horarios[array_search('10:00', $horarios)]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $regional->idregional,
            'dia' => $dia->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_OUTROS,
        ]))
        ->assertJsonMissing(['10:00'])
        ->assertJson($horarios);
    }

    /** @test 
     * 
     * Testando acesso a página de consulta de Agendamentos.
    */
    public function access_search_agendamentos_from_portal()
    {
        $this->get(route('agendamentosite.consultaView'))->assertOk();
    }

    /** @test 
     * 
     * Testando consulta de Agendamento pelo protocolo no Portal.
    */
    public function search_agendamento_on_portal()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSee('Agendamento encontrado!')
        ->assertSee($agendamento->protocolo)
        ->assertSee($agendamento->nome)
        ->assertSee(onlyDate($agendamento->dia))
        ->assertSee($agendamento->hora)
        ->assertSee($agendamento->regional->regional)
        ->assertSee($agendamento->regional->endereco)
        ->assertSee($agendamento->tiposervico);
    }

    /** @test */
    public function not_find_agendamento_when_search_on_portal()
    {
        $protocolo = 'XXXXXX';

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSee('Nenhum agendamento encontrado!');
    }

    /** @test */
    public function not_search_agendamento_on_portal_with_size_less_than_6_protocolo()
    {
        $protocolo = 'XXXXX';

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSessionHasErrors([
            'protocolo'
        ]);
    }

    /** @test */
    public function not_search_agendamento_on_portal_with_size_greater_than_6_protocolo()
    {
        $protocolo = 'XXXXXXX';

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSessionHasErrors([
            'protocolo'
        ]);
    }

    /** @test */
    public function not_search_agendamento_on_portal_with_invalid_format_protocolo()
    {
        $protocolo = '/XXXXX';

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSessionHasErrors([
            'protocolo'
        ]);
    }

    /** @test */
    public function not_find_agendamento_when_search_on_portal_if_dia_is_past()
    {
        $agendamento = factory('App\Agendamento')->create([
            'dia' => Carbon::today()->subDay()->format('Y-m-d')
        ]);
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSee('Nenhum agendamento encontrado!');
    }

    /** @test */
    public function find_agendamento_when_search_on_portal_if_dia_is_today()
    {
        $agendamento = factory('App\Agendamento')->create([
            'dia' => Carbon::today()->format('Y-m-d')
        ]);
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSee('Agendamento encontrado!')
        ->assertSee($agendamento->protocolo)
        ->assertSee($agendamento->nome)
        ->assertSee(onlyDate($agendamento->dia))
        ->assertSee($agendamento->hora)
        ->assertSee($agendamento->regional->regional)
        ->assertSee($agendamento->regional->endereco)
        ->assertSee($agendamento->tiposervico);
    }

    /** @test */
    public function find_agendamento_when_search_on_portal_with_status_cancelado()
    {
        $agendamento = factory('App\Agendamento')->create([
            'status' => Agendamento::STATUS_CANCELADO
        ]);
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSee('Agendamento cancelado');
    }

    /** @test 
     * 
     * Testando cancelamento de Agendamento no Portal.
    */
    public function cancel_agendamento_on_portal()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ]);

        $this->assertEquals(Agendamento::find($agendamento->idagendamento)->status, Agendamento::STATUS_CANCELADO);
    }

    /** @test */
    public function log_is_generated_when_cancel()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString($agendamento->nome.' (CPF: '.$agendamento->cpf.') *cancelou* atendimento em *'.$agendamento->regional->regional, $log);
        $this->assertStringContainsString('* no dia '.onlyDate($agendamento->dia), $log);
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_without_protocolo()
    {
        $agendamento = factory('App\Agendamento')->create();

        $this->put(route('agendamentosite.cancelamento'), [
            'cpf' => $agendamento->cpf
        ])->assertRedirect(route('agendamentosite.consultaView'));

        $this->get(route('agendamentosite.consultaView'))
        ->assertSee('Protocolo não recebido. Faça a consulta do agendamento novamente.');
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_with_invalid_format_protocolo()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = 'XXXXX';

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ])->assertRedirect(route('agendamentosite.consultaView'));

        $this->get(route('agendamentosite.consultaView'))
        ->assertSeeText('O protocolo não existe para agendamentos de hoje em diante');
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_without_cpf()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => ''
        ])->assertSessionHasErrors([
            'cpf'
        ]);
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_with_invalid_cpf()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => '123.456.789-00'
        ])->assertSessionHasErrors([
            'cpf'
        ]);
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_with_inexistent_protocolo()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = 'ABC123';

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ])->assertStatus(302);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSeeText('O protocolo não existe para agendamentos de hoje em diante');
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_with_different_cpf()
    {
        $agendamento = factory('App\Agendamento')->create();
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => '710.634.520-23'
        ])->assertStatus(302);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSeeText('O CPF informado não corresponde ao protocolo. Por favor, pesquise novamente o agendamento');
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_if_dia_is_past_or_today()
    {
        $agendamento = factory('App\Agendamento')->create([
            'dia' => Carbon::today()->format('Y-m-d')
        ]);
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ])->assertStatus(302);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSeeText('Cancelamento do agendamento deve ser antes do dia do atendimento');

        $agendamento->dia = Carbon::today()->subDays(5)->format('Y-m-d');

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ])->assertStatus(302);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSeeText('Cancelamento do agendamento deve ser antes do dia do atendimento');
    }

    /** @test */
    public function cannot_cancel_agendamento_on_portal_with_status_not_null()
    {
        $agendamento = factory('App\Agendamento')->create([
            'status' => Agendamento::STATUS_COMPARECEU
        ]);
        $protocolo = str_replace('AGE-', null, $agendamento->protocolo);

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ])->assertStatus(302);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSeeText('O protocolo não existe para agendamentos de hoje em diante');

        $agendamento->status = Agendamento::STATUS_NAO_COMPARECEU;

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ])->assertStatus(302);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSeeText('O protocolo não existe para agendamentos de hoje em diante');

        $agendamento->status = Agendamento::STATUS_CANCELADO;

        $this->put(route('agendamentosite.cancelamento', ['protocolo' => $protocolo]), [
            'cpf' => $agendamento->cpf
        ])->assertStatus(302);

        $this->get(route('agendamentosite.consulta', ['protocolo' => $protocolo]))
        ->assertSeeText('O protocolo não existe para agendamentos de hoje em diante');
    }

    /** @test 
     * 
     * Testando a API que retorna os horários de acordo com regional e dia.
    */
    public function retrieve_agendamentos_by_api()
    {
        // regional_1 permite 2 agendamentos por horário
        $regional_1 = factory('App\Regional')->create([
            'idregional' => 1,
            'regional' => 'São Paulo', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        $regional_2 = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Campinas', 
            'ageporhorario' => 2, 
            'horariosage' => '10:00,11:00,12:00,13:00,14:00'
        ]);

        // Registrando um agendamento na regional_1 às 10:00
        $agendamento_1 = factory('App\Agendamento')->create([
            'idregional' => $regional_1->idregional,
            'hora' => '10:00',
            'protocolo' => 'AGE-XXXXXX'
        ]);

        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();

        // Verificando que ainda é possível agendar na regional_1 às 10:00 e em todos os outros horários
        $this->get(route('agendamentosite.diasHorasAjax', ['idregional' => 1, 'servico' => 'Outros', 'dia' => $dia->format('d/m/Y')]))
            ->assertSee('10:00')
            ->assertSee('11:00')
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('14:00');

        // Registrando mais um agendamento na regional_1 às 10:00
        $agendamento_2 = factory('App\Agendamento')->create([
            'idregional' => $regional_1->idregional,
            'hora' => '10:00',
            'protocolo' => 'AGE-YYYYYY'
        ]);

        // Verificando que não é mais possível agendar na regional_1 às 10:00, mas ainda é possível em todos os outros horários
        $this->get(route('agendamentosite.diasHorasAjax', ['idregional' => 1, 'servico' => 'Outros', 'dia' => $dia->format('d/m/Y')]))
            ->assertDontSee('10:00')
            ->assertSee('11:00')
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('14:00');

        // Verificando que o horário as 10:00 está disponível na outra regional "regional_2"
        $this->get(route('agendamentosite.diasHorasAjax', ['idregional' => 2, 'servico' => 'Outros', 'dia' => $dia->format('d/m/Y')]))
            ->assertSee('10:00')
            ->assertSee('11:00')
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('14:00');
    }

    /** @test */
    public function view_regionais_on_portal()
    {
        $regionais = factory('App\Regional', 3)->create();
        $regionais->get(0)->regional = 'São Paulo - Avenida Brigadeiro Luís Antônio';

        $this->get(route('agendamentosite.formview'))
        ->assertSeeText('Selecione a regional')
        ->assertSeeText($regionais->get(0)->regional)
        ->assertSeeText($regionais->get(1)->regional)
        ->assertSeeText($regionais->get(2)->regional);
    }

    /** @test */
    public function cannot_view_regional_14_on_portal()
    {
        factory('App\Regional')->create();
        $regional = factory('App\Regional')->create([
            'idregional' => 14,
            'regional' => 'Alameda'
        ]);

        $this->get(route('agendamentosite.formview'))
        ->assertDontSeeText('<option value="14">'.$regional->regional.'</option>');
    }

    /** @test */
    public function view_tipo_pessoas_on_portal()
    {
        factory('App\Regional')->create();

        $this->get(route('agendamentosite.formview'))
        ->assertSeeText('Para:')
        ->assertSeeText('Pessoa Física')
        ->assertSeeText('Pessoa Jurídica')
        ->assertSeeText('Ambas');
    }

    /** @test */
    public function view_tipos_servicos_on_portal()
    {
        factory('App\Regional')->create();
        
        $this->get(route('agendamentosite.formview'))
        ->assertSeeText('Tipo de Serviço')
        ->assertSeeText(Agendamento::SERVICOS_ATUALIZACAO_DE_CADASTRO)
        ->assertSeeText(Agendamento::SERVICOS_CANCELAMENTO_DE_REGISTRO)
        ->assertDontSeeText(Agendamento::SERVICOS_REFIS)
        ->assertSeeText(Agendamento::SERVICOS_REGISTRO_INICIAL)
        ->assertSeeText(Agendamento::SERVICOS_OUTROS);
    }

    /** @test */
    public function view_termo_on_portal()
    {
        factory('App\Regional')->create();
        
        $this->get(route('agendamentosite.formview'))
        ->assertSee(route('termo.consentimento.pdf'));
    }

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

        $this->get(route('agendamentosite.formview'))->assertSee(Agendamento::SERVICOS_PLANTAO_JURIDICO);
    }

    /** @test */
    public function cannot_view_plantao_juridico_option_when_disabled_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 0
        ]);

        $this->get(route('agendamentosite.formview'))->assertDontSee(Agendamento::SERVICOS_PLANTAO_JURIDICO);
    }

    /** @test */
    public function can_create_agendamento_with_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF e PJ',
            'dia' => onlyDate($plantao->dataInicial),
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->get(route('agendamentosite.formview'))->assertSee(Agendamento::SERVICOS_PLANTAO_JURIDICO);

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
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF e PJ',
            'dia' => onlyDate($plantao->dataInicial),
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
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF e PJ',
            'dia' => onlyDate($plantao->dataInicial),
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
    public function cannot_create_agendamentos_greater_than_qtd_advogados_in_same_hour_with_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF e PJ',
            'dia' => onlyDate($plantao->dataInicial),
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
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF e PJ',
            'dia' => onlyDate($plantao->dataInicial),
            'hora' => '10:00',
            'termo' => 'on'
        ]);

       $this->post(route('agendamentosite.store'), $dados)
        ->assertSessionHasErrors(['hora']);

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
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF',
            'dia' => onlyDate($plantao->dataInicial),
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->get(route('agendamentosite.formview'))->assertDontSee(Agendamento::SERVICOS_PLANTAO_JURIDICO);

        $this->post(route('agendamentosite.store'), $dados)
        ->assertSessionHasErrors(['hora']);

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
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF',
            'dia' => onlyDate($plantao->dataInicial),
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)
        ->assertSessionHasErrors(['dia']);
        
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
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF',
            'dia' => Carbon::parse($plantao->dataFinal)->addDay()->format('d\/m\/Y'),
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)
        ->assertSessionHasErrors(['hora']);
        
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
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF',
            'dia' => onlyDate($plantao->dataFinal),
            'hora' => '09:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)
        ->assertSessionHasErrors(['hora']);
        
        $this->assertDatabaseMissing('agendamentos', [
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function cannot_create_agendamento_with_full_day_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'horarios' => '10:00',
            'qtd_advogados' => 1
        ]);
        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dados = factory('App\Agendamento')->raw([
            'cpf' => '274.461.700-85',
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF e PJ',
            'dia' => onlyDate($plantao->dataFinal),
            'hora' => '10:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)
        ->assertSessionHasErrors(['hora']);
        
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
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PF',
            'dia' => onlyDate($plantao->dataInicial),
            'hora' => '11:00',
            'termo' => 'on'
        ]);

        $this->post(route('agendamentosite.store'), $dados)->assertStatus(302);

        $this->get(route('agendamentosite.formview'))
        ->assertSeeText('Durante o período deste plantão jurídico é permitido apenas 1 agendamento por cpf');
        
        $this->assertDatabaseMissing('agendamentos', [
            'idagendamento' => 2,
            'cpf' => $dados['cpf'],
            'hora' => $dados['hora'],
            'tiposervico' => $dados['servico'].' para '.$dados['pessoa']
        ]);
    }

    /** @test */
    public function can_create_two_or_more_agendamentos_with_same_cpf_and_differents_regionais_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $plantao2 = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dados = factory('App\Agendamento')->raw([
            'idregional' => $plantao2->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
            'pessoa' => 'PJ',
            'dia' => onlyDate($plantao->dataInicial),
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

        $this->get(route('agendamentosite.regionaisPlantaoJuridico'))
        ->assertJson([$plantao->idregional]);
    }

    /** @test */
    public function get_horarios_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'dia' => onlyDate($plantao->dataInicial),
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJson(explode(',', $plantao->horarios));
    }

    /** @test */
    public function get_empty_horarios_when_disabled_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'dia' => onlyDate($plantao->dataInicial),
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
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
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $horarios = explode(',', $plantao->horarios);
        unset($horarios[array_search($agendamento->hora, $horarios)]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'dia' => onlyDate($plantao->dataFinal),
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
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
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $dia = Carbon::parse($plantao->dataFinal);
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJson([$lotado]);
    }

    /** @test */
    public function get_empty_lotado_when_disabled_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJson([]);
    }

    /** @test */
    public function get_dates_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([$plantao->dataInicial, $plantao->dataFinal]);
    }

    /** @test */
    public function get_dates_when_data_inicial_less_than_tomorrow_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'dataInicial' => date('Y-m-d'),
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([null, $plantao->dataFinal]);
    }

    /** @test */
    public function get_datas_when_data_final_less_than_tomorrow_active_plantao_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'dataInicial' => date('Y-m-d'),
            'dataFinal' => date('Y-m-d'),
            'qtd_advogados' => 1
        ]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([null, null]);
    }

    /** @test */
    public function get_empty_dates_when_disabled_planta_juridico()
    {
        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional
        ]))
        ->assertJson([]);
    }

    /** @test */
    public function get_full_days_with_bloqueio_pj()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'horarios' => '10:00,11:00',
            'qtd_advogados' => 1
        ]);

        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantao->id,
            'horarios' => $plantao->horarios,
            'dataFinal' => $plantao->dataInicial
        ]);

        $dia = Carbon::parse($plantao->dataInicial);
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $dia = Carbon::parse($plantao->dataFinal);
        $nao_lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJsonFragment([$lotado])
        ->assertJsonMissingExact([$nao_lotado]);
    }

    /** @test */
    // Situação em que o bloqueio é criado para algumas horas e não cancela o agendamento já existente no horário bloqueado.
    // Então mesmo com a hora disponível, a quantidade de agendados já foi preenchida, a não ser que ocorra o cancelamento.
    public function get_full_days_with_bloqueio_pj_and_created_agendado()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'horarios' => '10:00,11:00',
            'qtd_advogados' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $plantao->idregional,
            'hora' => '10:00',
            'dia' => $plantao->dataInicial,
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para PF'
        ]);

        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantao->id,
            'horarios' => '10:00'
        ]);

        $dia = Carbon::parse($plantao->dataInicial);
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    // Situação em que o bloqueio é criado para algumas horas e cancela o agendamento já existente no horário bloqueado.
    public function get_empty_days_with_bloqueio_pj_and_after_cancel_created_agendado()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'horarios' => '10:00,11:00',
            'qtd_advogados' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $plantao->idregional,
            'hora' => '10:00',
            'dia' => $plantao->dataInicial,
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para PF'
        ]);

        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantao->id,
            'horarios' => '10:00'
        ]);

        $dia = Carbon::parse($plantao->dataInicial);
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJsonFragment([$lotado]);

        $agendamento->update(['status' => AGENDAMENTO::STATUS_CANCELADO]);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJsonFragment([]);
    }

    /** @test */
    public function get_full_days_when_weekends_and_empty_days_for_agendamentos_pj()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'idregional' => $plantao->idregional,
            'hora' => '10:00',
            'dia' => $plantao->dataInicial,
            'tiposervico' => Agendamento::SERVICOS_PLANTAO_JURIDICO.' para PF'
        ]);

        $diaAge = Carbon::parse($agendamento->dia);
        $lotados = array();
        $dia = Carbon::parse($plantao->dataInicial);
        while($dia->lte(Carbon::parse($plantao->dataFinal)))
        {
            if($dia->isWeekend())
                array_push($lotados, [$dia->month, $dia->day, 'lotado']);
            $dia->addDay();
        }

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO
        ]))
        ->assertJson($lotados)
        ->assertJsonMissing([
            [$diaAge->month, $diaAge->day, 'lotado']
        ]);
    }

    /** @test */
    public function remove_full_hour_if_weekend_pj()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $sabado = Carbon::parse($plantao->dataInicial);
        while(!$sabado->isSaturday())
            $sabado->addDay();

        $plantao->dataFinal = $sabado->format('Y-m-d');
        $lotado = [$sabado->month, $sabado->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'dia' => $sabado->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
        ]))
        ->assertJsonMissing([$plantao->horarios]);

        $domingo = Carbon::parse($plantao->dataInicial);
        while(!$domingo->isSaturday())
            $domingo->addDay();

        $plantao->dataFinal = $domingo->format('Y-m-d');
        $lotado = [$domingo->month, $domingo->day, 'lotado'];

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'dia' => $domingo->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
        ]))
        ->assertJsonMissing([$plantao->horarios]);
    }

    /** @test */
    public function remove_full_hour_with_bloqueio_pj()
    {
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantao->id,
            'horarios' => '10:00,11:30'
        ]);
            
        $dia = Carbon::parse($plantao->dataInicial);

        $this->get(route('agendamentosite.diasHorasAjax', [
            'idregional' => $plantao->idregional,
            'dia' => $dia->format('d\/m\/Y'),
            'servico' => Agendamento::SERVICOS_PLANTAO_JURIDICO,
        ]))
        ->assertJsonMissing(explode(',', $bloqueio->horarios));
    }
}