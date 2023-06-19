<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;
use App\AgendamentoSala;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoSalaMail;
use Illuminate\Http\UploadedFile;

class AgendamentoSalaTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES AGENDADOS NO ADMIN
     * =======================================================================================================
     */

    // /** @test */
    // public function non_authenticated_users_cannot_access_links()
    // {
    //     $this->assertGuest();
        
    //     $sala = factory('App\SalaReuniao')->create();
        
    //     $this->get(route('sala.reuniao.index'))->assertRedirect(route('login'));
    //     $this->get(route('sala.reuniao.editar.view', $sala->id))->assertRedirect(route('login'));
    //     $this->put(route('sala.reuniao.editar', $sala->id))->assertRedirect(route('login'));
    // }

    // // /** @test */
    // // public function non_authorized_users_cannot_access_links()
    // // {
    // //     $this->signIn();
    // //     $this->assertAuthenticated('web');

    // //     $plantao = factory('App\PlantaoJuridico')->create();
    // //     $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

    // //     $this->get(route('plantao.juridico.index'))->assertForbidden();
    // //     $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertForbidden();
    // //     $this->put(route('plantao.juridico.editar', $plantao->id))->assertForbidden();
    // //     $this->get(route('plantao.juridico.bloqueios.index'))->assertForbidden();
    // //     $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertForbidden();
    // //     $this->post(route('plantao.juridico.bloqueios.criar'))->assertForbidden();
    // //     $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertForbidden();
    // //     $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id))->assertForbidden();
    // //     $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertForbidden();
    // //     $this->get(route('plantao.juridico.bloqueios.ajax'))->assertForbidden();
    // // }

    /** 
     * =======================================================================================================
     * TESTES AGENDAMENTO NA ÁREA DO RC
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_cannot_access_routes_agendamento()
    {
        $this->get(route('representante.agendar.inserir.view'))->assertRedirect(route('representante.login'));
        $this->get(route('representante.agendar.inserir.view', 'agendar'))->assertRedirect(route('representante.login'));

        $agendamento = factory('App\AgendamentoSala')->create();

        $this->get(route('representante.agendar.inserir.view', ['editar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->get(route('representante.agendar.inserir.view', ['cancelar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->get(route('representante.agendar.inserir.view', ['justificar', $agendamento->id]))->assertRedirect(route('representante.login'));

        $this->post(route('representante.agendar.inserir.post', 'agendar'))->assertRedirect(route('representante.login'));
        $this->put(route('representante.agendar.inserir.put', ['editar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->put(route('representante.agendar.inserir.put', ['cancelar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->put(route('representante.agendar.inserir.put', ['justificar', $agendamento->id]))->assertRedirect(route('representante.login'));
    }

    /** @test */
    public function view_aba_agendar_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.dashboard'))
        ->assertOk()
        ->assertSee('<i class="fas fa-business-time"></i>&nbsp;&nbsp;Agendar Salas&nbsp;&nbsp;&nbsp;');
    }

    /** @test */
    public function view_button_agendar_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.agendar.inserir.view'))
        ->assertOk()
        ->assertSee('<a href="'. route('representante.agendar.inserir.view', 'agendar') .'" class="btn btn-primary link-nostyle branco">Agendar sala</a>');
    }

    // /** @test */
    // public function view_message_erro_when_agendar_sala_without_situacao_em_dia()
    // {
    //     // Tem de alterar o retorno em GerentiMock
    //     $representante = factory('App\Representante')->create();
    //     $this->actingAs($representante, 'representante');

    //     $dados = factory('App\SalaReuniao')->create();

    //     $this->get(route('representante.agendar.inserir.view', 'agendar'))
    //     ->assertRedirect(route('representante.agendar.inserir.view'));

    //     $this->get(route('representante.agendar.inserir.view'))
    //     ->assertSee('<i class="fas fa-exclamation-triangle"></i>&nbsp;Não pode criar agendamento no momento. Por gentileza, procure o atendimento do Core-SP.');

    //     $this->post(route('representante.agendar.inserir.post', 'agendar'), [
    //         'tipo_sala' => 'coworking', 'sala_reuniao_id' => $dados->id, 'dia' => now()->addDay()->format('d/m/Y'), 'periodo' => 'manha'
    //         ])
    //         ->assertRedirect(route('representante.agendar.inserir.view'));

    //     $this->get(route('representante.agendar.inserir.view'))
    //     ->assertSee('<i class="fas fa-exclamation-triangle"></i>&nbsp;Não pode criar agendamento no momento. Por gentileza, procure o atendimento do Core-SP.');
    // }

    /** @test */
    public function view_salas_in_form_agendar_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $salas = factory('App\SalaReuniao', 3)->create();

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSeeText($salas[0]->regional->regional)
        ->assertSeeText($salas[1]->regional->regional)
        ->assertSeeText($salas[2]->regional->regional);
    }

    /** @test */
    public function view_total_and_itens_in_form_agendar_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda->sala->id, 'dia' => onlyDate($agenda->dia), 'tipo' => 'coworking'
        ]))
        ->assertJsonFragment([
            'itens' => $agenda->sala->getItensHtml('coworking'),
            'total' => $agenda->sala->getParticipantesAgendar('coworking')
        ]);
    }

    /** @test */
    public function can_submit_agendar_sala_coworking()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->assertEquals(AgendamentoSala::count(), 0);

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'idrepresentante' => 1,
        ]);

        $this->assertEquals(AgendamentoSala::count(), 1);
    }

    /** @test */
    public function can_submit_agendar_sala_reuniao()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->assertEquals(AgendamentoSala::count(), 0);

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'reuniao',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['56983238010', '81921923008'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => $agenda['participantes']
        ]);

        $this->assertEquals(AgendamentoSala::count(), 1);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_tipo_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => '',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'tipo_sala'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_tipo_sala_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'reunion',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'tipo_sala'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_sala_reuniao_id()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => '', 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'sala_reuniao_id'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_sala_reuniao_id_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => '23', 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'sala_reuniao_id'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_dia()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => '', 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_format_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => '12-23/2024', 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_before_tomorrow()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => now()->format('d/m/Y'), 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_after_1_month()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => now()->addMonth()->addDays(5)->format('d/m/Y'), 
            'periodo' => $agenda['periodo'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_periodo()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '',
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_periodo_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => 'madrugada',
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_without_participantes_cpf()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => [],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_cpf_not_array()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => 'dfdfdf',
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_cpf_invalid_cpfs()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['111.111.111-11', '222.333.444-99'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_cpf.*'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_cpf_equals()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_cpf.*'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_cpf_rc_in_participantes_cpf()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => [$representante->cpf_cnpj, '569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_cpf.*'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_without_participantes_nome()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => [],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_nome'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_not_array()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => 'fgfgf',
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_nome'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_size_less_than_participantes_cpf()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['SÓ UM NOME', ''],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_nome'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_equals()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['TUDO IGUAL', 'TUDO IGUAL'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_with_numbers()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['TUDO IGUAL 2', 'TUDO 1GUAL'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_less_than_5_chars()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['TUDO', 'TUD'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_greater_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => [$faker->sentence(300), $faker->sentence(400)],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_vetados()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'participante_vetado'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_lotado()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => 'manha'
        ]);
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => 'tarde',
            'sala_reuniao_id' => $agenda1->sala_reuniao_id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => 'manha',
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia',
            'periodo'
        ]);

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => 'tarde',
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia',
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_periodo_lotado()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => 'manha'
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => 'manha',
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia',
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_agendado()
    {
        // Agendado em outra regional, mas no mesmo dia e periodo

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        factory('App\AgendamentoSala')->states('reuniao')->create([
            'periodo' => 'manha'
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => 'manha',
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'acao' => 'agendar'
        ]))
        ->assertSessionHasErrors([
            'dia',
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_after_created_4_by_month()
    {
        // Dependendo do dia que o teste é executado, pode dar erro devido a mudança de mês.
        $dia = Carbon::today()->addDays(2);
        while($dia->isWeekend())
            $dia->addDay();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        factory('App\AgendamentoSala', 4)->create();
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => $dia->format('d/m/Y'), 
            'periodo' => 'manha',
            'acao' => 'agendar'
        ]))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos a finalizar no mês atual e/ou seguinte.');
    }

    /** @test */
    public function get_lotados_reuniao()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create();
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'periodo' => 'tarde'
        ]);

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => '',
            'tipo' => 'reuniao',
        ]))
        ->assertJsonFragment([
            [$dia->month, $dia->day, 'lotado']
        ]);
    }

    /** @test */
    public function get_lotados_coworking()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        $agenda1 = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id
        ]);
        factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => 'tarde'
        ]);

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => '',
            'tipo' => 'coworking',
        ]))
        ->assertJsonFragment([
            [$dia->month, $dia->day, 'lotado']
        ]);
    }

    /** @test */
    public function get_liberado_periodo_reuniao()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create();

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => $dia->format('d/m/Y'),
            'tipo' => 'reuniao',
        ]))
        ->assertJsonFragment([
            'tarde' => "Tarde: ".implode(', ', $agenda1->sala->getHorariosTarde('reuniao'))
        ]);
    }

    /** @test */
    public function get_liberado_periodo_coworking()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        $agenda1 = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id
        ]);

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => $dia->format('d/m/Y'),
            'tipo' => 'coworking',
        ]))
        ->assertJsonFragment([
            'tarde' => "Tarde: ".implode(', ', $agenda1->sala->getHorariosTarde('coworking'))
        ]);
    }

    /** @test */
    public function view_agendamento_after_created()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p class="pb-0 branco">Protocolo: <strong>'.$agenda->protocolo.'</strong></p>')
        ->assertSee('<p class="pb-0 branco">Regional: <strong>'.$agenda->sala->regional->regional.'</strong></p>')
        ->assertSee('Sala: <strong>'.$agenda->getTipoSala().'</strong>')
        ->assertSee('&nbsp;&nbsp;|&nbsp;&nbsp;Dia: <strong>'.onlyDate($agenda->dia).'</strong>')
        ->assertSee('&nbsp;&nbsp;|&nbsp;&nbsp;Período: <strong>'.$agenda->getPeriodo().'</strong>')
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle">Cancelar</a>');
    }

    /** @test */
    public function view_agendamento_participantes_after_created()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $cpfs = array();
        $nomes = array();

        foreach($agenda->getParticipantes() as $cpf => $nome){
            array_push($cpfs, $cpf);
            array_push($nomes, $nome);
        }

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p class="pb-0 branco"><i class="fas fa-users text-dark"></i> Participantes: ')
        ->assertSee('CPF: <strong>'.formataCpfCnpj($cpfs[0]) . '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;Nome: <strong>' .$nomes[0].'</strong>')
        ->assertSee('CPF: <strong>'.formataCpfCnpj($cpfs[1]) . '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;Nome: <strong>' .$nomes[1].'</strong>')
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]).'" class="btn btn-secondary btn-sm link-nostyle">Editar Participantes</a>');
    }

    /** @test */
    public function can_to_edit_participantes_reuniao()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Editar');

        $this->put(route('representante.agendar.inserir.put', [
            'participantes_cpf' => ['56983238010'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'acao' => 'editar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Participantes foram alterados com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => json_encode([
                '56983238010' => 'NOME PARTICIPANTE UM'
            ], JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_send_mail_when_not_changed_participantes_reuniao()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'participantes' => json_encode([
                '56983238010' => 'NOME PARTICIPANTE UM'
            ], JSON_FORCE_OBJECT)
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'participantes_cpf' => ['56983238010'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'acao' => 'editar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        Mail::assertNotQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-info-circle"></i>&nbsp;&nbsp;Não houve alterações nos participantes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => $agenda['participantes']
        ]);
    }

    /** @test */
    public function cannot_to_edit_participantes_reuniao_equal_or_after_dia()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertDontSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]).'" class="btn btn-secondary btn-sm link-nostyle">Editar Participantes</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível editar o agendamento.');

        $this->put(route('representante.agendar.inserir.put', [
            'participantes_cpf' => ['56983238010'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'acao' => 'editar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível editar o agendamento.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => $agenda['participantes']
        ]);
    }

    /** @test */
    public function can_to_cancel()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle">Cancelar</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSee('<button type="submit" class="btn btn-danger">');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'cancelar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento cancelado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Cancelado'
        ]);
    }

    /** @test */
    public function cannot_to_cancel_equal_or_after_dia()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertDontSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle">Cancelar</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível cancelar o agendamento.');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'cancelar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível cancelar o agendamento.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'status' => 'Cancelado'
        ]);
    }

    /** @test */
    public function can_to_justify()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]).'" class="btn btn-sm btn-dark link-nostyle">Justificar</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Justificar');

        $this->put(route('representante.agendar.inserir.put', [
            'justificativa' => 'dfdfdfdfdfdfdfdfdfdfdfdfdf',
            'anexo_sala' => '',
            'acao' => 'justificar',
            'id' => $agenda->id
        ]))
        ->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento justificado com sucesso! Está em análise do atendente. Foi enviado um e-mail com a sua justificativa.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Justificativa Enviada'
        ]);
    }
}
