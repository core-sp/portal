<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;
use App\SalaReuniao;
use App\SalaReuniaoBloqueio;
use Illuminate\Support\Facades\Mail;
use App\Mail\SalaReuniaoMail;

class SalaReuniaoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES GERENCIAR SALA DE REUNIÃO
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $sala = factory('App\SalaReuniao')->create();
        
        $this->get(route('sala.reuniao.index'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.editar', $sala->id))->assertRedirect(route('login'));

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.busca'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.criar'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio->id))->assertRedirect(route('login'));
        $this->post(route('sala.reuniao.bloqueio.store'))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio->id))->assertRedirect(route('login'));
        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.horariosAjax'), ['id' => $bloqueio->sala_reuniao_id])->assertRedirect(route('login'));
        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [])->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $sala = factory('App\SalaReuniao')->create();
        
        $this->get(route('sala.reuniao.index'))->assertForbidden();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertForbidden();
        $this->put(route('sala.reuniao.editar', $sala->id), [
            'hora_limite_final_manha' => '12:00',
            'hora_limite_final_tarde' => '16:00',
            'participantes_reuniao' => 0,
            'horarios_reuniao' => [],
            'itens_reuniao' => [],
            'participantes_coworking' => 0,
            'horarios_coworking' => [],
            'itens_coworking' => [],
        ])->assertForbidden();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.busca'))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.criar'))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio->id))->assertForbidden();
        $this->post(route('sala.reuniao.bloqueio.store'), [
            'sala_reuniao_id' => $bloqueio->sala_reuniao_id,
            'dataInicial' => $bloqueio->dataInicial,
            'dataFinal' => null,
            'horarios' => ['10:00'],
        ])->assertForbidden();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio->id), [
            'sala_reuniao_id' => $bloqueio->sala_reuniao_id,
            'dataInicial' => $bloqueio->dataInicial,
            'dataFinal' => null,
            'horarios' => ['10:00'],
        ])->assertForbidden();
        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.horariosAjax'), ['id' => $bloqueio->sala_reuniao_id])->assertForbidden();
        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['10:00']
        ])->assertForbidden();
    }

    /* SALAS */

    /** @test */
    public function sala_can_be_edited()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_manha'] = '12:00';
        $dados['hora_limite_final_tarde'] = '17:00';
        $dados['participantes_reuniao'] = 2;
        $dados['participantes_coworking'] = 2;
        $dados['horarios_reuniao'] = ['10:00', '11:00', '11:30', '14:00', '15:00', '15:30'];
        $dados['horarios_coworking'] = $dados['horarios_reuniao'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'hora_limite_final_manha' => $dados['hora_limite_final_manha'],
            'hora_limite_final_tarde' => $dados['hora_limite_final_tarde'],
            'participantes_reuniao' => 2,
            'participantes_coworking' => 2,
            'horarios_reuniao' => implode(',', $dados['horarios_reuniao']),
            'horarios_coworking' => implode(',', $dados['horarios_reuniao'])
        ]);
    }

    /** @test */
    public function when_add_mesa_change_same_participantes_reuniao()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorarios('reuniao');
        $horarios_c = $sala->getHorarios('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_manha'] = '12:00';
        $dados['hora_limite_final_tarde'] = '17:00';
        $dados['participantes_reuniao'] = 2;
        $dados['horarios_reuniao'] = $horarios_r;
        $dados['horarios_coworking'] = $horarios_c;
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertNotEquals($dados['itens_reuniao'][3], "Mesa com 2 cadeira(s)");
        $itens = $dados['itens_reuniao'];
        $itens[3] = "Mesa com 2 cadeira(s)";
        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_reuniao' => 2,
            'horarios_reuniao' => implode(',', $dados['horarios_reuniao']),
            'horarios_coworking' => implode(',', $dados['horarios_coworking']),
            'itens_reuniao' => json_encode($itens, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function log_is_generated_when_sala_is_edited()
    {
        $user = $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorarios('reuniao');
        $horarios_c = $sala->getHorarios('coworking');

        $this->get(route('sala.reuniao.editar.view', $sala->id));

        $dados = $sala->toArray();
        $dados['hora_limite_final_manha'] = '12:00';
        $dados['hora_limite_final_tarde'] = '17:00';
        $dados['participantes_reuniao'] = 2;
        $dados['horarios_reuniao'] = $horarios_r;
        $dados['horarios_coworking'] = $horarios_c;
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *sala de reunião / coworking* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function sala_can_be_edited_without_horarios_reuniao_when_participantes_reuniao_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_c = $sala->getHorarios('coworking');
        array_push($horarios_c, '14:00');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 0;
        $dados['horarios_reuniao'] = [];
        $dados['horarios_coworking'] = $horarios_c;
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_reuniao' => 0,
            'horarios_reuniao' => null,
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_itens_reuniao_when_participantes_reuniao_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_c = $sala->getHorarios('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 0;
        $dados['horarios_reuniao'] = [];
        $dados['horarios_coworking'] = $horarios_c;
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_reuniao' => 0,
            'horarios_reuniao' => null,
            'itens_reuniao' => json_encode(array(), JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_horarios_coworking_when_participantes_coworking_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorarios('reuniao');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = 0;
        $dados['horarios_reuniao'] = $horarios_r;
        $dados['horarios_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_coworking' => 0,
            'horarios_coworking' => null,
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_itens_coworking_when_participantes_coworking_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorarios('reuniao');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = 0;
        $dados['horarios_reuniao'] = $horarios_r;
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = [];
        $dados['horarios_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_coworking' => 0,
            'horarios_coworking' => null,
            'itens_coworking' => json_encode(array(), JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_with_itens_with_underline_when_participantes_reuniao_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'itens_reuniao' => json_encode(array(), JSON_FORCE_OBJECT)
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 0;
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['itens_reuniao'] = ['Água', 'Café', 'TV _ polegadas com entrada HDMI'];
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        $this->assertDatabaseHas('salas_reunioes', [
            'itens_reuniao' => json_encode(array(), JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_hora_limite_final_manha()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'participantes_reuniao' => 0,
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_manha'] = null;
        $dados['hora_limite_final_tarde'] = '17:00';
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'hora_limite_final_manha'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_invalid_value_hora_limite_final_manha()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'participantes_reuniao' => 0,
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_manha'] = '10:30';
        $dados['hora_limite_final_tarde'] = '17:00';
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'hora_limite_final_manha'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_hora_limite_final_tarde()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'participantes_reuniao' => 0,
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_tarde'] = null;
        $dados['hora_limite_final_manha'] = '12:00';
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'hora_limite_final_tarde'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_invalid_value_hora_limite_final_tarde()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'participantes_reuniao' => 0,
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_tarde'] = '14:30';
        $dados['hora_limite_final_manha'] = '12:00';
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'hora_limite_final_tarde'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_horarios_equal_hora_limite_final_manha()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_manha'] = '12:00';
        $dados['horarios_reuniao'] = ['09:00','10:00','11:00','12:00','12:30'];
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_horarios_equal_hora_limite_final_tarde()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['hora_limite_final_tarde'] = '16:00';
        $dados['horarios_reuniao'] = ['09:00','10:00','11:00','16:00','16:30'];
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_participantes_reuniao()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = null;
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_participantes_reuniao_not_integer()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 'A';
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_participantes_reuniao_equal_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 1;
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_participantes_reuniao_greater_than_20()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 21;
        $dados['horarios_reuniao'] = $sala->getHorarios('reuniao');
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_horarios_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = [];
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_horarios_not_array_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = implode(',',$sala->getHorarios('reuniao'));
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_horarios_invalid_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['14:00','18:00'];
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_horarios_not_distinct_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['09:00','10:00','09:00'];
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_reuniao.*'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_itens_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['09:00','10:00','11:00'];
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_array_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['09:00','10:00','11:00'];
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = [];
        $dados['itens_reuniao'] = 'tv de teste';

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_invalid_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['09:00','10:00','11:00'];
        $dados['horarios_coworking'] = [];
        $dados['itens_reuniao'] = ['Agua', 'Café'];
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_distinct_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['09:00','10:00','11:00'];
        $dados['horarios_coworking'] = [];
        $dados['itens_reuniao'] = ['Água', 'Café', 'Água'];
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_with_underline_when_participantes_reuniao_greater_than_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['09:00','10:00','11:00'];
        $dados['horarios_coworking'] = [];
        $dados['itens_reuniao'] = ['Água', 'Café', 'TV _ polegadas com entrada HDMI'];
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_participantes_coworking()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = null;
        $dados['horarios_coworking'] = ['09:00','10:00','11:00'];
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_participantes_coworking_not_integer()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = 'A';
        $dados['horarios_coworking'] = ['09:00','10:00','11:00'];
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_when_participantes_coworking_greater_than_20()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = 21;
        $dados['horarios_coworking'] = ['09:00','10:00','11:00'];
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_horarios_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = [];
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_coworking',
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_horarios_not_array_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = '09:00,10:00';
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_invalid_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['17:00', '18:00'];
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_not_distinct_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['09:00', '10:00', '10:00'];
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'horarios_coworking.*'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_itens_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['09:00', '10:00', '11:00'];
        $dados['itens_coworking'] = null;
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_array_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['09:00', '10:00', '11:00'];
        $dados['itens_coworking'] = 'tv de teste';
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_invalid_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['09:00', '10:00', '11:00'];
        $dados['itens_coworking'] = ['Agua', 'Café'];
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_distinct_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['09:00', '10:00', '11:00'];
        $dados['itens_coworking'] = ['Água', 'Café', 'Água'];
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking.*'
        ]);
    }

    /** @test */
    public function send_mail_after_change_itens_reuniao_or_participantes_reuniao_when_idregional_not_1_or_14()
    {
        Mail::fake();
        $this->signInAsAdmin();

        $user = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 21
            ]),
            'email' => 'gerteste@teste.com',
            'idregional' => factory('App\Regional')->create([
                'idregional' => 2
            ])
        ]);

        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['10:30', '11:00', '11:30'];
        $dados['participantes_reuniao'] = 5;
        $dados['itens_reuniao'] = ['Água', 'Café'];
        $dados['itens_coworking'] = [];
        $dados['horarios_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** @test */
    public function send_mail_after_change_itens_reuniao_or_participantes_reuniao_when_idregional_1_or_14()
    {
        Mail::fake();
        $this->signInAsAdmin();

        $user = factory('App\User')->create([
            'idusuario' => 39,
            'email' => 'teste@teste.com',
            'idregional' => 1
        ]);

        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_reuniao'] = ['10:30', '11:00', '11:30'];
        $dados['participantes_reuniao'] = 5;
        $dados['itens_reuniao'] = ['Água', 'Café'];
        $dados['itens_coworking'] = [];
        $dados['horarios_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** @test */
    public function send_mail_after_change_itens_coworking_or_participantes_coworking_when_idregional_not_1_or_14()
    {
        Mail::fake();
        $this->signInAsAdmin();

        $user = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 21
            ]),
            'email' => 'gerteste@teste.com',
            'idregional' => factory('App\Regional')->create([
                'idregional' => 2
            ])
        ]);

        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['10:30', '11:00', '11:30'];
        $dados['participantes_coworking'] = 5;
        $dados['itens_coworking'] = ['Água', 'Café'];
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** @test */
    public function send_mail_after_change_itens_coworking_or_participantes_coworking_when_idregional_1_or_14()
    {
        Mail::fake();
        $this->signInAsAdmin();

        $user = factory('App\User')->create([
            'idusuario' => 39,
            'email' => 'teste@teste.com',
            'idregional' => 1
        ]);

        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['horarios_coworking'] = ['10:30', '11:00', '11:30'];
        $dados['participantes_coworking'] = 5;
        $dados['itens_coworking'] = ['Água', 'Café'];
        $dados['itens_reuniao'] = [];
        $dados['horarios_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** @test */
    public function get_format_hours()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['10:30', '11:30', '14:30', '15:30'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertJsonFragment([
            "10:30 até 11:30<br>11:30 até 12:30<br>Período todo: 10:30 até 12:30<br>14:30 até 15:30<br>15:30 até 16:30<br>Período todo: 14:30 até 16:30"
        ]);

        $this->assertDatabaseHas('salas_reunioes', [
            'id' => 1,
            'hora_limite_final_manha' => '12:00',
            'hora_limite_final_tarde' => '17:00',
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_without_horarios()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => null,
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_with_horarios_not_array()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => '10:00',
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_with_horarios_invalid_value()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['18:30'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_with_horarios_not_distinct()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['14:00', '15:00', '14:00'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios.*'
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_with_horarios_within_range_hora_limite_final_manha()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['11:00', '12:00'],
            'hora_limite_final_manha' => '11:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_with_horarios_within_range_hora_limite_final_tarde()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['09:30', '17:00'],
            'hora_limite_final_manha' => '11:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_with_hora_limite_final_manha_invalid_value()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['14:00', '15:00'],
            'hora_limite_final_manha' => '10:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'hora_limite_final_manha'
        ]);

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['10:34', '15:00'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['abc', '15:00'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** @test */
    public function cannot_get_format_hours_with_hora_limite_final_tarde_invalid_value()
    {
        $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['09:00', '10:00'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '14:30'
        ])
        ->assertSessionHasErrors([
            'hora_limite_final_tarde'
        ]);

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['09:00', '14:10'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);

        $this->post(route('sala.reuniao.horario.formatado', $sala->id), [
            'horarios' => ['09:00', 'adf12'],
            'hora_limite_final_manha' => '12:30',
            'hora_limite_final_tarde' => '16:30'
        ])
        ->assertSessionHasErrors([
            'horarios'
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES GERENCIAR SALA DE REUNIÃO BLOQUEIO
     * =======================================================================================================
     */

    /** @test */
    public function can_create_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '11:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 

        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function two_or_more_bloqueios_with_same_sala_can_be_created()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();

        $dados = [
            'sala_reuniao_id' => $sala->id,
            'dataInicial' => now()->addDay()->format('Y-m-d'),
            'dataFinal' => null,
            'horarios' => ['11:00']
        ];

        $this->post(route('sala.reuniao.bloqueio.store'), $dados)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $dados['dataInicial'],
            'dataFinal' => $dados['dataFinal'],
            'horarios' => '11:00',
            'sala_reuniao_id' => $sala->id
        ]);

        $dados['dataFinal'] = now()->addDays(3)->format('Y-m-d');
        $dados['dataFinal'] = now()->addDays(6)->format('Y-m-d');
        $dados['horarios'] = ['10:00'];

        $this->post(route('sala.reuniao.bloqueio.store'), $dados)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 2,
            'dataInicial' => $dados['dataInicial'],
            'dataFinal' => $dados['dataFinal'],
            'horarios' => '10:00',
            'sala_reuniao_id' => $sala->id
        ]);
    }

    /** @test */
    public function can_create_bloqueio_without_data_final()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '14:00'],
            'dataFinal' => null
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_created()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '11:00'],
        ]);

        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *sala reunião / coworking bloqueio* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_create_bloqueio_without_sala_reuniao_id()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'sala_reuniao_id' => null,
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertStatus(404);
    }

    /** @test */
    public function cannot_create_bloqueio_when_sala_disabled()
    {
        $user = $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->states('desativa_ambos')->create();

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), [
            'sala_reuniao_id' => $sala->id,
            'dataInicial' => now()->addDay()->format('Y-m-d'),
            'dataFinal' => null,
            'horarios' => ['10:00'],
        ])
        ->assertSessionHasErrors([
            'sala_reuniao_id',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_without_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataInicial' => null,
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_inicial_invalid()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataFinal' => null,
            'dataInicial' => now()->addDays(2)->format('d/m/Y'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_final_invalid()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataInicial' => now()->addDays(2)->format('Y-m-d'),
            'dataFinal' => now()->addDays(2)->format('d/m/Y'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataFinal',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_inicial_before_or_equal_today()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataInicial' => now()->format('Y-m-d'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_final_filled_and_before_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataFinal' => now()->format('Y-m-d'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataFinal',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_without_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => [],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_horarios_without_array()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => '10:00',
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_same_hours_in_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios.*',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_invalid_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '18:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function can_edit_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function can_edit_bloqueio_without_data_final()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataFinal'] = null;

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function can_edit_bloqueio_with_sala_disabled()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->states('desativa_ambos')->create()
        ])->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataFinal'] = null;

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_edited()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];

        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *sala reunião / coworking bloqueio* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_sala_reuniao_id()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['sala_reuniao_id'] = null;
        $bloqueio['horarios'] = ['10:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertStatus(404);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataInicial'] = null;

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_data_inicial_before_or_equal_today()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataInicial'] = now()->format('Y-m-d');

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_data_final_filled_and_before_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataFinal'] = now()->format('Y-m-d');

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'dataFinal',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = [];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_horarios_without_array()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = '10:00';

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_same_hours_in_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00', '10:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios.*',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_invalid_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00', '18:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function can_delete_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id))
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseMissing('salas_reunioes_bloqueios', [
            'idagendamentobloqueio' => $bloqueio->id,
            'dataInicial' => $bloqueio->dataInicial,
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_deleted()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') excluiu *sala reunião / coworking bloqueio* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function can_view_list_bloqueios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))
        ->assertSee($bloqueio->sala->regional->regional)
        ->assertSee($bloqueio->mostraPeriodo())
        ->assertSee($bloqueio->horarios);
    }

    /** @test */
    public function can_view_list_bloqueios_with_data_final_null()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'dataFinal' => null
        ]);

        $this->get(route('sala.reuniao.bloqueio.lista'))
        ->assertSee($bloqueio->sala->regional->regional)
        ->assertSee('Tempo Indeterminado');
    }

    /** @test */
    public function can_view_button_editar_and_cancelar()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))
        ->assertSee($bloqueio->sala->regional->regional)
        ->assertSee($bloqueio->mostraPeriodo())
        ->assertSee($bloqueio->horarios)
        ->assertSee('Editar')
        ->assertSee('Apagar');
    }

    /** @test */
    public function can_view_list_salas_when_create()
    {
        $user = $this->signInAsAdmin();

        $salas = factory('App\SalaReuniao', 5)->create();

        $this->get(route('sala.reuniao.bloqueio.criar'))
        ->assertSee($salas->get(0)->regional->regional)
        ->assertSee($salas->get(1)->regional->regional)
        ->assertSee($salas->get(2)->regional->regional)
        ->assertSee($salas->get(3)->regional->regional)
        ->assertSee($salas->get(4)->regional->regional);
    }

    /** @test */
    public function cannot_view_sala_disabled_when_create()
    {
        $user = $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->states('desativa_ambos')->create();

        $this->get(route('sala.reuniao.bloqueio.criar'))
        ->assertDontSeeText($sala->regional->regional);
    }

    /** @test */
    public function get_horarios_by_ajax()
    {
        $user = $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->get(route('sala.reuniao.bloqueio.horariosAjax', ['id' => $sala->id]))
        ->assertJson(
            $sala->getTodasHoras()
        );
    }

    /** @test */
    public function get_error_when_nonexistent_sala_by_ajax()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('sala.reuniao.bloqueio.horariosAjax', ['id' => 5]))->assertStatus(500);
    }
}
