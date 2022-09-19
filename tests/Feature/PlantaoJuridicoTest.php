<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;

class PlantaoJuridicoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $plantao = factory('App\PlantaoJuridico')->create();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
        
        $this->get(route('plantao.juridico.index'))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertRedirect(route('login'));
        $this->put(route('plantao.juridico.editar', $plantao->id))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.index'))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertRedirect(route('login'));
        $this->post(route('plantao.juridico.bloqueios.criar'))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertRedirect(route('login'));
        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id))->assertRedirect(route('login'));
        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertRedirect(route('login'));
        $this->get(route('plantao.juridico.bloqueios.ajax'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $plantao = factory('App\PlantaoJuridico')->create();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

        $this->get(route('plantao.juridico.index'))->assertForbidden();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertForbidden();
        $this->put(route('plantao.juridico.editar', $plantao->id))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.index'))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertForbidden();
        $this->post(route('plantao.juridico.bloqueios.criar'))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertForbidden();
        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id))->assertForbidden();
        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertForbidden();
        $this->get(route('plantao.juridico.bloqueios.ajax'))->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $this->assertAuthenticated('web');
        
        $plantao = factory('App\PlantaoJuridico')->create();
        $dados = $plantao->toArray();
        $dados['horarios'] = ['12:00', '13:00'];

        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
        
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();
        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)->assertStatus(302);
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['12:00']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)->assertStatus(302);
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();
        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)->assertStatus(302);
        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertStatus(302);
        $this->get(route('plantao.juridico.bloqueios.ajax', ['id' => 1]))->assertOk();
    }

    /* PLANTÃO JURÍDICO */

    /** @test */
    public function plantao_can_be_edited()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['qtd_advogados'] = 1;
        $dados['dataInicial'] = date('Y-m-d', strtotime('+2 month'));
        $dados['dataFinal'] = date('Y-m-d', strtotime('+2 month'));
        $dados['horarios'] = ['10:00', '11:00', '12:00'];

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)->assertRedirect(route('plantao.juridico.index'));
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $dados['dataInicial'],
            'horarios' => '10:00,11:00,12:00',
            'qtd_advogados' => 1
        ]);
    }

    /** @test */
    public function log_is_generated_when_plantao_is_edited()
    {
        $user = $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();

        $dados = $plantao->toArray();
        $dados['qtd_advogados'] = 1;
        $dados['dataInicial'] = date('Y-m-d', strtotime('+2 month'));
        $dados['dataFinal'] = date('Y-m-d', strtotime('+2 month'));
        $dados['horarios'] = ['10:00', '11:00', '12:00'];

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('plantão juridico', $log);
        $this->assertStringContainsString('editou', $log);
    }

    /** @test */
    public function plantao_can_be_edited_without_data_inicial_when_qtd_advogados_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = [
            'dataInicial' => '',
            'dataFinal' => $plantao->dataFinal,
            'qtd_advogados' => 0
        ];

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)->assertRedirect(route('plantao.juridico.index'));
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => null,
            'qtd_advogados' => 0
        ]);
    }

    /** @test */
    public function plantao_can_be_edited_without_data_final_when_qtd_advogados_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = [
            'dataInicial' => $plantao->dataFinal,
            'dataFinal' => '',
            'qtd_advogados' => 0
        ];

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)->assertRedirect(route('plantao.juridico.index'));
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataFinal' => null,
            'qtd_advogados' => 0
        ]);
    }

    /** @test */
    public function plantao_can_be_edited_without_horarios_when_qtd_advogados_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        unset($dados['horarios']);
        $dados['qtd_advogados'] = 0;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)->assertRedirect(route('plantao.juridico.index'));
        $this->assertDatabaseHas('plantoes_juridicos', [
            'horarios' => null,
            'qtd_advogados' => 0
        ]);
    }

    /** @test */
    public function plantao_can_be_edited_without_inputs_when_qtd_advogados_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = [
            'dataInicial' => '',
            'dataFinal' => '',
            'qtd_advogados' => 0
        ];

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)->assertRedirect(route('plantao.juridico.index'));
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => null,
            'dataFinal' => null,
            'horarios' => null,
            'qtd_advogados' => 0
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_without_inputs_when_qtd_advogados_greater_then_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = [
            'horarios' => '',
            'dataInicial' => '',
            'dataFinal' => '',
            'qtd_advogados' => 1
        ];

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors([
            'dataInicial',
            'dataFinal',
            'horarios'
        ]);
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_without_data_inicial_when_qtd_advogados_greater_then_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['dataInicial'] = '';
        $dados['horarios'] = explode(',', $plantao->horarios);
        $dados['qtd_advogados'] = 1;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('dataInicial');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_without_data_final_when_qtd_advogados_greater_then_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['dataFinal'] = '';
        $dados['horarios'] = explode(',', $plantao->horarios);
        $dados['qtd_advogados'] = 1;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('dataFinal');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_without_horarios_when_qtd_advogados_greater_then_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['horarios'] = array();
        $dados['qtd_advogados'] = 1;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('horarios');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_with_horarios_without_array_when_qtd_advogados_equal_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['horarios'] = '10:00';
        $dados['qtd_advogados'] = 0;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('horarios');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_with_horarios_without_array_when_qtd_advogados_greater_then_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['horarios'] = '10:00';
        $dados['qtd_advogados'] = 1;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('horarios');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_with_horarios_with_same_hours_when_qtd_advogados_greater_then_0()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['horarios'] = ['10:00', '10:00'];
        $dados['qtd_advogados'] = 1;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('horarios.*');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_with_invalid_dates()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = [
            'qtd_advogados' => 1,
            'dataInicial' => '2022-02-31',
            'dataFinal' => '2022-02-31'
        ];

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors([
            'dataInicial',
            'dataFinal'
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_with_data_inicial_before_or_equal_today()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['dataInicial'] = date('Y-m-d');
        $dados['horarios'] = explode(',', $plantao->horarios);
        $dados['qtd_advogados'] = 1;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('dataInicial');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_with_data_final_before_data_inicial()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['dataInicial'] = date('Y-m-d', strtotime('+2 day'));
        $dados['dataFinal'] = date('Y-m-d', strtotime('+1 day'));
        $dados['horarios'] = explode(',', $plantao->horarios);
        $dados['qtd_advogados'] = 1;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('dataFinal');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function plantao_cannot_be_edited_with_qtd_advogados_greater_then_9()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.index'))->assertOk();
        $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertOk();

        $dados = $plantao->toArray();
        $dados['horarios'] = explode(',', $plantao->horarios);
        $dados['qtd_advogados'] = 10;

        $this->put(route('plantao.juridico.editar', $plantao->id), $dados)
        ->assertSessionHasErrors('qtd_advogados');
        $this->assertDatabaseHas('plantoes_juridicos', [
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal,
            'horarios' => $plantao->horarios,
            'qtd_advogados' => $plantao->qtd_advogados
        ]);
    }

    /** @test */
    public function alert_when_expired_plantao_and_active()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1,
            'dataInicial' => date('Y-m-d', strtotime('-1 day')),
            'dataFinal' => date('Y-m-d', strtotime('-1 day'))
        ]);

        $this->get(route('plantao.juridico.index'))->assertSeeText('Período expirado, DESATIVE o plantão');
    }

    /** @test */
    public function show_status()
    {
        $this->signInAsAdmin();

        $plantao1 = factory('App\PlantaoJuridico')->create();

        $this->get(route('plantao.juridico.index'))
        ->assertSeeText('Desativado');

        $plantao2 = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1,
        ]);

        $this->get(route('plantao.juridico.index'))
        ->assertSeeText('Ativado')
        ->assertSeeText('com '.$plantao2->qtd_advogados.' advogado(s)');
    }

    /** @test */
    public function show_periodo()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('plantao.juridico.index'))
        ->assertSeeText(onlyDate($plantao->dataInicial).' - '.onlyDate($plantao->dataFinal));
    }

    /** @test */
    public function show_horarios()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create();

        $this->get(route('plantao.juridico.index'))
        ->assertSeeText($plantao->horarios);
    }

    /** @test */
    public function show_agendados_text_when_active_plantao_without_agendados()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $this->get(route('plantao.juridico.editar.view', $plantao->id))
        ->assertSeeText('Ainda não há agendados');
    }

    /** @test */
    public function not_show_agendados_text_when_disabled_plantao_without_agendados()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 0
        ]);

        $this->get(route('plantao.juridico.editar.view', $plantao->id))
        ->assertDontSeeText('Ainda não há agendados');
    }

    /** @test */
    public function show_agendados_table_when_active_plantao_with_agendados()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $agendados = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $this->get(route('plantao.juridico.editar.view', $plantao->id))
        ->assertSeeText('Total de agendamentos deste plantão ativo já cadastrados')
        ->assertSee('<td>'.onlyDate($agendados->dia).'</td>')
        ->assertSee('1 agendado(s) às '.$agendados->hora.' <i class="fas fa-grip-lines-vertical" style="font-size:16px;color:red"></i>');
    }

    /** @test */
    public function not_show_agendados_table_when_disabled_plantao_with_agendados()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create();

        $agendados = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $this->get(route('plantao.juridico.editar.view', $plantao->id))
        ->assertDontSeeText('Total de agendamentos deste plantão ativo já cadastrados')
        ->assertDontSee('<td>'.onlyDate($agendados->dia).'</td>')
        ->assertDontSeeText('1 agendado(s) às '.$agendados->hora);
    }

    /** @test */
    public function not_show_past_days_agendados_table_when_disabled_plantao_with_agendados()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1,
            'dataInicial' => Carbon::today()->subDay()->format('Y-m-d')
        ]);

        $agendadoAntigo = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCF',
            'dia' => $plantao->dataInicial,
            'hora' => '11:00'
        ]);

        $agendadoAtual = factory('App\Agendamento')->create([
            'tiposervico' => 'Plantão Jurídico para Ambas',
            'idregional' => $plantao->idregional,
            'protocolo' => 'AGE-ABCD',
            'dia' => $plantao->dataFinal,
            'hora' => '10:00'
        ]);

        $this->get(route('plantao.juridico.editar.view', $plantao->id))
        ->assertSeeText('Total de agendamentos deste plantão ativo já cadastrados')
        ->assertDontSee('<td>'.onlyDate($plantao->dataInicial).'</td>')
        ->assertSee('<td>'.onlyDate($plantao->dataFinal).'</td>')
        ->assertSeeText('1 agendado(s) às '.$agendadoAtual->hora)
        ->assertDontSeeText('1 agendado(s) às '.$agendadoAntigo->hora);
    }

    /** 
     * =======================================================================================================
     * BLOQUEIOS
     * =======================================================================================================
     */

    /** @test */
    public function bloqueio_can_be_created()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)->assertRedirect(route('plantao.juridico.bloqueios.index'));
        $this->assertDatabaseHas('plantoes_juridicos_bloqueios', [
            'dataInicial' => $dados['dataInicialBloqueio'],
            'dataFinal' => $dados['dataFinalBloqueio'],
            'horarios' => '11:00,11:30',
            'idplantaojuridico' => $plantao->id
        ]);
    }

    /** @test */
    public function two_or_more_bloqueios_with_same_plantao_can_be_created()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)->assertRedirect(route('plantao.juridico.bloqueios.index'));
        $this->assertDatabaseHas('plantoes_juridicos_bloqueios', [
            'id' => 1,
            'dataInicial' => $dados['dataInicialBloqueio'],
            'dataFinal' => $dados['dataFinalBloqueio'],
            'horarios' => '11:00,11:30',
            'idplantaojuridico' => $plantao->id
        ]);

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => Carbon::parse($plantao->dataInicial)->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::parse($plantao->dataFinal)->format('Y-m-d'),
            'horariosBloqueio' => ['11:00']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)->assertRedirect(route('plantao.juridico.bloqueios.index'));
        $this->assertDatabaseHas('plantoes_juridicos_bloqueios', [
            'id' => 2,
            'dataInicial' => $dados['dataInicialBloqueio'],
            'dataFinal' => $dados['dataFinalBloqueio'],
            'horarios' => '11:00',
            'idplantaojuridico' => $plantao->id
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_created()
    {
        $user = $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('plantão juridico bloqueio', $log);
        $this->assertStringContainsString('criou', $log);
    }

    /** @test */
    public function bloqueio_cannot_be_created_without_plantao()
    {
        $this->signInAsAdmin();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => '',
            'dataInicialBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('plantaoBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_without_data_inicial()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => '',
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('dataInicialBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_without_data_final()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => '',
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_without_horarios()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ''
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('horariosBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_horarios_without_array()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => '10:00'
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('horariosBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_same_hours_in_horarios()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['10:00', '10:00']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('horariosBloqueio.*');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_without_inputs()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => '',
            'dataInicialBloqueio' => '',
            'dataFinalBloqueio' => '',
            'horariosBloqueio' => ''
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors([
            'plantaoBloqueio',
            'dataInicialBloqueio',
            'dataFinalBloqueio',
            'horariosBloqueio'
        ]);
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_invalid_dates()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => '2022-02-31',
            'dataFinalBloqueio' => '2022-02-31',
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors([
            'dataInicialBloqueio',
            'dataFinalBloqueio'
        ]);

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_data_inicial_before_data_inicial_plantao()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => Carbon::parse($plantao->dataInicial)->subDay()->format('Y-m-d'),
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors([
            'dataInicialBloqueio'
        ]);

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_data_inicial_after_data_final_plantao()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => Carbon::parse($plantao->dataFinal)->addDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::parse($plantao->dataFinal)->addDay()->format('Y-m-d'),
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors([
            'dataInicialBloqueio',
            'dataFinalBloqueio'
        ]);

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_data_inicial_before_or_equal_today()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => date('Y-m-d'),
            'dataFinalBloqueio' => $plantao->dataFinal,
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('dataInicialBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_data_final_before_data_inicial()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => Carbon::tomorrow()->addDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_data_final_after_data_final_plantao()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => Carbon::parse($plantao->dataFinal)->addDay()->format('Y-m-d'),
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_data_final_before_data_inicial_plantao()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => Carbon::parse($plantao->dataInicial)->subDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::parse($plantao->dataInicial)->subDay()->format('Y-m-d'),
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('dataInicialBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_data_inicial_greater_then_data_final()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => Carbon::tomorrow()->addDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'horariosBloqueio' => ['11:00', '11:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_with_horarios_different_of_the_plantao()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertOk();

        $dados = [
            'plantaoBloqueio' => $plantao->id,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['13:00', '13:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('horariosBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => 1
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_created_when_nonexistent_regional()
    {
        $user = $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create();
                
        $dados = [
            'plantaoBloqueio' => 55,
            'dataInicialBloqueio' => $plantao->dataInicial,
            'dataFinalBloqueio' => $plantao->dataInicial,
            'horariosBloqueio' => ['10:00', '10:30']
        ];

        $this->post(route('plantao.juridico.bloqueios.criar'), $dados)
        ->assertSessionHasErrors('plantaoBloqueio');
    }

    /** @test */
    public function bloqueio_can_be_edited()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)->assertRedirect(route('plantao.juridico.bloqueios.index'));
        $this->assertDatabaseHas('plantoes_juridicos_bloqueios', [
            'dataInicial' => $dados['dataInicialBloqueio'],
            'dataFinal' => $dados['dataFinalBloqueio'],
            'horarios' => '11:30,12:00',
            'idplantaojuridico' => $bloqueio->idplantaojuridico
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_edited()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('plantão juridico bloqueio', $log);
        $this->assertStringContainsString('editou', $log);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_without_plantao()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => '',
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('plantaoBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_without_data_inicial()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => '',
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('dataInicialBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_without_data_final()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => '',
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_without_horarios()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ''
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('horariosBloqueio');
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => ''
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_without_inputs()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => '',
            'dataInicialBloqueio' => '',
            'dataFinalBloqueio' => '',
            'horariosBloqueio' => ''
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors([
            'plantaoBloqueio',
            'dataInicialBloqueio',
            'dataFinalBloqueio',
            'horariosBloqueio'
        ]);
        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => ''
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_invalid_dates()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => '2022-02-31',
            'dataFinalBloqueio' => '2022-02-31',
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors([
            'dataInicialBloqueio',
            'dataFinalBloqueio',
        ]);

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_data_inicial_before_data_inicial_plantao()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => Carbon::parse($bloqueio->plantaoJuridico->dataInicial)->subDay()->format('Y-m-d'),
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors([
            'dataInicialBloqueio',
        ]);

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_data_inicial_after_data_final_plantao()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => Carbon::parse($bloqueio->plantaoJuridico->dataFinal)->addDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::parse($bloqueio->plantaoJuridico->dataFinal)->addDay()->format('Y-m-d'),
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors([
            'dataInicialBloqueio',
        ]);

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_data_inicial_before_or_equal_today()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => date('Y-m-d'),
            'dataFinalBloqueio' => $bloqueio->dataFinal,
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('dataInicialBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_data_final_before_data_inicial()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => Carbon::tomorrow()->addDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_data_final_after_data_final_plantao()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => Carbon::parse($bloqueio->plantaoJuridico->dataFinal)->addDay()->format('Y-m-d'),
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_data_final_before_data_inicial_plantao()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => Carbon::parse($bloqueio->plantaoJuridico->dataInicial)->subDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::parse($bloqueio->plantaoJuridico->dataInicial)->subDay()->format('Y-m-d'),
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('dataInicialBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_data_inicial_greater_then_data_final()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => Carbon::tomorrow()->addDay()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'horariosBloqueio' => ['11:30', '12:00']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('dataFinalBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '11:30,12:00'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_horarios_different_of_the_plantao()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertOk();

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ['09:30']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('horariosBloqueio');

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '09:30'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_with_expired_data_final()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create([
            'dataInicial' => date('Y-m-d'),
            'dataFinal' => date('Y-m-d')
        ]);
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantao->id,
            'dataInicial' => $plantao->dataInicial,
            'dataFinal' => $plantao->dataFinal
        ]);

        $this->get(route('plantao.juridico.bloqueios.index'))
        ->assertDontSeeText('Editar');

        $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))
        ->assertRedirect(route('plantao.juridico.bloqueios.index'));

        $this->get(route('plantao.juridico.bloqueios.index'))
        ->assertSeeText('O bloqueio não pode mais ser editado devido o período do plantão ter expirado');

        $dados = [
            'plantaoBloqueio' => $bloqueio->idplantaojuridico,
            'dataInicialBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'dataFinalBloqueio' => Carbon::tomorrow()->format('Y-m-d'),
            'horariosBloqueio' => ['09:30']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertRedirect(route('plantao.juridico.bloqueios.index'));

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'horarios' => '09:30'
        ]);
    }

    /** @test */
    public function bloqueio_cannot_be_edited_when_nonexistent_regional()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

        $dados = [
            'plantaoBloqueio' => 55,
            'dataInicialBloqueio' => $bloqueio->dataInicial,
            'dataFinalBloqueio' => $bloqueio->dataInicial,
            'horariosBloqueio' => ['10:30']
        ];

        $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id), $dados)
        ->assertSessionHasErrors('plantaoBloqueio');
    }

    /** @test */
    public function bloqueio_can_be_deleted()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
                
        $this->get(route('plantao.juridico.bloqueios.index'))->assertOk();
        
        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))
        ->assertRedirect(route('plantao.juridico.bloqueios.index'));

        $this->assertDatabaseMissing('plantoes_juridicos_bloqueios', [
            'id' => $bloqueio->id
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_deleted()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

        $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString('plantão juridico bloqueio', $log);
        $this->assertStringContainsString('excluiu', $log);
    }

    /** @test */
    public function get_datas_horas_link_agendados_plantao_ajax_bloqueio()
    {
        $this->signInAsAdmin();

        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);

        $this->get(route('plantao.juridico.bloqueios.ajax', ['id' => $plantao->id]))
        ->assertJson([
            'horarios' => explode(',', $plantao->horarios),
            'datas' => [$plantao->dataInicial, $plantao->dataFinal],
            'link-agendados' => route('plantao.juridico.editar.view', $plantao->id)
        ]);
    }

    /** @test */
    public function data_inicial_tomorrow_when_before_tomorrow_and_horas_link_plantao_ajax_bloqueio()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create([
            'dataInicial' => Carbon::today()->format('Y-m-d')
        ]);

        $this->get(route('plantao.juridico.bloqueios.ajax', ['id' => $plantao->id]))
        ->assertJson([
            'horarios' => explode(',', $plantao->horarios),
            'datas' => [Carbon::tomorrow()->format('Y-m-d'), $plantao->dataFinal],
            'link-agendados' => null
        ]);
    }

    /** @test */
    public function show_periodo_bloqueio()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

        $this->get(route('plantao.juridico.bloqueios.index'))
        ->assertSeeText(onlyDate($bloqueio->dataInicial).' - '.onlyDate($bloqueio->dataFinal));
    }

    /** @test */
    public function show_horarios_bloqueio()
    {
        $this->signInAsAdmin();
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

        $this->get(route('plantao.juridico.bloqueios.index'))
        ->assertSeeText($bloqueio->horarios);
    }

    /** @test */
    public function show_periodo_plantao_or_expired_bloqueio()
    {
        $this->signInAsAdmin();
        $plantaoExpirado = factory('App\PlantaoJuridico')->create([
            'dataInicial' => Carbon::today()->format('Y-m-d'),
            'dataFinal' => Carbon::today()->format('Y-m-d')
        ]);

        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();
        $bloqueio2 = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantaoExpirado->id,
            'dataInicial' => $plantaoExpirado->dataInicial,
            'dataFinal' => $plantaoExpirado->dataInicial,
        ]);

        $this->get(route('plantao.juridico.bloqueios.index'))
        ->assertSeeText(onlyDate($bloqueio->plantaoJuridico->dataInicial).' - '.onlyDate($bloqueio->plantaoJuridico->dataFinal))
        ->assertSeeText('Expirado');
    }

    /** @test */
    public function show_link_agendados_when_to_edit_and_actived_plantao_bloqueio()
    {
        $this->signInAsAdmin();
        $plantao = factory('App\PlantaoJuridico')->create([
            'qtd_advogados' => 1
        ]);
        $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create([
            'idplantaojuridico' => $plantao->id
        ]);

        $this->get(route('plantao.juridico.bloqueios.editar', $bloqueio->id))
        ->assertSeeText('Ativado!')
        ->assertSee('Confira <a id="linkAgendadosPlantao" href="'
        .route('plantao.juridico.editar.view', $bloqueio->plantaoJuridico->id).
        '">aqui</a> se existem agendados no horário a ser bloqueado para realizar o cancelamento.');
    }
}
